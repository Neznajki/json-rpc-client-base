<?php declare(strict_types=1);


namespace Tests\Functional\JsonRpcClientBase;


use AspectMock\Test;
use Codeception\Example;
use Exception;
use FunctionalTester;
use JsonRpcClientBase\AbstractClient;
use JsonRpcClientBase\DataObject\ResponseCollection;
use JsonRpcClientBase\RequestHandler\CurlRequestHandler;
use JsonRpcClientBase\ValueObject\ClientUser;
use JsonRpcClientBase\ValueObject\ResponseEntity;
use JsonRpcServerCommon\Service\DefaultPasswordEncryptService;
use Tests\JsonRpcClientBase\TestClient;
use Tests\Neznajka\Codeception\Engine\Abstraction\AbstractFunctionalSymfonyCodeceptionTest;
use TypeError;

class AbstractClientCest extends AbstractFunctionalSymfonyCodeceptionTest
{

    /**
     * @dataProvider testRequestData
     * @param FunctionalTester $I
     * @param Example $dataProvider
     * @throws Exception
     */
    public function testRequest(FunctionalTester $I, Example $dataProvider)
    {
        Test::double(AbstractClient::class, ['__destruct' => null]);

        $curlRequestHandler = new CurlRequestHandler(
            new DefaultPasswordEncryptService('functional_test')
        );

        if ($dataProvider->offsetExists('expectsException')) {
            $I->expectThrowable(
                $dataProvider->offsetGet('expectsException'),
                function () use ($dataProvider) {
                    new ClientUser($dataProvider->offsetGet('userName'), $dataProvider->offsetGet('password'));
                }
            );

            return;
        }
        $client = new TestClient($curlRequestHandler);

        $clientUser = new ClientUser($dataProvider->offsetGet('userName'), $dataProvider->offsetGet('password'));
        $client->setUser($clientUser);
        $urlMock = $this->getString();
        $client->setEndpointUrl($urlMock);
        $I->assertSame($urlMock, $this->getNotPublicValue($client, 'endpointUrl'));

        $responseCollectionMock = $this->createPartialAbstractMock(ResponseCollection::class, ['getResponseById']);
        $expectingResult        = $this->createMockExpectsNoUsage(ResponseEntity::class);

        Test::double(CurlRequestHandler::class, ['executeRequestCollection' => $responseCollectionMock]);

        $responseCollectionMock->expects($this->once())->method('getResponseById')->willReturn($expectingResult);

        $responseCollection = $client->ping($this->getString(), $this->getInt());
        $I->assertSame($expectingResult, $responseCollection);
    }

    protected function testRequestData(): array
    {
        return [
            [
                'userName' => $this->getString(),
                'password' => $this->getString(),
            ],
            [
                'userName'         => $this->getString(),
                'password'         => $this->getInt(),
                'expectsException' => TypeError::class,
            ],
            [
                'userName'         => $this->getInt(),
                'password'         => $this->getString(),
                'expectsException' => TypeError::class,
            ],
            [
                'userName'         => null,
                'password'         => $this->getString(),
                'expectsException' => TypeError::class,
            ],
            [
                'userName'         => $this->getString(),
                'password'         => null,
                'expectsException' => TypeError::class,
            ],
        ];
    }
}

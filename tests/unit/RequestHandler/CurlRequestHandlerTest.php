<?php declare(strict_types=1);

use JsonRpcClientBase\AbstractClient;
use JsonRpcClientBase\DataObject\RequestCollection;
use JsonRpcClientBase\DataObject\ResponseCollection;
use JsonRpcClientBase\Exceptions\InvalidResponseException;
use JsonRpcClientBase\RequestHandler\CurlRequestHandler;
use JsonRpcClientBase\ValueObject\ClientUser;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\JsonRpcClientBase\TestClient;
use Tests\Neznajka\Codeception\Engine\Abstraction\AbstractSimpleCodeceptionTest;

/**
 * Class CurlRequestHandlerTest
 * @method MockObject|CurlRequestHandler getWorkingClass(... $mockedMethods)
 */
class CurlRequestHandlerTest extends AbstractSimpleCodeceptionTest
{

    /**
     * @dataProvider getExecuteRequestCollectionData
     * @param $urlMock
     * @param $responseCollectionMock
     * @param string|null $expectsException
     * @throws InvalidResponseException
     * @throws ReflectionException
     */
    public function test_executeRequestCollection($urlMock, $responseCollectionMock, $expectsException = null)
    {
        $this->wantToTestThisMethod();
        $workingClass = $this->getWorkingClass('getCurlOptions', 'createResponseCollection');

        if ($expectsException) {
            $this->expectException($expectsException);
        }

        $curlResourceMock    = $this->getString();
        $curlInitMock        = \AspectMock\Test::func(
            $this->getWorkingClassNameSpace(),
            'curl_init',
            $curlResourceMock
        );
        $curlSetoptArrayMock = \AspectMock\Test::func(
            $this->getWorkingClassNameSpace(),
            'curl_setopt_array',
            true
        );
        $rawResponseMock     = $this->getString();
        $curlExecMock        = \AspectMock\Test::func($this->getWorkingClassNameSpace(), 'curl_exec', $rawResponseMock);
        /** @var MockObject|AbstractClient $clientAbstractMock */
        $clientAbstractMock = $this->createPartialAbstractMock(TestClient::class, ['getUser', 'getEndpointUrl', '__destruct']);

        $jsonBodyMock            = $this->getString();
        $jsonEncodeMock          = \AspectMock\Test::func($this->getWorkingClassNameSpace(), 'json_encode', $jsonBodyMock);
        $requestEntityCollection = $this->createMockExpectsNoUsage(RequestCollection::class);
        $userMock = $this->createMockExpectsNoUsage(ClientUser::class);


        $curlOptionsMock = $this->getArray();
        $workingClass->expects($this->once())->method('getCurlOptions')->with($userMock, $jsonBodyMock)->willReturn($curlOptionsMock);
        $workingClass->expects($this->once())
            ->method('createResponseCollection')
            ->with($requestEntityCollection)
            ->willReturn($responseCollectionMock);

        $clientAbstractMock->expects($this->once())->method('getUser')->willReturn($userMock);
        $clientAbstractMock->expects($this->once())->method('getEndpointUrl')->willReturn($urlMock);

        $expectingResult = $this->createMockExpectsNoUsage(ResponseCollection::class);
        /** @var MockObject|RequestCollection $requestEntityCollection */
        $result = $workingClass->executeRequestCollection($clientAbstractMock, $requestEntityCollection);

        $this->assertEquals($expectingResult, $result);

        $jsonEncodeMock->verifyInvoked([$requestEntityCollection]);
        $curlInitMock->verifyInvoked([$urlMock]);
        $curlExecMock->verifyInvoked([$curlResourceMock]);
        $curlSetoptArrayMock->verifyInvoked([$curlResourceMock, $curlOptionsMock]);
        $this->assertSame($rawResponseMock, $this->getNotPublicValue($workingClass, 'rawResponse'));
        //        $curl = curl_init($this->endpointUrl);
        //
        //        $requestBody = json_encode($requestEntityCollection);
        //        curl_setopt_array($curl,$this->getCurlOptions($requestBody));
        //
        //        $this->rawResponse = curl_exec($curl);
        //
        //        return new ResponseCollection($requestEntityCollection, $this->rawResponse);
    }

    public function getExecuteRequestCollectionData(): array
    {
        return [
            [
                'url'                    => $this->getString(),
                'responseCollectionMock' => $this->createMockExpectsNoUsage(ResponseCollection::class),
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getWorkingClassName(): string
    {
        return CurlRequestHandler::class;
    }
}

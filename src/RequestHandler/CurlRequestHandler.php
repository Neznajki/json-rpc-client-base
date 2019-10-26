<?php


namespace JsonRpcClientBase\RequestHandler;


use JsonRpcClientBase\AbstractClient;
use JsonRpcClientBase\Contract\RequestHandlerInterface;
use JsonRpcClientBase\DataObject\RequestCollection;
use JsonRpcClientBase\DataObject\ResponseCollection;
use JsonRpcClientBase\Exceptions\InvalidResponseException;
use JsonRpcClientBase\ValueObject\ClientUser;
use JsonRpcClientBase\ValueObject\RequestEntity;
use JsonRpcClientBase\ValueObject\ResponseEntity;
use JsonRpcServerCommon\Contract\PasswordEncryptInterface;

class CurlRequestHandler implements RequestHandlerInterface
{
    /** @var PasswordEncryptInterface */
    protected $passwordEncrypt;
    /** @var string */
    protected $rawResponse;

    /**
     * CurlRequestHandler constructor.
     * @param PasswordEncryptInterface $passwordEncrypt
     */
    public function __construct(PasswordEncryptInterface $passwordEncrypt)
    {
        $this->passwordEncrypt = $passwordEncrypt;
    }

    /**
     *
     * @param AbstractClient $client
     * @param RequestEntity $requestEntity
     * @return ResponseEntity raw server response
     * @throws InvalidResponseException
     */
    public function executeSingleRequest(AbstractClient $client, RequestEntity $requestEntity): ResponseEntity
    {
        $requestCollection = new RequestCollection();
        $requestCollection->addRequest($requestEntity);

        $responseCollection = $this->executeRequestCollection($client, $requestCollection);

        return $responseCollection->getResponseById($requestEntity->getId());
    }

    /**
     * @param AbstractClient $client
     * @param RequestCollection $requestEntityCollection
     * @return ResponseCollection
     * @throws InvalidResponseException
     */
    public function executeRequestCollection(AbstractClient $client, RequestCollection $requestEntityCollection): ResponseCollection
    {
        $curl = curl_init($client->getEndpointUrl());

        $requestBody = json_encode($requestEntityCollection);
        curl_setopt_array($curl,$this->getCurlOptions($client->getUser(), $requestBody));

        $this->rawResponse = curl_exec($curl);

        return $this->createResponseCollection($requestEntityCollection);
    }

    /**
     * @param ClientUser $user
     * @param string $dataString
     * @return array
     */
    protected function getHeaders(ClientUser $user, string $dataString): array
    {
        $generationTime = time();

        return [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString),
            sprintf('userName: %s', $user->getUserName()),
            sprintf('password: %s', $this->passwordEncrypt->encryptPassword($user->getPassword(), $generationTime)),
            sprintf('generationTime: %s', $generationTime),
        ];
    }

    /**
     * @param ClientUser $user
     * @param string $requestBody
     * @return array
     */
    protected function getCurlOptions(ClientUser $user, string $requestBody): array
    {
        return [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => $this->getHeaders($user, $requestBody),
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $requestBody,
        ];
    }

    /**
     * @param RequestCollection $requestEntityCollection
     * @return ResponseCollection
     * @throws InvalidResponseException
     */
    protected function createResponseCollection(RequestCollection $requestEntityCollection): ResponseCollection
    {
        return new ResponseCollection($requestEntityCollection, $this->rawResponse);
    }
}

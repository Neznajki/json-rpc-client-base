<?php


namespace JsonRpcClientBase\Contract;


use JsonRpcClientBase\AbstractClient;
use JsonRpcClientBase\DataObject\RequestCollection;
use JsonRpcClientBase\DataObject\ResponseCollection;
use JsonRpcClientBase\ValueObject\RequestEntity;
use JsonRpcClientBase\ValueObject\ResponseEntity;
use JsonRpcServerCommon\Contract\PasswordEncryptInterface;

interface RequestHandlerInterface
{
    /**
     * RequestHandlerInterface constructor.
     * @param PasswordEncryptInterface $passwordEncrypt
     */
    public function __construct(PasswordEncryptInterface $passwordEncrypt);

    /**
     *
     * @param RequestEntity $requestEntity
     * @return ResponseEntity
     */
    public function executeSingleRequest(AbstractClient $client, RequestEntity $requestEntity): ResponseEntity;

    /**
     * @param RequestCollection $requestEntityCollection
     * @return ResponseCollection
     */
    public function executeRequestCollection(AbstractClient $client, RequestCollection $requestEntityCollection): ResponseCollection;
}

<?php


namespace JsonRpcClientBase;


use JsonRpcClientBase\Contract\RequestHandlerInterface;
use JsonRpcClientBase\DataObject\RequestCollection;
use JsonRpcClientBase\DataObject\ResponseCollection;
use JsonRpcClientBase\ValueObject\ClientUser;
use JsonRpcClientBase\ValueObject\RequestEntity;
use RuntimeException;

abstract class AbstractClient
{
    /** @var ClientUser */
    protected $user;
    /** @var string */
    protected $endpointUrl;

    /**
     * @param ClientUser $clientUser
     */
    public function setUser(ClientUser $clientUser): void
    {
        $this->user = $clientUser;
    }

    /**
     * @return ClientUser
     */
    public function getUser(): ClientUser
    {
        return $this->user;
    }

    /**
     * @param string $endpointUrl
     */
    public function setEndpointUrl(string $endpointUrl): void
    {
        $this->endpointUrl = $endpointUrl;
    }

    /**
     * @return string
     */
    public function getEndpointUrl(): string
    {
        return $this->endpointUrl;
    }

    /** @var RequestHandlerInterface */
    protected $requestHandler;
    /** @var RequestCollection */
    protected $requestCollection;

    /**
     * AbstractClient constructor.
     * @param RequestHandlerInterface $requestHandler
     */
    public function __construct(RequestHandlerInterface $requestHandler)
    {
        $this->requestHandler = $requestHandler;
        $this->requestCollection = new RequestCollection();
    }

    /**
     * @param string $method
     * @param array $requestData
     * @return RequestEntity
     */
    public function addRequest(string $method, array $requestData): RequestEntity
    {
        $requestEntity = new RequestEntity(uniqid($method . '_'), $method, $requestData);
        $this->requestCollection->addRequest($requestEntity);

        return $requestEntity;
    }

    /**
     * @return ResponseCollection
     */
    public function handle(): ResponseCollection
    {
        if ($this->requestCollection->getRequestCount() <= 0) {
            throw new RuntimeException('request count should be more than 0 before running handle');
        }

        $result = $this->requestHandler->executeRequestCollection($this, $this->requestCollection);
        $this->requestCollection = new RequestCollection();

        return $result;
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->requestCollection->getRequestCount()) {
            throw new RuntimeException('found not handled requests');
        }
    }
}

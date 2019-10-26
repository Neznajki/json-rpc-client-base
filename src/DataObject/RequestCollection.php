<?php


namespace JsonRpcClientBase\DataObject;


use JsonRpcClientBase\ValueObject\RequestEntity;
use JsonSerializable;
use RuntimeException;

class RequestCollection implements JsonSerializable
{
    /** @var RequestEntity[] */
    protected $collection = [];

    /**
     * @param RequestEntity $requestEntity
     */
    public function addRequest(RequestEntity $requestEntity)
    {
        $this->collection[$requestEntity->getId()] = $requestEntity;
    }

    /**
     * @param string $requestId
     * @return RequestEntity
     */
    public function getRequestById(string $requestId): RequestEntity
    {
        if (empty($this->collection[$requestId])) {
            throw new RuntimeException("request with id ({$requestId}) does not exists");
        }

        return $this->collection[$requestId];
    }

    /**
     * @param int $pos
     * @return RequestEntity
     */
    public function getRequestByPos(int $pos): RequestEntity
    {
        $requestIdList = array_keys($this->collection);

        if (empty($requestIdList[$pos])) {
            throw new RuntimeException('request could not be found by pos %d totals %d', $pos, count($this->collection));
        }

        return $this->collection[$requestIdList[$pos]];
    }

    /**
     * @return int
     */
    public function getRequestCount(): int
    {
        return count($this->collection);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->collection;
    }
}

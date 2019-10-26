<?php


namespace JsonRpcClientBase\ValueObject;


use InvalidArgumentException;
use JsonSerializable;

class RequestEntity implements JsonSerializable
{
    /** @var string */
    protected $id;
    /** @var array|bool|float|int|JsonSerializable|string|null */
    protected $requestData;
    /** @var string */
    protected $rpcVersion;
    /** @var string */
    protected $method;


    /**
     * RequestEntity constructor.
     * @param string $id multiple requests with same id won't be transferred during single request
     * @param string $method
     * @param string|integer|bool|null|JsonSerializable|float|array $requestData // json serializable data
     * @param string $rpcVersion
     */
    public function __construct(string $id, string $method, $requestData, string $rpcVersion = '2.0')
    {
        $this->id          = $id;
        $this->requestData = $requestData;
        $this->rpcVersion  = $rpcVersion;
        $this->method      = $method;

        if (json_encode($requestData) === null) {
            $errorMessage = json_last_error_msg();
            $errorCode    = json_last_error();
            throw new InvalidArgumentException("invalid json data ({$errorMessage} : {$errorCode})");
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array|bool|float|int|JsonSerializable|string|null
     */
    public function getRequestData()
    {
        return $this->requestData;
    }

    /**
     * @return string
     */
    public function getRpcVersion(): string
    {
        return $this->rpcVersion;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        $result = [
            'jsonrpc' => $this->getRpcVersion(),
            'id'      => $this->getId(),
            'method'  => $this->getMethod(),
        ];

        if ($this->getRequestData() !== null) {
            $result['params'] = $this->getRequestData();
        }

        return $result;
    }
}

<?php


namespace JsonRpcClientBase\Exceptions;


use Exception;
use JsonRpcClientBase\DataObject\RequestCollection;
use RuntimeException;

class InvalidResponseException extends Exception
{
    /** @var string */
    protected $rawResponse;
    /** @var RequestCollection */
    protected $requestCollection;

    /**
     * InvalidResponseException constructor.
     * @param RequestCollection $requestCollection
     * @param string $rawResponse
     */
    public function __construct(RequestCollection $requestCollection, string $rawResponse)
    {
        $responseData = json_decode($rawResponse, true);
        if ($responseData !== null) {
            throw new RuntimeException('raw response is json fromat');
        }

        $errorMessage = json_last_error_msg();
        $errorCode    = json_last_error();

        parent::__construct(sprintf('received not json response from server, json parse error (%s, %d)', $errorMessage, $errorCode), -32603);
        $this->rawResponse = $rawResponse;
        $this->requestCollection = $requestCollection;
    }

    /**
     * @return string
     */
    public function getRawResponse(): string
    {
        return $this->rawResponse;
    }

    /**
     * @return RequestCollection
     */
    public function getRequestCollection(): RequestCollection
    {
        return $this->requestCollection;
    }
}

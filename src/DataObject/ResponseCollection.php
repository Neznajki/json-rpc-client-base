<?php


namespace JsonRpcClientBase\DataObject;


use JsonRpcClientBase\Exceptions\InvalidResponseException;
use JsonRpcClientBase\ValueObject\ResponseEntity;
use RuntimeException;

class ResponseCollection
{
    /** @var ResponseEntity[] */
    protected $collection = [];
    /** @var ResponseEntity[] */
    protected $unknownCollection = [];
    /** @var string */
    protected $rawResponse;
    /** @var RequestCollection */
    protected $requestCollection;

    /**
     * ResponseCollection constructor.
     * @param RequestCollection $requestCollection
     * @param string $rawResponse
     * @throws InvalidResponseException
     */
    public function __construct(RequestCollection $requestCollection, string $rawResponse)
    {
        if ($requestCollection->getRequestCount() < 1) {
            throw new RuntimeException('request count should be 1 or more');
        }

        $this->rawResponse = $rawResponse;
        $responseData      = json_decode($rawResponse, true);
        if ($responseData === null) {
            throw new InvalidResponseException($requestCollection, $rawResponse);
        }

        $this->requestCollection = $requestCollection;

        reset($responseData);

        if (key($responseData) === 0) {
            $this->addBatchResponse($requestCollection, $responseData);
        } else {
            $this->addSingleRequestResponse($requestCollection, $responseData);
        }

        foreach ($this->unknownCollection as $itemPos => $responseEntity) {
            $request = $this->requestCollection->getRequestByPos($itemPos);

            if ($this->getResponseById($request->getId())) {
                throw new RuntimeException('unknown response could not be detected, mixed order of response items');
            }

            $this->addResponse(
                new ResponseEntity(
                    $this->requestCollection, [
                        'id'    => $request->getId(),
                        'error' => $responseEntity->getError(),
                    ]
                )
            );
        }
    }

    /**
     * @param ResponseEntity $responseEntity
     */
    public function addResponse(ResponseEntity $responseEntity)
    {
        $this->collection[$responseEntity->getId()] = $responseEntity;
    }

    /**
     * @param string $requestId
     * @return ResponseEntity
     */
    public function getResponseById(string $requestId): ?ResponseEntity
    {
        if (empty($this->collection[$requestId])) {
            return null;
        }

        return $this->collection[$requestId];
    }

    /**
     * @param array $singleResponseData
     * @param int $requestNum
     */
    protected function createResponseEntity(array $singleResponseData, int $requestNum): void
    {
        try {
            $this->addResponse(new ResponseEntity($this->requestCollection, $singleResponseData));
        } catch (RuntimeException $exception) {
            $uniqid                  = uniqid($requestNum . '_');
            $this->unknownCollection[] = new ResponseEntity(
                null,
                [
                    'id'    => $uniqid,
                    'error' => [
                        'message' => sprintf('unknown response for request (%s)', $exception->getMessage()),
                        'code'    => -32603,
                    ],
                ]
            );
        }
    }

    /**
     * @param RequestCollection $requestCollection
     * @param $responseData
     */
    protected function addBatchResponse(RequestCollection $requestCollection, $responseData): void
    {
        if (count($responseData) !== $requestCollection->getRequestCount()) {
            throw new RuntimeException(
                sprintf('expected %d responses got %d', $requestCollection->getRequestCount(), count($responseData))
            );
        }

        foreach ($responseData as $requestNum => $singleResponseData) {
            $this->createResponseEntity($singleResponseData, $requestNum);
        }
    }

    /**
     * @param RequestCollection $requestCollection
     * @param $responseData
     */
    protected function addSingleRequestResponse(RequestCollection $requestCollection, $responseData): void
    {
        if ($requestCollection->getRequestCount() > 1) {
            throw new RuntimeException('requests are more than responses');
        }

        $this->createResponseEntity($responseData, 0);
    }
}

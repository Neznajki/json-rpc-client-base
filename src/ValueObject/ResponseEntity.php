<?php


namespace JsonRpcClientBase\ValueObject;


use JsonRpcClientBase\DataObject\RequestCollection;
use RuntimeException;

class ResponseEntity
{
    /** @var RequestEntity */
    protected $request;
    /** @var string */
    protected $id;
    /** @var array */
    protected $error;
    /** @var mixed */
    protected $result;

    /**
     * ResponseEntity constructor.
     * @param RequestCollection|null $requestCollection
     * @param array $responseData
     */
    public function __construct(?RequestCollection $requestCollection, array $responseData)
    {
        if (empty($responseData['id'])) {
            throw new RuntimeException(sprintf('response field id is mandatory response : %s', json_encode($responseData)));
        }

        $this->id = $responseData['id'];

        if ($requestCollection) {
            $this->request = $requestCollection->getRequestById($responseData['id']);
        }
        if (! empty($responseData['result'])) {
            $this->result = $responseData['result'];
            return;
        }

        if (! empty($responseData['error'])) {
            $this->error = $responseData['error'];

            if (! array_key_exists('code', $this->error)) {
                throw new RuntimeException('field error.code is mandatory for error response');
            }

            if (! is_int($this->error['code'])) {
                if (! preg_match('/^-?[0-9]+$/', (string) $this->error['code'])) {
                    throw new RuntimeException('field error.code should be integer');
                }

                $this->error['code'] = (int)$this->error['code'];
            }

            if (empty($this->error['message'])) {
                throw new RuntimeException('field error.message is mandatory for error response (should be not empty)');
            }

            return;
        }

        throw new RuntimeException("server response should have result or error field");
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return array
     */
    public function getError(): array
    {
        return $this->error;
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->error['code'];
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->error['message'];
    }
}

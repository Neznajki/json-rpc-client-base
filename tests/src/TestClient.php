<?php declare(strict_types=1);

namespace Tests\JsonRpcClientBase;


use JsonRpcClientBase\AbstractClient;
use JsonRpcClientBase\ValueObject\RequestEntity;
use JsonRpcClientBase\ValueObject\ResponseEntity;

class TestClient extends AbstractClient
{

    /**
     * @param string $param1
     * @param int $param2
     * @return ResponseEntity
     */
    public function ping(string $param1, int $param2): ResponseEntity
    {
        $request = $this->addPing($param1, $param2);

        return $this->handle()->getResponseById($request->getId());
    }

    /**
     * @param string $param1
     * @param int $param2
     * @return RequestEntity
     */
    protected function addPing(string $param1, int $param2): RequestEntity
    {
        return $this->addRequest(__FUNCTION__, ['param1' => $param1, 'param2' => $param2]);
    }
}

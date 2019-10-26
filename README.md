# json-rpc-client-base
client base to make json rpc requests

# installation
* composer require neznajki/json-rpc-client-base
* extend
```php
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
```
* define required items
```yaml
serivces:
    TestClient:
        class: TestClient
        autowire: true
        calls:
            - method: setUser
              arguments:
                  - '@JsonRpcClientBase\ValueObject\ClientUser'
            - method: setEndpointUrl
              arguments:
                  - 'http://myCoolDev.com'

    JsonRpcClientBase\Contract\RequestHandlerInterface:
        class: JsonRpcClientBase\RequestHandler\CurlRequestHandler

    JsonRpcServerCommon\Contract\PasswordEncryptInterface:
        class: JsonRpcServerCommon\Service\DefaultPasswordEncryptService

```

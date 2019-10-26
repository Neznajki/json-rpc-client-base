<?php


namespace JsonRpcClientBase\ValueObject;


class ClientUser
{

    /** @var string */
    protected $userName;
    /** @var string */
    protected $password;

    /**
     * ClientUser constructor.
     * @param string $userName
     * @param string $password
     */
    public function __construct(string $userName, string $password)
    {
        $this->userName = $userName;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}

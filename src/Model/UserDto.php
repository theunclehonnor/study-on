<?php


namespace App\Model;


use JMS\Serializer\Annotation as Serializer;

class UserDto
{
    private $email;

    /**
     * @Serializer\Type("string")
     */
    private $username;

    /**
     * @Serializer\Type("string")
     */
    private $password;

    /**
     * @Serializer\Type("array")
     */
    private $roles;

    /**
     * @Serializer\Type("float")
     */
    private $balance;

    /**
     * @Serializer\Type("string")
     */
    private $token;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
        $this->email= $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(?array $roles): void
    {
        $this->roles = $roles;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): void
    {
        $this->balance = $balance;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }
}
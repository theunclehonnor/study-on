<?php


namespace App\Tests\Mock;

use App\Exception\BillingUnavailableException;
use App\Exception\ClientException;
use App\Model\UserDto;
use App\Service\BillingClient;

class BillingClientMock extends BillingClient
{
    public function auth(string $request): array
    {
        $data = json_decode($request, true);
        if ($data['username'] === 'user@yandex.ru' && $data['password'] === 'user123') {
            return [
                'token' => $this->generateToken('ROLE_USER', 'user@yandex.ru'),
                'username' => 'user@yandex.ru',
                'roles' => ["ROLE_USER"]
            ];
        }
        if ($data['username'] === 'admin@yandex.ru' && $data['password'] === 'admin123') {
            return [
                'token' => $this->generateToken('ROLE_SUPER_ADMIN', 'admin@yandex.ru'),
                'username' => 'user@yandex.ru',
                'roles' => ["ROLE_SUPER_ADMIN", "ROLE_USER"]
            ];
        }
        throw new BillingUnavailableException('Проверьте правильность введёного логина и пароля');
    }

    public function register(UserDto $dataUser): UserDto
    {
        // Симуляция обработки уже существующих пользователей
        if($dataUser->getUsername() === 'user@yandex.ru' | $dataUser->getUsername() === 'admin@yandex.ru') {
            throw new ClientException('Данный пользователь уже существует');
        }
        $token = $this->generateToken('ROLE_USER', $dataUser->getUsername());
        $dataUser->setToken($token);
        $dataUser->setBalance(0);
        $dataUser->setRoles(["ROLE_USER"]);
        return $dataUser;
    }

    private function generateToken(string $role, string $username): string
    {
        $roles = null;
        if ($role === 'ROLE_USER') {
            $roles = ["ROLE_USER"];
        } elseif ($role === 'ROLE_SUPER_ADMIN') {
            $roles = ["ROLE_SUPER_ADMIN", "ROLE_USER"];
        }
        $data = [
            'username' => $username,
            'roles' => $roles,
            'exp' => (new \DateTime('+ 1 hour'))->getTimestamp(),
        ];
        $query = base64_encode(json_encode($data));
        return 'header.' . $query . '.signature';
    }
}

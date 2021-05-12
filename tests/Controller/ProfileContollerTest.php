<?php


namespace App\Tests\Controller;

use App\Tests\AbstractTest;
use App\Tests\Authorization\Auth;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;

class ProfileContollerTest extends AbstractTest
{
    /** @var SerializerInterface */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$container->get(SerializerInterface::class);
    }

    // Тест профиля пользователя
    public function testProfileUser(): void
    {
        // Для начала нам надо авторизоваться
        $auth = new Auth();
        $auth->setSerializer($this->serializer);
        // Формируем данные для авторизации, будем авторизовываться обычным пользователем
        $data = [
            'username' => 'user@yandex.ru',
            'password' => 'user123'
        ];
        $requestData = $this->serializer->serialize($data, 'json');
        $crawler = $auth->auth($requestData);

        $client = self::getClient();
        $client->request('GET', '/profile/');
        self::assertResponseIsSuccessful();
    }
}

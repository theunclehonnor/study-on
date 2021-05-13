<?php


namespace App\Tests\Authorization;

use App\Model\UserDto;
use App\Service\BillingClient;
use App\Service\DecodingJwt;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;
use Symfony\Bundle\FrameworkBundle\Client;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class Auth extends AbstractTest
{
    private $serializer;

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function auth(string $data)
    {
        /** @var UserDto $userDto */
        $userDto = $this->serializer->deserialize($data, UserDto::class, 'json');
        // Заменяем сервис
        $this->getBillingClient();
        $client = self::getClient();
        // Переходим на страницу с формой для авторизации
        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();
        // Заполняем форму
        $form = $crawler->selectButton('Войти')->form();
        $form['email'] = $userDto->getUsername();
        $form['password'] = $userDto->getPassword();
        $client->submit($form);
        // Проверяем ошибки
        $error = $crawler->filter('#errors');
        self::assertCount(0, $error);
        // Проверяем, что пользователя редиректнуло на страницу с курсами
        $crawler = $client->followRedirect();
        $this->assertResponseOk();
        self::assertEquals('/courses/', $client->getRequest()->getPathInfo());
        return $crawler;
    }

    // Метод для замены сервиса билинга на Mock версию для тестов
    public function getBillingClient(): void
    {
        // запрещаем перезагрузку ядра, чтобы не сбросилась подмена сервиса при запросе
        self::getClient()->disableReboot();
        // подмена сервиса
        self::getClient()->getContainer()->set(
            BillingClient::class,
            new BillingClientMock($this->serializer)
        );
    }
}

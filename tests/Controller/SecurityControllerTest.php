<?php


namespace App\Tests\Controller;

use App\Tests\AbstractTest;
use App\Tests\Authorization\Auth;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends AbstractTest
{
    /** @var SerializerInterface */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$container->get(SerializerInterface::class);
    }

    // Тесты авторизации пользователя в системе
    public function testAuth(): void
    {
        //____________________________Успешная авторизация____________________________
        $auth = new Auth();
        $auth->setSerializer($this->serializer);
        // Формируем данные для авторизации
        $data = [
            'username' => 'user@yandex.ru',
            'password' => 'user123'
        ];
        $requestData = $this->serializer->serialize($data, 'json');
        $crawler = $auth->auth($requestData);

        //____________________________Неуспешная авторизация____________________________
        $client = self::getClient();

        // Выйдем из прошлого аккаунта
        $linkLogout = $crawler->selectLink('Выход')->link();
        $crawler = $client->click($linkLogout);
        $this->assertResponseRedirect();
        self::assertEquals('/logout', $client->getRequest()->getPathInfo());

        // Редиректит на страницу /
        $crawler = $client->followRedirect();
        $this->assertResponseRedirect();
        self::assertEquals('/', $client->getRequest()->getPathInfo());
        // Редиректит на страницу /courses/
        $crawler = $client->followRedirect();
        self::assertEquals('/courses/', $client->getRequest()->getPathInfo());
        // Переходим на страницу /login
        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();

        // Формируем данные для авторизации, где будет неверный пароль
        $data = [
            'username' => 'user@yandex.ru',
            'password' => 'user'
        ];
        $requestData = $this->serializer->serialize($data, 'json');
        // Авторизация пользователя
        $auth = new Auth();
        $auth->setSerializer($this->serializer);

        $requestData = json_decode($requestData, true);

        // Заполняем форму
        $form = $crawler->selectButton('Войти')->form();
        $form['email'] = $requestData['username'];
        $form['password'] = $requestData['password'];
        $client->submit($form);

        // Проверяем, что редиректа на курсы не произойдет
        self::assertFalse($client->getResponse()->isRedirect('/courses/'));
        $crawler = $client->followRedirect();

        // Проверяем, что пояивлась ошибка
        $error = $crawler->filter('#errors');
        self::assertEquals('Проверьте правильность введёного логина и пароля', $error->text());
    }

    // Тесты для проверки регистрации пользователя
    public function testRegister(): void
    {
        $auth = new Auth();
        $auth->setSerializer($this->serializer);
        $auth->getBillingClient();
        //____________________________Валидные значения при регистрации____________________________
        // Переходим на страницу регистрации
        $client = static::getClient();
        $crawler = $client->request('GET', '/register');

        // Проверка статуса ответа
        $this->assertResponseOk();

        // Работа с формой
        $form = $crawler->selectButton('Зарегистрироваться')->form();
        $form['registration[username]'] = 'intaro@intaro.ru';
        $form['registration[password][first]'] = 'intaro123';
        $form['registration[password][second]'] = 'intaro123';

        // Отправляем форму
        $crawler = $client->submit($form);

        // Проверяем список с ошибками (если есть)
        $errors = $crawler->filter('span.form-error-message');
        self::assertCount(0, $errors);

        // Редирект на /courses/
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();
        $this->assertResponseOk();
        self::assertEquals('/courses/', $client->getRequest()->getPathInfo());

        //____________________________Невалидные значения при регистрации____________________________
        // Для начала выйдем из аккаунта под которым зарегистрировались
        $linkLogout = $crawler->selectLink('Выход')->link();
        $crawler = $client->click($linkLogout);
        $this->assertResponseRedirect();
        self::assertEquals('/logout', $client->getRequest()->getPathInfo());

        // Редиректит на страницу /
        $crawler = $client->followRedirect();
        $this->assertResponseRedirect();
        self::assertEquals('/', $client->getRequest()->getPathInfo());
        // Редиректит на страницу /courses/
        $crawler = $client->followRedirect();
        self::assertEquals('/courses/', $client->getRequest()->getPathInfo());
        // Переходим на страницу /login
        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();

        // Переходим на страницу регистрации
        $client = static::getClient();
        $crawler = $client->request('GET', '/register');

        // Проверка статуса ответа
        $this->assertResponseOk();

        // Заполнение формы с пустыми значениями
        $form = $crawler->selectButton('Зарегистрироваться')->form();
        $form['registration[username]'] = '';
        $form['registration[password][first]'] = '';
        $form['registration[password][second]'] = '';

        // Отправляем форму
        $crawler = $client->submit($form);

        // Получаем список ошибок
        $errors = $crawler->filter('span.form-error-message');
        self::assertCount(2, $errors);

        // Текст ошибок
        $errorsMessage = $errors->each(function (Crawler $node) {
            return $node->text();
        });

        // Проверка сообщений
        self::assertEquals('Введите Email', $errorsMessage[0]);
        self::assertEquals('Введите пароль', $errorsMessage[1]);

        // Заполнение формы с неправильным email и коротким паролем
        $form['registration[username]'] = 'intaro';
        $form['registration[password][first]'] = '123';
        $form['registration[password][second]'] = '123';

        // Отправляем форму
        $crawler = $client->submit($form);

        // Получаем список ошибок
        $errors = $crawler->filter('span.form-error-message');
        self::assertCount(2, $errors);

        // Получение текста ошибок
        $errorsMessage = $errors->each(function (Crawler $node, $i) {
            return $node->text();
        });

        // Проверка сообщений
        self::assertEquals('Неверно указан Email', $errorsMessage[0]);
        self::assertEquals('Ваш пароль менее 6 символов', $errorsMessage[1]);

        // Заполнение формы с разными паролями
        $form['registration[username]'] = 'intaro@intaro.ru';
        $form['registration[password][first]'] = '123456';
        $form['registration[password][second]'] = '123';

        // Отправляем форму
        $crawler = $client->submit($form);

        // Получаем список ошибок
        $errors = $crawler->filter('span.form-error-message');
        self::assertCount(1, $errors);

        // Получение текста ошибок
        $errorsValues = $errors->each(function (Crawler $node, $i) {
            return $node->text();
        });

        // Проверка сообщений
        self::assertEquals('Пароли должны совпадать', $errorsValues[0]);
    }
}
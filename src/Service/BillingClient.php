<?php


namespace App\Service;

use App\Exception\BillingUnavailableException;
use App\Exception\ClientException;
use App\Model\CourseDto;
use App\Model\PayDto;
use App\Model\UserDto;
use App\Security\User;
use JMS\Serializer\SerializerInterface;

class BillingClient
{
    private $startUri;
    protected $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->startUri = $_ENV['BILLING'];
        $this->serializer = $serializer;
    }

    public function refreshToken(string $refreshToken): UserDto
    {
        // Запрос в сервис биллинг
        $query = curl_init($this->startUri . '/api/v1/token/refresh');
        curl_setopt($query , CURLOPT_POST, 1);
        curl_setopt($query , CURLOPT_POSTFIELDS, $refreshToken);
        curl_setopt($query, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($query, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($refreshToken)
        ]);
        $response = curl_exec($query);
        // Ошибка с биллинга
        if ($response === false) {
            throw new BillingUnavailableException('Сервис временно недоступен. 
            Попробуйте авторизоваться позднее');
        }

        /** @var UserDto $userDto */
        $userDto = $this->serializer->deserialize($response, UserDto::class, 'json');

        return $userDto;
    }

    /**
     * @throws BillingUnavailableException
     */
    public function auth(string $request): UserDto
    {
        // Запрос в сервис биллинг
        $query = curl_init($this->startUri . '/api/v1/auth');
        curl_setopt($query , CURLOPT_POST, 1);
        curl_setopt($query , CURLOPT_POSTFIELDS, $request);
        curl_setopt($query, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($query, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($request)
        ]);
        $response = curl_exec($query);
        // Ошибка с биллинга
        if ($response === false) {
            throw new BillingUnavailableException('Возникли технические неполадки. Попробуйте позднее');
        }
        curl_close($query);

        // Ответа от сервиса
        $result = json_decode($response, true);
        if (isset($result['code'])){
            if($result['code'] === 401)
                throw new BillingUnavailableException('Проверьте правильность введёного логина и пароля');
        }
        /** @var UserDto $userDto */
        $userDto = $this->serializer->deserialize($response, UserDto::class, 'json');

        return $userDto;
    }

    /**
     * @throws BillingUnavailableException
     */
    public function getCurrentUser(User $user, DecodingJwt $decodingJwt)
    {
        // Декодируем токен
        $decodingJwt->decoding($user->getApiToken());

        // Запрос в сервис биллинг, получение данных
        $query = curl_init($this->startUri . '/api/v1/users/current');
        curl_setopt($query, CURLOPT_HTTPGET, 1);
        curl_setopt($query, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($query, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $user->getApiToken()
        ]);
        $response = curl_exec($query);
        // Ошибка с биллинга
        if ($response === false) {
            throw new BillingUnavailableException('Сервис временно недоступен. 
            Попробуйте авторизоваться позднее');
        }
        curl_close($query);

        // Ответа от сервиса
        $result = json_decode($response, true);
        if (isset($result['code'])) {
            throw new BillingUnavailableException($result['message']);
        }

        return $response;
    }

    /**
     * @throws BillingUnavailableException
     * @throws ClientException
     */
    public function register(UserDto $dataUser): UserDto
    {
        $dataSerialize = $this->serializer->serialize($dataUser, 'json');
        // Запрос в сервис биллинг
        $query = curl_init($this->startUri . '/api/v1/register');
        curl_setopt($query, CURLOPT_POST, 1);
        curl_setopt($query, CURLOPT_POSTFIELDS, $dataSerialize);
        curl_setopt($query, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($query, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataSerialize)
        ]);
        $response = curl_exec($query);

        // Ошибка с биллинга
        if ($response === false) {
            throw new BillingUnavailableException('Сервис временно недоступен. 
            Попробуйте зарегистрироваться позднее');
        }
        // Ответа от сервиса
        $result = json_decode($response, true);
        if (isset($result['code'])) {
            if($result['code'] == 403)
                throw new ClientException($result['message']);
            else
                throw new BillingUnavailableException('Сервис временно недоступен. 
            Попробуйте зарегистрироваться позднее');
        }
        curl_close($query);

        /** @var UserDto $userDto */
        $userDto = $this->serializer->deserialize($response, UserDto::class, 'json');

        return $userDto;
    }

    /**
     * @throws BillingUnavailableException
     */
    public function getAllCourses(): array
    {
        // Запрос в сервис биллинг, получение данных
        $query = curl_init($this->startUri . '/api/v1/courses');
        curl_setopt($query, CURLOPT_HTTPGET, 1);
        curl_setopt($query, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($query, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        $response = curl_exec($query);
        // Ошибка с биллинга
        if ($response === false) {
            throw new BillingUnavailableException('Сервис временно недоступен. 
            Попробуйте позднее');
        }
        curl_close($query);

        // Ответа от сервиса
        $result = json_decode($response, true);
        if (isset($result['code'])) {
            throw new BillingUnavailableException($result['message']);
        }

        return $this->serializer->deserialize($response, 'array<App\Model\CourseDto>', 'json');
    }

    /**
     * @throws BillingUnavailableException
     */
    public function transactionsHistory(User $user, string $request = ''): array
    {
        // Запрос в сервис биллинг, получение данных
        $query = curl_init($this->startUri . '/api/v1/transactions/?' . $request);
        curl_setopt($query, CURLOPT_HTTPGET, 1);
        curl_setopt($query, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($query, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $user->getApiToken()
        ]);
        $response = curl_exec($query);
        // Ошибка с биллинга
        if ($response === false) {
            throw new BillingUnavailableException('Сервис временно недоступен. 
            Попробуйте позднее');
        }
        curl_close($query);

        // Ответа от сервиса
        $result = json_decode($response, true);
        if (isset($result['code'])) {
            throw new BillingUnavailableException($result['message']);
        }

        return $this->serializer->deserialize($response, 'array<App\Model\TransactionDto>', 'json');
    }

    /**
     * @throws BillingUnavailableException
     */
    public function getCourse(string $courseCode): CourseDto
    {
        // Запрос в сервис биллинг, получение данных
        $query = curl_init($this->startUri . '/api/v1/courses/' . $courseCode);
        curl_setopt($query, CURLOPT_HTTPGET, 1);
        curl_setopt($query, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($query, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        $response = curl_exec($query);
        // Ошибка с биллинга
        if ($response === false) {
            throw new BillingUnavailableException('Сервис временно недоступен. 
            Попробуйте позднее');
        }
        curl_close($query);

        return $this->serializer->deserialize($response, CourseDto::class, 'json');
    }

    public function paymentCourse(User $user, string $codeCourse): PayDto
    {
        // Запрос в сервис биллинг, получение данных
        $query = curl_init($this->startUri . '/api/v1/courses/' . $codeCourse . '/pay');
        curl_setopt($query, CURLOPT_POST, 1);
        curl_setopt($query, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($query, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $user->getApiToken()
        ]);
        $response = curl_exec($query);
        // Ошибка с биллинга
        if ($response === false) {
            throw new BillingUnavailableException('Сервис временно недоступен. 
            Попробуйте позднее');
        }
        curl_close($query);

        // Ответа от сервиса
        $result = json_decode($response, true);
        if (isset($result['code'])) {
            throw new BillingUnavailableException($result['message']);
        }

        return $this->serializer->deserialize($response, PayDto::class, 'json');
    }
}
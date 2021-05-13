<?php


namespace App\Tests\Mock;

use App\Entity\Course;
use App\Exception\BillingUnavailableException;
use App\Exception\ClientException;
use App\Model\CourseDto;
use App\Model\TransactionDto;
use App\Model\UserDto;
use App\Security\User;
use App\Service\BillingClient;
use App\Service\DecodingJwt;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BillingClientMock extends BillingClient
{
    /** @var UserDto */
    private $userDefault;

    /** @var UserDto */
    private $userSuperAdmin;

    /** @var CourseDto[]  */
    private $courses;
    private $typesCourse;

    /** @var TransactionDto[]  */
    private $transactions;
    private $typesTransaction;

    public function __construct(SerializerInterface $serializer)
    {
        parent::__construct($serializer);

        // Обычный пользователь
        $this->userDefault = new UserDto();
        $this->userDefault->setUsername('user@yandex.ru');
        $this->userDefault->setPassword('user123');
        $this->userDefault->setRoles(["ROLE_USER"]);
        $this->userDefault->setBalance(50000);

        // Супер админ пользователь
        $this->userSuperAdmin = new UserDto();
        $this->userSuperAdmin->setUsername('admin@yandex.ru');
        $this->userSuperAdmin->setPassword('admin123');
        $this->userSuperAdmin->setRoles(["ROLE_SUPER_ADMIN"]);
        $this->userSuperAdmin->setBalance(0);

        // Курсы
        $this->typesCourse = [
            1 => 'rent',
            2 => 'free',
            3 => 'buy',
        ];

        $dataCourse = [
            // Арендные
            [
                'code' => 'AREND199230SKLADS',
                'title' => 'Портфель роста 2021',
                'type' => $this->typesCourse[1],
                'price' => 2021,
            ],
            [
                'code' => 'AREND948120385129',
                'title' => 'Успешная торговля каждый день',
                'type' => $this->typesCourse[1],
                'price' => 1000,
            ],
            [
                'code' => 'AREND318305889120',
                'title' => 'Покупай/продовай на сигналах. Ленивый трейдинг',
                'type' => $this->typesCourse[1],
                'price' => 3000,
            ],
            // Бесплатные курсы
            [
                'code' => 'BPSKSODSAJGJSKAOD983A',
                'title' => 'C чего начать новичку?',
                'type' => $this->typesCourse[2],
                'price' => 0,
            ],
            [
                'code' => 'JZLAO2390KSALLFASK123',
                'title' => 'Как выбрать надежного брокера?',
                'type' => $this->typesCourse[2],
                'price' => 0
            ],
            // Покупные
            [
                'code' => 'MLSADKLD13213KSDMDNVM35',
                'title' => 'Основы рынка',
                'type' => $this->typesCourse[3],
                'price' => 15000,
            ],
            [
                'code' => 'QNDIQJWDALSDASDJGLSAD',
                'title' => 'Инвестор',
                'type' => $this->typesCourse[3],
                'price' => 50000,
            ],
            [
                'code' => 'MSALDLGSALDFJASLDDASODP',
                'title' => 'Трейдер',
                'type' => $this->typesCourse[3],
                'price' => 65000,
            ],
        ];
        $json = $this->serializer->serialize($dataCourse, 'json');
        $this->courses = $this->serializer->deserialize($json, 'array<App\Model\CourseDto>', 'json');

        // Транзакции
        $this->typesTransaction = [
            1 => 'payment',
            2 => 'deposit',
        ];

        $transactionDeposit = new TransactionDto();
        $transactionDeposit->setId(1);
        $transactionDeposit->setAmount(50000);
        $transactionDeposit->setCreatedAt('2021-04-22 UTC 00:00:00');
        $transactionDeposit->setType($this->typesTransaction[2]);

        $transactionPayment = new TransactionDto();
        $transactionPayment->setId(2);
        $transactionPayment->setAmount($this->courses[5]->getPrice());
        $transactionPayment->setCreatedAt((new \DateTime())->format('Y-m-d T H:i:s'));
        $transactionPayment->setType($this->typesTransaction[1]);
        $transactionPayment->setCourseCode($this->courses[5]->getCode());

        $this->transactions = [
            $transactionDeposit,
            $transactionPayment,
        ];

        $this->userDefault->setBalance($this->userDefault->getBalance() - $transactionPayment->getAmount());
    }

    public function auth(string $request): UserDto
    {
        /** @var UserDto $userDto  */
        $userDto =$this->serializer->deserialize($request, UserDto::class, 'json');
        if ($userDto->getUsername() === $this->userDefault->getUsername() &&
            $userDto->getPassword() === $this->userDefault->getPassword()) {
            $userDto->setToken($this->generateToken('ROLE_USER', $this->userDefault->getUsername()));
            $userDto->setRoles(["ROLE_USER"]);
            $userDto->setRefreshToken('911');
            return $userDto;
        }
        if ($userDto->getUsername() === $this->userSuperAdmin->getUsername() &&
            $userDto->getPassword() === $this->userSuperAdmin->getPassword()) {
            $userDto->setToken($this->generateToken('ROLE_SUPER_ADMIN', $this->userSuperAdmin->getUsername()));
            $userDto->setRoles(["ROLE_SUPER_ADMIN", "ROLE_USER"]);
            $userDto->setRefreshToken('911');
            return $userDto;
        }
        throw new BillingUnavailableException('Проверьте правильность введёного логина и пароля');
    }

    public function register(UserDto $dataUser): UserDto
    {
        // Симуляция обработки уже существующих пользователей
        if ($dataUser->getUsername() === $this->userDefault->getUsername()|
            $dataUser->getUsername() === $this->userSuperAdmin->getUsername()) {
            throw new ClientException('Данный пользователь уже существует');
        }
        $token = $this->generateToken('ROLE_USER', $dataUser->getUsername());
        $dataUser->setToken($token);
        $dataUser->setBalance(0);
        $dataUser->setRoles(["ROLE_USER"]);
        $dataUser->setRefreshToken('912');
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

    public function getCurrentUser(User $user, DecodingJwt $decodingJwt)
    {
        $decodingJwt->decoding($user->getApiToken());
        if ($decodingJwt->getUsername() === $this->userDefault->getUsername()) {
            $data = [
                'username' => $decodingJwt->getUsername() ,
                'roles' => $decodingJwt->getRoles(),
                'balance' => $this->userDefault->getBalance(),
            ];
            return $this->serializer->serialize($data, 'json');
        }
        if ($decodingJwt->getUsername() === $this->userSuperAdmin->getUsername()) {
            $data = [
                'username' => $decodingJwt->getUsername() ,
                'roles' => $decodingJwt->getRoles(),
                'balance' => $this->userSuperAdmin->getBalance(),
            ];
            return $this->serializer->serialize($data, 'json');
        }

        $data = [
            'username' => $decodingJwt->getUsername() ,
            'roles' => $decodingJwt->getRoles(),
            'balance' => 0,
        ];
        return $this->serializer->serialize($data, 'json');
    }

    public function getAllCourses(): array
    {
        return $this->courses;
    }

    public function transactionsHistory(User $user, string $request = ''): array
    {
        if ($request === '') {
            // декодируем токен
            $decodingJwt = new  DecodingJwt();
            $decodingJwt->decoding($user->getApiToken());

            if ($decodingJwt->getUsername() === $this->userDefault->getUsername()) {
                return $this->transactions;
            }

            if ($decodingJwt->getUsername() === $this->userSuperAdmin->getUsername()) {
                return [];
            }
        }

        $filters = explode('&', $request);

        $typesRequest = [];
        $valuesRequest= [];

        foreach ($filters as $filter) {
            $temp = explode('=', $filter);
            $typesRequest[] = $temp[0];
            $valuesRequest[$temp[0]] = $temp[1];
        }

        $responseTransactions = [];

        if (in_array('skip_expired', $typesRequest, true)
            && in_array('type', $typesRequest, true)
            && in_array('course_code', $typesRequest, true)
        ) {
            foreach ($this->transactions as $transaction) {
                if ($valuesRequest['type'] === $transaction->getType()
                    && $valuesRequest['course_code'] === $transaction->getCourseCode()
                    && (
                        (
                            $transaction->getExpiresAt() !== null &&
                            $transaction->getExpiresAt()> (new \DateTime())->format('Y-m-d T H:i:s')
                        ) |
                        (true)
                    )
                ) {
                    $responseTransactions[] = $transaction;
                }
            }
            return $responseTransactions;
        }

        if (in_array('skip_expired', $typesRequest, true)
            && in_array('type', $typesRequest, true)
        ) {
            foreach ($this->transactions as $transaction) {
                if ($valuesRequest['type'] === $transaction->getType()
                    && (
                        (
                            $transaction->getExpiresAt() !== null &&
                            $transaction->getExpiresAt()> (new \DateTime())->format('Y-m-d T H:i:s')
                        ) |
                        (true)
                    )
                ) {
                    $responseTransactions[] = $transaction;
                }
            }
            return $responseTransactions;
        }

        throw new AccessDeniedException();
    }

    public function getCourse(string $courseCode): CourseDto
    {
        // Ищем код курса на сервере
        $index = null;
        foreach ($this->courses as $key => $course) {
            if ($course->getCode() === $courseCode) {
                $index = $key;
            }
        }

        if (!$index) {
            throw new BillingUnavailableException('Данный курс не найден');
        }

        return $this->courses[$index];
    }
}

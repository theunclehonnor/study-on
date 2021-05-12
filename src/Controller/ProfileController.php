<?php


namespace App\Controller;

use App\Exception\BillingUnavailableException;
use App\Exception\ClientException;
use App\Model\TransactionDto;
use App\Model\UserDto;
use App\Repository\CourseRepository;
use App\Service\BillingClient;
use App\Service\DecodingJwt;
use Doctrine\ORM\ORMException;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile")
 */
class ProfileController extends AbstractController
{
    private $billingClient;
    private $decodingJwt;
    private $serializer;

    public function __construct(
        BillingClient $billingClient,
        DecodingJwt $decodingJwt,
        SerializerInterface $serializer
    ) {
        $this->billingClient = $billingClient;
        $this->decodingJwt = $decodingJwt;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/", name="profile")
     * @throws \Exception
     */
    public function index(): Response
    {
        try {
            $response = $this->billingClient->getCurrentUser($this->getUser(), $this->decodingJwt);
        } catch (BillingUnavailableException $e) {
            throw new \Exception($e->getMessage());
        }
        $userDto = $this->serializer->deserialize($response, UserDto::class, 'json');

        return $this->render('profile/index.html.twig', [
            'userDto' => $userDto,
        ]);
    }

    /**
     * @Route("/history", name="profile_history")
     * @throws \Exception
     */
    public function history(CourseRepository $courseRepository): Response
    {
        try {
            /** @var TransactionDto[] $transactionsDto */
            $transactionsDto = $this->billingClient->transactionsHistory($this->getUser());

            $courses = $courseRepository->findAll();

            $coursesData = [];
            foreach ($transactionsDto as $transactionDto) {
                foreach ($courses as $course) {
                    if ($transactionDto && $transactionDto->getCourseCode() === $course->getCode()) {
                        $coursesData[$transactionDto->getCourseCode()] = [
                            'id' => $course->getId(),
                            'name' => $course->getName(),
                        ];
                    }
                }
            }
        } catch (BillingUnavailableException $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this->render('profile/history.html.twig', [
            'transactionsDto' => $transactionsDto,
            'courses' => $coursesData,
        ]);
    }
}

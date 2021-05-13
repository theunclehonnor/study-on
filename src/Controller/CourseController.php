<?php

namespace App\Controller;

use App\Entity\Course;
use App\Exception\BillingUnavailableException;
use App\Form\CourseType;
use App\Model\CourseDto;
use App\Model\PayDto;
use App\Model\TransactionDto;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Service\BillingClient;
use App\Service\DecodingJwt;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/courses")
 */
class CourseController extends AbstractController
{
    /**
     * @Route("/", name="course_index", methods={"GET"})
     */
    public function index(
        CourseRepository $courseRepository,
        BillingClient $billingClient,
        DecodingJwt $decodingJwt
    ): Response {
        try {
            /** @var CourseDto[] $coursesDto */
            $coursesDto = $billingClient->getAllCourses();

            // Создаем массив, где вместо индексов code, для удобства работы с курсами
            $coursesInfoBilling = [];
            foreach ($coursesDto as $courseDto) {
                $coursesInfoBilling[$courseDto->getCode()] = [
                    'course' => $courseDto,
                    'transaction' => null,
                ];
            }

            // Если пользователь не авторизован
            if (!$this->getUser()) {
                return $this->render('course/index.html.twig', [
                    'courses' => $courseRepository->findBy([], ['id' => 'ASC']),
                    'coursesInfoBilling' => $coursesInfoBilling,
                ]);
            }

            // Нам нужны транзакции оплаты курсов пользователя, а также нам нужно пропустить курсы,
            // аренда которых уже завершилась
            /** @var TransactionDto[] $transactionsDto */
            $transactionsDto = $billingClient->transactionsHistory($this->getUser(), 'type=payment&skip_expired=true');

            // Создаем массив, где вместо индексов code, для удобства работы с курсами
            $coursesInfoBilling = [];
            foreach ($coursesDto as $courseDto) {
                foreach ($transactionsDto as $transactionDto) {
                    if ($transactionDto->getCourseCode() === $courseDto->getCode()) {
                        $coursesInfoBilling[$courseDto->getCode()] = [
                            'course' => $courseDto,
                            'transaction' => $transactionDto,
                        ];
                        break;
                    }

                    $coursesInfoBilling[$courseDto->getCode()] = [
                        'course' => $courseDto,
                        'transaction' => null,
                    ];
                }
            }

            // Получим баланс пользователя
            $response = $billingClient->getCurrentUser($this->getUser(), $decodingJwt);
            $data = json_decode($response, true);
            $balance = $data['balance'];

            return $this->render('course/index.html.twig', [
                'courses' => $courseRepository->findBy([], ['id' => 'ASC']),
                'coursesInfoBilling' => $coursesInfoBilling,
                'balance' => $balance,
            ]);
        } catch (BillingUnavailableException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @Route("/pay", name="course_pay", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pay(Request $request, BillingClient $billingClient): Response
    {
        // Откуда перешли на данную страницу для обратного редиректа
        $referer = $request->headers->get('referer');

        $courseCode = $request->get('course_code');
        try {
            /** @var PayDto $payDto */
            $payDto = $billingClient->paymentCourse($this->getUser(), $courseCode);
            // flash message
            $this->addFlash('success', 'Оплата прошла успешно! Наслаждайтесь курсом!');
        } catch (BillingUnavailableException $e) {
            throw new \Exception($e->getMessage());
        }

        return $this->redirect($referer);
    }

    /**
     * @Route("/new", name="course_new", methods={"GET","POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403, message="У вас нет доступа! Только для администратора.")
     */
    public function new(Request $request): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($course);
            $entityManager->flush();

            return $this->redirectToRoute('course_index');
        }

        return $this->render('course/new.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="course_show", methods={"GET"})
     */
    public function show(Course $course, LessonRepository $lessonRepository, BillingClient $billingClient): Response
    {
        try {
            // Проверим сначало является ли пользователь администратором
            if ($this->getUser() && $this->getUser()->getRoles()[0] === 'ROLE_SUPER_ADMIN') {
                $lessons = $lessonRepository->findByCourse($course);

                return $this->render('course/show.html.twig', [
                    'course' => $course,
                    'lessons' => $lessons,
                ]);
            }

            // Далее проверим, что курс который собираются открыть - бесплатный
            /** @var CourseDto $courseDto */
            $courseDto = $billingClient->getCourse($course->getCode());

            // Если он бесплатный, тогда ОК
            if ($courseDto->getType() === 'free') {
                $lessons = $lessonRepository->findByCourse($course);

                return $this->render('course/show.html.twig', [
                    'course' => $course,
                    'lessons' => $lessons,
                ]);
            }

            // Если курс платный, а пользователь не авторизован, то выдаем ошибку
            if (!$this->getUser()) {
                throw new AccessDeniedException('Доступ запрещен, авторизуйтесь или зарегистрируйтесь.');
            }

            // Если пользователь авторизован, то нам надо проверить историю его транзакций с этим курсом, имеет ли
            // пользователь доступ к нему

            // Нам нужно найти транзакцию оплаты курса пользователем,
            // также мы отбрасываем курсы, аренда которых уже завершилась
            /** @var TransactionDto[] $transactionsDto */
            $transactionDto = $billingClient->transactionsHistory(
                $this->getUser(),
                'type=payment&course_code='. $course->getCode() . '&skip_expired=true'
            );

            // Если такая тразакция существует, то мы выдадим курс
            if ($transactionDto !== []) {
                $lessons = $lessonRepository->findByCourse($course);

                return $this->render('course/show.html.twig', [
                    'course' => $course,
                    'lessons' => $lessons,
                ]);
            }

            // Иначе ошибка
            throw new AccessDeniedException('Доступ запрещен.');
        } catch (AccessDeniedException $e) {
            throw new \Exception($e->getMessage());
        } catch (BillingUnavailableException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @Route("/{id}/edit", name="course_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403, message="У вас нет доступа! Только для администратора.")
     */
    public function edit(Request $request, Course $course): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('course_show', [
                'id' => $course->getId(),
            ]);
        }

        return $this->render('course/edit.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="course_delete", methods={"DELETE"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403, message="У вас нет доступа! Только для администратора.")
     */
    public function delete(Request $request, Course $course): Response
    {
        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($course->getLessons() as $lesson) {
                $entityManager->remove($lesson);
                $entityManager->flush();
            }
            $entityManager->remove($course);
            $entityManager->flush();
        }

        return $this->redirectToRoute('course_index');
    }
}

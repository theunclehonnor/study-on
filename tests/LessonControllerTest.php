<?php

namespace App\Tests;

use App\DataFixtures\CoursesFixtures;
use App\Entity\Course;
use App\Entity\Lesson;

class LessonControllerTest extends AbstractTest
{
    // Стартовая страница курсов
    private $startingPathCourse = '/course';
    // Стартовая страница уроков
    private $startingPathLesson = '/lesson';

    // Метод вызова старовой страницы курсов
    public function getPathCourse(): string
    {
        return $this->startingPathCourse;
    }

    // Метод вызова старовой страницы уроков
    public function getPathLesson(): string
    {
        return $this->startingPathLesson;
    }

    // Переопределение метода для фикстур
    protected function getFixtures(): array
    {
        return [CoursesFixtures::class];
    }

    // Проверка на корректный http-статус для всех уроков по всем курсам
    public function testPageIsSuccessful(): void
    {
        // Перейдём на главную с курсами
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        // Переходим по курсам к их урокам
        $courseLinks = $crawler->filter('a.card-link')->links();
        foreach ($courseLinks as $courseLink) {
            $crawler = $client->click($courseLink);
            $this->assertResponseOk();

            // Переходим по всем урокам данного курса и проверям, что всё ок
            $lessonLinks = $crawler->filter('a.card-link')->links();
            foreach ($lessonLinks as $lessonLink) {
                $crawler = $client->click($lessonLink);
                self::assertResponseIsSuccessful();
            }
        }
    }

    // Провекра перехода на несуществующий урок
    public function testPageIsNotFound(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathLesson() . '/-1');
        $this->assertResponseNotFound();
    }

    // Тест страницы добавления урока с валидными значениями,
    // А также проверить удаление урока
    // А также редирект на страницу курса после добалвения и удаления урока
    public function testLessonNewAddValidFieldsAndDeleteCourse(): void
    {
        // Стартовая точка на главной странице с курсами
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        // Перейдём к первому, допустим, курсу по ссылке
        $link = $crawler->filter('a.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // Перейдём к добавлению  (форме)
        $link = $crawler->filter('a.lesson__new')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // Заполнение полей формы
        $form = $crawler->selectButton('lesson__add')->form();
        // Изменяем поля в форме
        $form['lesson[name]'] = 'Новый урок';
        $form['lesson[material]'] = 'Тестовый материал';
        $form['lesson[number]'] = '1';
        // Получим id созданного курса
        $em = static::getEntityManager();
        $course = $em->getRepository(Course::class)->findOneBy(['id' => $form['lesson[course]']->getValue()]);
        self::assertNotEmpty($course);
        // Отправляем форму
        $client->submit($form);
        // Проверка редиректа на страницу курса
        self::assertTrue($client->getResponse()->isRedirect($this->getPathCourse() . '/' . $course->getId()));
        // Переходим на страницу добавленного урока
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        // Перейдём на страницу добавленного урока
        $link = $crawler->filter('ol > li > a')->first()->link();
        $client->click($link);
        $this->assertResponseOk();

        // Нажимаме кнопку удалить
        $client->submitForm('lesson__delete');
        // Проверка редиректа на страницу курса
        self::assertTrue($client->getResponse()->isRedirect($this->getPathCourse() . '/' . $course->getId()));
        // Переходим на страницу редиректа
        $crawler = $client->followRedirect();
        $this->assertResponseOk();
    }

    // Тест страницы добавления курса с невалидным полем name
    public function testLessonNewAddNotValidName(): void
    {
        // Стартовая точка на главной странице с курсами
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        // Перейдём к первому, допустим, курсу по ссылке
        $link = $crawler->filter('a.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // Перейдём к добавлению  (форме)
        $link = $crawler->filter('a.lesson__new')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // Проверка передачи пустого значения в поле code
        // Заполнение полей формы
        $crawler = $client->submitForm('lesson__add', [
            'lesson[name]' => '',
            'lesson[material]' => 'Новый урок',
            'lesson[number]' => '13',
        ]);
        // Список ошибок
        $error = $crawler->filter('span.form-error-message')->first();
        self::assertEquals('Name can not be blank', $error->text());

        // Проверка передачи значения более 255 символов в поле code
        // Заполнение полей формы
        $crawler = $client->submitForm('lesson__add', [
            'lesson[name]' => 'sadjskadkasjdddddddasdkkkkkkkkk
            kkkkkkkasdkkkkkkkkkkkkkkkkkkasdllllllllllllllllllllllllll
            llllllllllllllllasdjjjjjjjjjjjjjjjjjjjjjjjjjjjjjjjjjjjjjjjj
            jjjjasdllllllllllllllllllllllllllllsadkasdkasdknqowhduiqbwd
            noskznmdoasmpodpasmdpamsd',
            'lesson[material]' => 'Новый урок',
            'lesson[number]' => '13',
        ]);
        // Список ошибок
        $error = $crawler->filter('span.form-error-message')->first();
        self::assertEquals('Name max length is 255 symbols', $error->text());
    }

    // Тест страницы добавления урока с невалидным полем material
    public function testLessonNewAddNotValidMaterial(): void
    {
        // Стартовая точка на главной странице с курсами
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        // Перейдём к первому, допустим, курсу по ссылке
        $link = $crawler->filter('a.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // Перейдём к добавлению  (форме)
        $link = $crawler->filter('a.lesson__new')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // Проверка передачи пустого значения в поле material
        // Заполнение полей формы
        $crawler = $client->submitForm('lesson__add', [
            'lesson[name]' => 'Новый урок',
            'lesson[material]' => '',
            'lesson[number]' => '13',
        ]);
        // Список ошибок
        $error = $crawler->filter('span.form-error-message')->first();
        self::assertEquals('Material field can not be empty', $error->text());
    }

    // Тест страницы добавления урока с невалидным полем number
    public function testLessonNewAddNotValidNumber(): void
    {
        // Стартовая точка на главной странице с курсами
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        // Перейдём к первому, допустим, курсу по ссылке
        $link = $crawler->filter('a.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // Перейдём к добавлению  (форме)
        $link = $crawler->filter('a.lesson__new')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // Проверка передачи пустого значения в поле number
        // Заполнение полей формы
        $crawler = $client->submitForm('lesson__add', [
            'lesson[name]' => 'Новый урок',
            'lesson[material]' => 'Новый материал',
            'lesson[number]' => '',
        ]);
        // Список ошибок
        $error = $crawler->filter('span.form-error-message')->first();
        self::assertEquals('Number field can not be empty', $error->text());

        // Проверка передачи значения неверной валидации номера
        // Заполнение полей формы
        $crawler = $client->submitForm('lesson__add', [
            'lesson[name]' => 'Новый урок',
            'lesson[material]' => 'Новый материал',
            'lesson[number]' => 'sadk123!!_',
        ]);
        // Список ошибок
        $error = $crawler->filter('span.form-error-message')->first();
        self::assertEquals('This value number is not valid', $error->text());
    }

    // Тест страницы редактирование урока, а именно - изменение полей и редирект на испарвленный урок
    // Проверка валдиации формы мы проверили в тестах выше
    public function testLessonEditAndCheckFields(): void
    {
        // Стартовая точка на главной странице с курсами
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        // Перейдём к первому, допустим, курсу по ссылке
        $link = $crawler->filter('a.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // Перейдём к первому уроку
        $link = $crawler->filter('ol > li > a')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // Нажмём на ссылку редактирования курса
        $link = $crawler->filter('a.lesson__edit')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // Заполнение полей формы
        $form = $crawler->selectButton('lesson__add')->form();
        // Получаем урок по номеру
        $em = self::getEntityManager();
        $lesson = $em->getRepository(Lesson::class)->findOneBy([
            'number' => $form['lesson[number]']->getValue(),
            'course' => $form['lesson[course]']->getValue(),
        ]);
        // Изменяем поля в форме
        $form['lesson[name]'] = 'New lesson';
        $form['lesson[material]'] = 'Test material';
        // Отправляем форму
        $client->submit($form);
        // Проверка редиректа на страницу урока
        self::assertTrue($client->getResponse()->isRedirect($this->getPathLesson() . '/' . $lesson->getId()));
        // Переходим на страницу редиректа
        $crawler = $client->followRedirect();
        $this->assertResponseOk();
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CoursesFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $coursesObject = [
            [
                'code' => 'AREND199230SKLADS',
                'name' => 'Портфель роста 2021',
                'description' => 'Собираем портфель роста в течении года. Арендуй курс, и не пропускай инвест-идеи!',
            ],
            [
                'code' => 'AREND948120385129',
                'name' => 'Успешная торговля каждый день',
                'description' => 'Каждодневная прибыль обеспечена! Данный курс предназначен для трейдеров, которые 
                уже устали красить свои седые волосы и сидеть на валидоле.',
            ],
            [
                'code' => 'AREND318305889120',
                'name' => 'Покупай/продовай на сигналах. Ленивый трейдинг',
                'description' => 'Устал искать точки входа? Хочешь зарабатывать не прилагая особых усилий? Тогда этот
                курс специально для тебя!',
            ],
            [
                'code' => 'BPSKSODSAJGJSKAOD983A',
                'name' => 'C чего начать новичку?',
                'description' => 'C чего начать новичку? Этим вопросом задается каждый новоприбывший будущий инвестор.
                В данном курсе мы расскажем с чего начать, и как не найти неприятностей себе на голову..',
            ],
            [
                'code' => 'JZLAO2390KSALLFASK123',
                'name' => 'Как выбрать надежного брокера?',
                'description' => 'В данном курсе вы узнаете как выбрать надежного брокера. Как проверить брокера, 
                и каковы риски.',
            ],
            [
                'code' => 'MLSADKLD13213KSDMDNVM35',
                'name' => 'Основы рынка',
                'description' => 'Данный курс предназначен для новичков, которые только знакомятся с фондовым рынком.
                Здесь вы узнаете основы.',
            ],
            [
                'code' => 'QNDIQJWDALSDASDJGLSAD',
                'name' => 'Инвестор',
                'description' => 'Данный курс предназначен для типа людей, которые не хотят ежедневно сидеть за
                своими мониторами, кто хочет получать постепенную прибыль, с минимальными рисками в долгосрочной
                перспективе.',
            ],
            [
                'code' => 'MSALDLGSALDFJASLDDASODP',
                'name' => 'Трейдер',
                'description' => 'Данный курс предназначен для рисковых людей. Если вы хотите играть на понижение,
                не слив весь депозит, то этот курс предназначен для вас!',
            ],
        ];

        $lessonObject = [
            // Портфель роста 2021
            [
                'name' => 'Топ 3 компании для покупки в марте',
                'material' => 'Тут будет материал о 3-ех компаниях для покупки в марте...',
                'number' => random_int(1, 1000),
            ],
            [
                'name' => 'Сектор здравоохранения покоряет новые вершины!',
                'material' => 'Тут будет материал о компаниях здравоохранения...',
                'number' => random_int(1, 1000),
            ],
            // Успешная торговля каждый день
            [
                'name' => '11.05.2021. Покупаем BIDU, и вот почему..',
                'material' => 'Тут будет материал о компании BIDU...',
                'number' => random_int(1, 1000),
            ],
            [
                'name' => '11.05.2021. Покупаем SPCE, и вот почему..',
                'material' => 'Тут будет материал о компании SPCE...',
                'number' => random_int(1, 1000),
            ],
            // Покупай/продовай на сигналах. Ленивый трейдинг
            [
                'name' => 'Сигналы на 04.05.2021',
                'material' => 'Тут будет материал о сигналах на 04.05.2021...',
                'number' => random_int(1, 1000),
            ],
            [
                'name' => 'Сигналы на 05.05.2021',
                'material' => 'Тут будет материал о сигналах на 05.05.2021...',
                'number' => random_int(1, 1000),
            ],
            // C чего начать новичку?
            [
                'name' => 'Новичкам везёт - вранье!',
                'material' => 'Тут будет материал о том, что новичкам ниразу не везёт...',
                'number' => random_int(1, 1000),
            ],
            [
                'name' => 'Купил на хаях, прокатился на ... Что делать?',
                'material' => 'Тут будет материал с разбором самых частых ошибок новичков...',
                'number' => random_int(1, 1000),
            ],
            // Как выбрать надежного брокера?
            [
                'name' => 'Топ лист надежных брокеров',
                'material' => 'Тут будет материал о надежных брокерах...',
                'number' => random_int(1, 1000),
            ],
            [
                'name' => 'Бан лист, самые ненадежные брокеры',
                'material' => 'Тут будет материал о самых ненадежных брокерах...',
                'number' => random_int(1, 1000),
            ],
            // Основы рынка
            [
                'name' => 'Акции',
                'material' => 'Тут будет материал об акциях...',
                'number' => random_int(1, 1000),
            ],
            [
                'name' => 'Облигации',
                'material' => 'Тут будет материал об облигациях...',
                'number' => random_int(1, 1000),
            ],
            [
                'name' => 'Фонды',
                'material' => 'Тут будет материал о фондах...',
                'number' => random_int(1, 1000),
            ],
            // Инвестор
            [
                'name' => 'Ивестор - базовый курс',
                'material' => 'Тут будет материал...',
                'number' => random_int(1, 1000),
            ],
            [
                'name' => 'Ивестор - продвинутый курс',
                'material' => 'Тут будет материал...',
                'number' => random_int(1, 1000),
            ],
            [
                'name' => 'Ивестор - как выбирать прибыльные компании?',
                'material' => 'Тут будет материал...',
                'number' => random_int(1, 1000),
            ],
            // Трейдер
            [
                'name' => 'Трейдер - базовый курс',
                'material' => 'Тут будет материал...',
                'number' => random_int(1, 1000),
            ],
            [
                'name' => 'Трейдер - проженный спекулянт',
                'material' => 'Тут будет материал...',
                'number' => random_int(1, 1000),
            ],
            [
                'name' => 'Трейдер - всё о свечных паттернах',
                'material' => 'Тут будет материал о паттернах...',
                'number' => random_int(1, 1000),
            ],
        ];

        // фикстуры для класса course
        foreach ($coursesObject as $coursesObj) {
            $course = new Course();
            $course->setCode($coursesObj['code']);
            $course->setName($coursesObj['name']);
            $course->setDescription($coursesObj['description']);
            $manager->persist($course);

            // фикстуры для класса lesson
            if ('Портфель роста 2021' === $coursesObj['name']) {
                for ($i = 0; $i < 2; ++$i) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonObject[$i]['name']);
                    $lesson->setCourse($course);
                    $lesson->setMaterial($lessonObject[$i]['material']);
                    $lesson->setNumber($lessonObject[$i]['number']);
                    $manager->persist($lesson);
                }
            } elseif ('Успешная торговля каждый день' === $coursesObj['name']) {
                for ($i = 2; $i < 4; ++$i) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonObject[$i]['name']);
                    $lesson->setCourse($course);
                    $lesson->setMaterial($lessonObject[$i]['material']);
                    $lesson->setNumber($lessonObject[$i]['number']);
                    $manager->persist($lesson);
                }
            } elseif ('Покупай/продовай на сигналах. Ленивый трейдинг' === $coursesObj['name']) {
                for ($i = 4; $i < 6; ++$i) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonObject[$i]['name']);
                    $lesson->setCourse($course);
                    $lesson->setMaterial($lessonObject[$i]['material']);
                    $lesson->setNumber($lessonObject[$i]['number']);
                    $manager->persist($lesson);
                }
            } elseif ('C чего начать новичку?' === $coursesObj['name']) {
                for ($i = 6; $i < 8; ++$i) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonObject[$i]['name']);
                    $lesson->setCourse($course);
                    $lesson->setMaterial($lessonObject[$i]['material']);
                    $lesson->setNumber($lessonObject[$i]['number']);
                    $manager->persist($lesson);
                }
            } elseif ('Как выбрать надежного брокера?' === $coursesObj['name']) {
                for ($i = 8; $i < 10; ++$i) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonObject[$i]['name']);
                    $lesson->setCourse($course);
                    $lesson->setMaterial($lessonObject[$i]['material']);
                    $lesson->setNumber($lessonObject[$i]['number']);
                    $manager->persist($lesson);
                }
            } elseif ('Основы рынка' === $coursesObj['name']) {
                for ($i = 10; $i < 13; ++$i) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonObject[$i]['name']);
                    $lesson->setCourse($course);
                    $lesson->setMaterial($lessonObject[$i]['material']);
                    $lesson->setNumber($lessonObject[$i]['number']);
                    $manager->persist($lesson);
                }
            } elseif ('Инвестор' === $coursesObj['name']) {
                for ($i = 13; $i < 16; ++$i) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonObject[$i]['name']);
                    $lesson->setCourse($course);
                    $lesson->setMaterial($lessonObject[$i]['material']);
                    $lesson->setNumber($lessonObject[$i]['number']);
                    $manager->persist($lesson);
                }
            } elseif ('Трейдер' === $coursesObj['name']) {
                for ($i = 16; $i < 19; ++$i) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonObject[$i]['name']);
                    $lesson->setCourse($course);
                    $lesson->setMaterial($lessonObject[$i]['material']);
                    $lesson->setNumber($lessonObject[$i]['number']);
                    $manager->persist($lesson);
                }
            }
        }
        $manager->flush();
    }
}

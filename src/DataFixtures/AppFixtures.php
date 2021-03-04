<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $coursesObject = [
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
            if ('Основы рынка' == $coursesObj['name']) {
                for ($i = 0; $i < 3; $i++) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonObject[$i]['name']);
                    $lesson->setCourse($course);
                    $lesson->setMaterial($lessonObject[$i]['material']);
                    $lesson->setNumber($lessonObject[$i]['number']);
                    $manager->persist($lesson);
                }
            } elseif ('Инвестор' == $coursesObj['name']) {
                for ($i = 3; $i < 6; $i++) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonObject[$i]['name']);
                    $lesson->setCourse($course);
                    $lesson->setMaterial($lessonObject[$i]['material']);
                    $lesson->setNumber($lessonObject[$i]['number']);
                    $manager->persist($lesson);
                }
            } elseif ('Трейдер' == $coursesObj['name']) {
                for ($i = 6; $i < 9; $i++) {
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

<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class LessonType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Name max length is {{ limit }} symbols',
                    ]),
                    new NotBlank([
                        'message' => 'Name can not be blank',
                    ]),
                ],
                'required' => false,
            ])
            ->add('material', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Material field can not be empty'
                    ]),
                ],
            ])
            ->add('number', NumberType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\d{1,}/',
                        'message' => 'This value number is not valid',
                    ]),
                    new NotBlank([
                        'message' => 'Number field can not be empty',
                    ]),
                ],
                'invalid_message'=> 'This value number is not valid',
                'required' => false,
            ])
            ->add('course', HiddenType::class)
        ;

        $builder->get('course')
            ->addModelTransformer(new CallbackTransformer(
                function (Course $course) {
                    return $course->getId();
                },
                function (int $courseId) {
                    return $this->entityManager->getRepository(Course::class)->find($courseId);
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}

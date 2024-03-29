<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Maximum code length is {{ limit }} symbols',
                    ]),
                    new NotBlank([
                        'message' => 'Code can not be empty',
                    ]),
                ],
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Maximum name length is {{ limit }} symbols',
                    ]),
                    new NotBlank([
                        'message' => 'Name can not be empty',
                    ]),
                ],
                'required' => false,
            ])
            ->add(
                'price',
                NumberType::class,
                [
                    'attr' => [
                        'value' => $options['price'],
                    ],
                    'mapped' => false,
                    'empty_data' => '',
                    'required' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Indicate the price',
                        ]),
                    ],
                ]
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'data' => $options['type'],
                    'mapped' => false,
                    'choices' => [
                        'Аренда' => 'rent',
                        'Бесплатный' => 'free',
                        'Покупка' => 'buy',
                    ],
                ]
            )
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Maximum description length is {{ limit }} symbols',
                    ]),
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
            'price' => 0.0,
            'type' => 'rent',
        ]);
        $resolver->setAllowedTypes('price', 'float');
        $resolver->setAllowedTypes('type', 'string');
    }
}

<?php

namespace App\User\Transport\Form;

use App\User\Model\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('firstName', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'aria-label' => 'First Name...',
                    'placeholder' => ''
                ],
                'label' => false
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('nationality', CountryType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('country', CountryType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('state', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('city',TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('street', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('housnumber', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('birthday', DateType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('position', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('photo', FileType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => false,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
            ])
            ->add('phone', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

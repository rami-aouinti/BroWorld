<?php

namespace App\User\Transport\Form;

use App\User\Domain\Entity\User;
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
use Vich\UploaderBundle\Form\Type\VichImageType;

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
                ],
                'required' => false,
            ])
            ->add('nationality', CountryType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('country', CountryType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('state', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('city',TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('street', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('housnumber', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('birthday', DateType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('position', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('phone', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
            ])
            ->add('imageName')
            ->add('imageSize')
            ->add('brochure', FileType::class, [
                'label' => 'Brochure (PDF file)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Brochure (PDF file)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
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

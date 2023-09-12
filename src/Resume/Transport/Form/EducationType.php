<?php

namespace App\Resume\Transport\Form;

use App\Resume\Model\Entity\Education;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EducationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start', DateType::class)
            ->add('end', DateType::class)
            ->add('school', TextType::class)
            ->add('city', TextType::class)
            ->add('description', TextareaType::class)
            ->add('diploma', TextType::class)
            ->add('specality', TextType::class)
            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Education::class,
        ]);
    }
}

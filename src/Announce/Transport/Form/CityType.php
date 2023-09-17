<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Valery Maslov
 * Date: 15.08.2018
 * Time: 19:55.
 */

namespace App\Announce\Transport\Form;

use App\Announce\Domain\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'attr' => [
                    'autofocus' => true,
                    'class' => 'form-control border'
                ],
                'label' => 'label.name',
            ])
            ->add('slug', null, [
                'label' => 'label.slug',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('title', null, [
                'label' => 'label.title',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('meta_title', null, [
                'label' => 'label.meta_title',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('meta_description', TextareaType::class, [
                'label' => 'label.meta_description',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}

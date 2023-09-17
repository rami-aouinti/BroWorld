<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Valery Maslov
 * Date: 12.08.2018
 * Time: 10:45.
 */

namespace App\Announce\Transport\Form;

use App\Announce\Domain\Entity\DealType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DealTypeType extends AbstractType
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
                    'class' => 'form-control',
                ],
                'label' => 'label.name',
            ])
            ->add('slug', null, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'label.slug',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DealType::class,
        ]);
    }
}
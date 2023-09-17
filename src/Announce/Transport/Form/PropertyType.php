<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Valery Maslov
 * Date: 16.08.2018
 * Time: 10:39.
 */

namespace App\Announce\Transport\Form;

use App\Announce\Domain\Entity\Category;
use App\Announce\Domain\Entity\City;
use App\Announce\Domain\Entity\DealType;
use App\Announce\Domain\Entity\District;
use App\Announce\Domain\Entity\Feature;
use App\Announce\Domain\Entity\Metro;
use App\Announce\Domain\Entity\Neighborhood;
use App\Announce\Domain\Entity\Property;
use App\User\Domain\Entity\User;
use App\Announce\Transport\Form\EventSubscriber\AddAgentFieldSubscriber;
use App\Announce\Transport\Form\EventSubscriber\AddDistrictFieldSubscriber;
use App\Announce\Transport\Form\EventSubscriber\AddMetroFieldSubscriber;
use App\Announce\Transport\Form\EventSubscriber\AddNeighborhoodFieldSubscriber;
use App\Announce\Transport\Form\EventSubscriber\UpdateDistrictFieldSubscriber;
use App\Announce\Transport\Form\EventSubscriber\UpdateMetroFieldSubscriber;
use App\Announce\Transport\Form\EventSubscriber\UpdateNeighborhoodFieldSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PropertyType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'placeholder' => 'placeholder.select_city',
                'label' => 'label.city',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('district', EntityType::class, [
                'class' => District::class,
                'choice_label' => 'name',
                'placeholder' => 'placeholder.select_district',
                'label' => 'label.district',
                'required' => false,
                'choices' => [],
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('neighborhood', EntityType::class, [
                'class' => Neighborhood::class,
                'choice_label' => 'name',
                'placeholder' => 'placeholder.select_neighborhood',
                'label' => 'label.neighborhood',
                'required' => false,
                'choices' => [],
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('metro_station', EntityType::class, [
                'class' => Metro::class,
                'choice_label' => 'name',
                'placeholder' => 'placeholder.select_metro_station',
                'label' => 'label.metro_station_name',
                'required' => false,
                'choices' => [],
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('dealType', EntityType::class, [
                'class' => DealType::class,
                'choice_label' => 'name',
                'label' => 'label.deal_type',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'label.category',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('bathrooms_number', null, [
                'label' => 'label.bathrooms_number',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('bedrooms_number', null, [
                'label' => 'label.bedrooms_number',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('max_guests', null, [
                'label' => 'label.max_guests',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('address', null, [
                'label' => 'label.address',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('latitude', null, [
                'label' => 'label.latitude',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('longitude', null, [
                'label' => 'label.longitude',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('show_map', CheckboxType::class, [
                'label' => 'label.show_map',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('price', null, [
                'label' => 'label.price',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('price_type', null, [
                'label' => 'label.price_type',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('available_now', CheckboxType::class, [
                'label' => 'label.available_now',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('features', EntityType::class, [
                'class' => Feature::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'label' => 'label.features',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('property_description', PropertyDescriptionType::class, [
                'attr' => [
                    'class' => 'form-control border'
                ],
            ]);

        $builder->addEventSubscriber(new AddNeighborhoodFieldSubscriber())
            ->get('city')->addEventSubscriber(new UpdateNeighborhoodFieldSubscriber());

        $builder->addEventSubscriber(new AddDistrictFieldSubscriber())
            ->get('city')->addEventSubscriber(new UpdateDistrictFieldSubscriber());

        $builder->addEventSubscriber(new AddMetroFieldSubscriber())
            ->get('city')->addEventSubscriber(new UpdateMetroFieldSubscriber());

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $this->addFieldsForAdmin($builder);
        }
    }

    private function addFieldsForAdmin(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builder
            ->add('priority_number', null, [
                'label' => 'label.priority_number',
                'required' => false,
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('author', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'label.agent',
                'attr' => [
                    'class' => 'form-control border'
                ],
            ])
            ->add('state', ChoiceType::class, [
                'label' => 'label.moderation_status',
                'choices' => [
                    'option.published' => 'published',
                    'option.private' => 'private',
                    'option.pending' => 'pending',
                    'option.rejected' => 'rejected',
                ],
                'attr' => [
                    'class' => 'form-control border'
                ],
            ]);

        $builder->addEventSubscriber(new AddAgentFieldSubscriber($this->security));

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
        ]);
    }
}

<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Form\Extension;

use App\Crm\Transport\Form\API\ActivityApiEditForm;
use App\Crm\Transport\Form\API\ActivityRateApiForm;
use App\Crm\Transport\Form\API\CustomerApiEditForm;
use App\Crm\Transport\Form\API\CustomerRateApiForm;
use App\Crm\Transport\Form\API\ProjectApiEditForm;
use App\Crm\Transport\Form\API\ProjectRateApiForm;
use App\Crm\Transport\Form\API\TagApiEditForm;
use App\Crm\Transport\Form\API\TeamApiEditForm;
use App\Crm\Transport\Form\API\TimesheetApiEditForm;
use App\Crm\Transport\Form\API\UserApiCreateForm;
use App\Crm\Transport\Form\API\UserApiEditForm;
use App\Crm\Transport\Form\Type\BillableType;
use App\Crm\Transport\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ApiFormExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [
            ActivityApiEditForm::class,
            ActivityRateApiForm::class,
            CustomerApiEditForm::class,
            CustomerRateApiForm::class,
            ProjectApiEditForm::class,
            ProjectRateApiForm::class,
            TagApiEditForm::class,
            TeamApiEditForm::class,
            TimesheetApiEditForm::class,
            UserApiCreateForm::class,
            UserApiEditForm::class,
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($builder->has('metaFields')) {
            $builder->remove('metaFields');
        }

        $replaces = [];

        foreach ($builder as $child) {
            if (\in_array($child->getType()->getInnerType()->getParent(), [CheckboxType::class, BillableType::class, YesNoType::class])) {
                $replaces[] = [
                    'name' => $child->getName(),
                    'label' => $child->getFormConfig()->getOption('label'),
                    'required' => $child->getFormConfig()->getRequired(),
                ];
            }
        }

        foreach ($replaces as $field) {
            $builder->remove($field['name']);
            $builder->add($field['name'], CheckboxType::class, [
                'label' => $field['label'],
                'required' => $field['required'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}

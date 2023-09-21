<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Form;

use App\Crm\Transport\Form\Type\AvatarType;
use App\Crm\Transport\Form\Type\MailType;
use App\Crm\Transport\Form\Type\TimezoneType;
use App\Crm\Transport\Form\Type\UserLanguageType;
use App\Crm\Transport\Form\Type\UserType;
use App\Crm\Transport\Form\Type\YesNoType;
use App\Crm\Application\Configuration\SystemConfiguration;
use App\User\Domain\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form used to edit the profile of a User.
 * @extends AbstractType<User>
 */
class UserEditType extends AbstractType
{
    use ColorTrait;

    public function __construct(private SystemConfiguration $configuration)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User|null $user */
        $user = null;
        if (\array_key_exists('data', $options)) {
            $user = $options['data'];
        }

        $builder->add('alias', TextType::class, [
            'label' => 'alias',
            'required' => false,
        ]);

        $builder->add('title', TextType::class, [
            'label' => 'title',
            'required' => false,
        ]);

        $builder->add('accountNumber', TextType::class, [
            'label' => 'account_number',
            'required' => false,
        ]);

        if ($this->configuration->isThemeAllowAvatarUrls()) {
            $builder->add('avatar', AvatarType::class, [
                'required' => false,
            ]);
        }

        $this->addColor($builder);

        $builder->add('email', MailType::class);

        if ($options['include_preferences']) {
            $builder->add('language', UserLanguageType::class, [
                'required' => true,
            ]);

            $builder->add('timezone', TimezoneType::class, [
                'required' => true,
            ]);
        }

        if ($options['include_active_flag']) {
            $builder->add('enabled', YesNoType::class, [
                'label' => 'active',
                'help' => 'active.help'
            ]);

            $builder->add('systemAccount', YesNoType::class, [
                'label' => 'system_account',
                'help' => 'system_account.help',
            ]);
        }

        if ($options['include_supervisor']) {
            $builder->add('supervisor', UserType::class, [
                'required' => false,
                'label' => 'supervisor',
                'ignore_users' => ($user instanceof User && $user->getId() !== null ? [$user] : []),
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => ['Profile'],
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'edit_user_profile',
            'include_active_flag' => true,
            'include_preferences' => true,
            'include_supervisor' => true,
        ]);
    }
}

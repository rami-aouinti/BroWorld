<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\API;

use App\Crm\Application\Service\Timesheet\DateTimeFactory;
use App\Crm\Application\Utils\Pagination;
use App\User\Domain\Entity\User;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @method null|User getUser()
 */
abstract class BaseApiController extends AbstractController
{
    public const DATE_ONLY_FORMAT = 'yyyy-MM-dd';
    public const DATE_FORMAT = DateTimeType::HTML5_FORMAT;
    public const DATE_FORMAT_PHP = 'Y-m-d\TH:i:s';

    /**
     * @template TFormType of FormTypeInterface<TData>
     * @template TData of mixed
     * @param class-string<TFormType> $type
     * @param TData|null $data
     * @param array<mixed> $options
     * @return FormInterface<TData|null>
     */
    protected function createSearchForm(string $type = FormType::class, mixed $data = null, array $options = []): FormInterface
    {
        return $this->container
            ->get('form.factory')
            ->createNamed('', $type, $data, array_merge(['method' => 'GET'], $options));
    }

    protected function getDateTimeFactory(?User $user = null): DateTimeFactory
    {
        if (null === $user) {
            $user = $this->getUser();
        }

        return DateTimeFactory::createByUser($user);
    }

    protected function addPagination(View $view, Pagination $pagination): void
    {
        $view->setHeader('X-Page', (string) $pagination->getCurrentPage());
        $view->setHeader('X-Total-Count', (string) $pagination->getNbResults());
        $view->setHeader('X-Total-Pages', (string) $pagination->getNbPages());
        $view->setHeader('X-Per-Page', (string) $pagination->getMaxPerPage());
    }
}
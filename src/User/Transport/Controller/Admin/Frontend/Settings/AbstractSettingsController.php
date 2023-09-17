<?php

declare(strict_types=1);

namespace App\User\Transport\Controller\Admin\Frontend\Settings;

use App\Frontend\Domain\Repository\SettingsRepository;
use App\User\Application\Service\Admin\SettingsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractSettingsController extends AbstractController
{
    protected array $settings;

    public function __construct(
        protected SettingsRepository $repository,
        protected SettingsService $service
    ) {
        $this->settings = $this->repository->findAllAsArray();
    }
}

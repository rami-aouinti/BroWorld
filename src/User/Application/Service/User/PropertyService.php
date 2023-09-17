<?php

declare(strict_types=1);

namespace App\User\Application\Service\User;

use App\Announce\Domain\Entity\Property;
use App\Announce\Domain\Repository\UserPropertyRepository;
use App\Announce\Application\Transformer\PropertyTransformer;
use App\Announce\Application\Transformer\RequestToArrayTransformer;
use App\User\Application\Service\Admin\PropertyService as Service;
use App\Announce\Application\Utils\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class PropertyService extends Service
{
    public function __construct(
        CsrfTokenManagerInterface $tokenManager,
        RequestStack $requestStack,
        EntityManagerInterface $em,
        MessageBusInterface $messageBus,
        Slugger $slugger,
        private readonly PropertyTransformer $propertyTransformer,
        private readonly UserPropertyRepository $repository,
        private readonly RequestToArrayTransformer $transformer,
        private readonly TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($tokenManager, $requestStack, $em, $messageBus, $slugger);
    }

    public function getUserProperties(Request $request): PaginationInterface
    {
        $searchParams = $this->transformer->transform($request);
        $searchParams['user'] = $this->tokenStorage->getToken()->getUser()->getId();

        return $this->repository->findByUser($searchParams);
    }

    public function contentToPlainText(Property $property, bool $isHtmlAllowed): Property
    {
        if (!$isHtmlAllowed) {
            $property = $this->propertyTransformer->contentToPlainText($property);
        }

        return $property;
    }

    public function contentToHtml(Property $property, bool $isHtml): Property
    {
        if (!$isHtml) {
            $property = $this->propertyTransformer->contentToHtml($property);
        }

        return $property;
    }

    public function sanitizeHtml(Property $property, bool $isHtmlAllowed): Property
    {
        if (!$isHtmlAllowed) {
            $property = $this->propertyTransformer->contentToPlainText($property);
            $property = $this->propertyTransformer->contentToHtml($property);
        }

        return $property;
    }
}

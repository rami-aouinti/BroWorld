<?php

declare(strict_types=1);

namespace App\User\Application\Service\Admin;

use App\Announce\Domain\Entity\Property;
use App\User\Application\Service\AbstractService;
use App\User\Transport\Message\DeletePhotos;
use App\Announce\Application\Utils\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class PropertyService extends AbstractService
{
    public function __construct(
        CsrfTokenManagerInterface $tokenManager,
        RequestStack $requestStack,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $messageBus,
        private readonly Slugger $slugger
    ) {
        parent::__construct($tokenManager, $requestStack);
    }

    public function create(Property $property): void
    {
        // Make slug
        $slug = $this->slugger->slugify($property->getPropertyDescription()->getTitle() ?? 'property');

        $property->setSlug($slug);
        $property->setCreatedAt(new \DateTime('now'));
        $property->setUpdatedAt(new \DateTime('now'));
        $property->setState('published');
        $property->setPriorityNumber((int) $property->getPriorityNumber());
        $this->save($property);
        $this->clearCache('properties_count');
        $this->addFlash('success', 'message.created');
    }

    public function update(Property $property): void
    {
        $slug = $this->slugger->slugify($property->getPropertyDescription()->getTitle() ?? 'property');
        $property->setSlug($slug);
        $property->setUpdatedAt(new \DateTime('now'));
        $property->setPriorityNumber((int) $property->getPriorityNumber());
        $this->em->flush();
        $this->addFlash('success', 'message.updated');
    }

    public function save(Property $property): void
    {
        $this->em->persist($property);
        $this->em->flush();
    }

    public function remove(Property $property): void
    {
        $this->em->remove($property);
        $this->em->flush();
    }

    public function delete(Property $property): void
    {
        $this->messageBus->dispatch(new DeletePhotos($property));
        $this->remove($property);
        $this->clearCache('properties_count');
        $this->addFlash('success', 'message.deleted');
    }
}

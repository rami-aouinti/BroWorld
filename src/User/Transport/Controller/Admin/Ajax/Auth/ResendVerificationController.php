<?php

declare(strict_types=1);

namespace App\User\Transport\Controller\Admin\Ajax\Auth;

use App\User\Transport\Controller\Admin\Ajax\AjaxController;
use App\User\Domain\Entity\User;
use App\User\Application\Service\Cache\UserDataCache;
use App\User\Transport\Message\SendEmailConfirmationLink;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ResendVerificationController extends AbstractController implements AjaxController
{
    use UserDataCache;

    #[Route('/auth/should_link_be_visible', name: 'check_confirmation', methods: ['GET'])]
    public function shouldLinkBeVisible(): JsonResponse
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        return new JsonResponse(['display' => $this->isSendingAllowed($user)]);
    }

    #[Route('/auth/resend', name: 'resend_confirmation', methods: ['POST'])]
    public function resendEmail(MessageBusInterface $messageBus, TranslatorInterface $translator): JsonResponse
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        if ($this->isSendingAllowed($user)) {
            $messageBus->dispatch(new SendEmailConfirmationLink($user));
        } else {
            return new JsonResponse(['message' => 'There is no need to resend this email'], 422);
        }

        $translated = $translator->trans('confirmation.email.success');

        return new JsonResponse(['message' => $translated.' '.$user->getEmail()]);
    }

    private function isSendingAllowed(User $user): bool
    {
        if (null !== $user->getEmailVerifiedAt()) {
            return false;
        }

        $sentAt = $this->getConfirmationSentAt($user);

        if ($sentAt instanceof \DateTimeInterface) {
            return ((int) date_diff(new \DateTime('now'), $sentAt)
                ->format('%i')) > 60;
        }

        return true;
    }
}

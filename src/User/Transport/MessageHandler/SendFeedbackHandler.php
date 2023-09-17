<?php

declare(strict_types=1);

namespace App\User\Transport\MessageHandler;

use App\Frontend\Application\Dto\FeedbackDto;
use App\User\Transport\Mailer\Mailer;
use App\User\Transport\Message\SendFeedback;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final class SendFeedbackHandler
{
    public function __construct(private readonly Mailer $mailer, private readonly TranslatorInterface $translator)
    {
    }

    public function __invoke(SendFeedback $sendFeedback): void
    {
        /** @var FeedbackDto $feedback */
        $feedback = $sendFeedback->getFeedback();

        $subject = $this->translator->trans('email.new_message');

        $email = (new Email())
            ->from(new Address($feedback->getFromEmail(), $feedback->getFromName()))
            ->to($feedback->getToEmail())
            ->replyTo($feedback->getFromEmail())
            ->subject($subject)
            ->text($feedback->getMessage())
            ->html($feedback->getMessage());

        $this->mailer->send($email);
    }
}

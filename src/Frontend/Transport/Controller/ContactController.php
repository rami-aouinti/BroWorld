<?php

namespace App\Frontend\Transport\Controller;

use App\Frontend\Model\Entity\Contact;
use App\Frontend\Transport\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contact);
            $entityManager->flush();
            $email = (new Email())
                ->from(new Address(
                    $contact->getEmail(),
                    $contact->getFirstName() .  " " . $contact->getLastName()
                ))
                ->to('info@ramyworld.de')
                ->subject('Contact from BroWorld')
                ->text($contact->getMessage())
                ->html($contact->getMessage());

            $mailer->send($email);
            $form = $this->createForm(ContactType::class, $contact);
            $this->addFlash('success', 'Your message was successfully sent');
            return $this->redirectToRoute('app_contact', ['form' => $form], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }

}

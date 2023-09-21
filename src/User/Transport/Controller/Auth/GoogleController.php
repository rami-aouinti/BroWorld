<?php

declare(strict_types=1);

# Controller/GoogleController

namespace App\User\Transport\Controller\Auth;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    /**
     * @param ClientRegistry $clientRegistry
     * @return RedirectResponse
     */
    #[Route(path: '/connect/google', name: 'connect_google')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        //Redirect to google
        return $clientRegistry->getClient('google')->redirect([], []);
    }

    /**
     * After going to Google, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     */
    #[Route(path: '/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(Request $request)
    {
        return $this->redirectToRoute('app_login');
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
    }
}

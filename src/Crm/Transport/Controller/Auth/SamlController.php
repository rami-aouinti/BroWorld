<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Controller\Auth;

use App\Crm\Application\Configuration\SamlConfigurationInterface;
use App\Crm\Application\Saml\SamlAuthFactory;
use OneLogin\Saml2\Error;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/saml')]
final class SamlController extends AbstractController
{
    public function __construct(private SamlAuthFactory $authFactory, private SamlConfigurationInterface $samlConfiguration)
    {
    }

    /**
     * @param Request $request
     * @return void
     * @throws Error
     */
    #[Route(path: '/login', name: 'saml_login')]
    public function loginAction(Request $request)
    {
        if (!$this->samlConfiguration->isActivated()) {
            throw $this->createNotFoundException('SAML deactivated');
        }

        $session = $request->getSession();
        $authErrorKey = Security::AUTHENTICATION_ERROR;

        $error = null;

        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif ($session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        }

        if ($error) {
            if (\is_object($error) && method_exists($error, 'getMessage')) {
                $error = $error->getMessage();
            }
            throw new \RuntimeException($error);
        }

        $this->authFactory->create()->login($session->get('_security.main.target_path'));
    }

    /**
     * @return Response
     * @throws Error
     */
    #[Route(path: '/metadata', name: 'saml_metadata')]
    public function metadataAction()
    {
        if (!$this->samlConfiguration->isActivated()) {
            throw $this->createNotFoundException('SAML deactivated');
        }

        $metadata = $this->authFactory->create()->getSettings()->getSPMetadata();

        $response = new Response($metadata);
        $response->headers->set('Content-Type', 'xml');

        return $response;
    }

    /**
     * @return mixed
     */
    #[Route(path: '/acs', name: 'saml_acs')]
    public function assertionConsumerServiceAction()
    {
        if (!$this->samlConfiguration->isActivated()) {
            throw $this->createNotFoundException('SAML deactivated');
        }

        throw new \RuntimeException('You must configure the check path in your firewall.');
    }

    /**
     * @return mixed
     */
    #[Route(path: '/logout', name: 'saml_logout')]
    public function logoutAction()
    {
        if (!$this->samlConfiguration->isActivated()) {
            throw $this->createNotFoundException('SAML deactivated');
        }

        throw new \RuntimeException('You must configure the logout path in your firewall.');
    }
}

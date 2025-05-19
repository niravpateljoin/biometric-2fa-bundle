<?php

declare(strict_types=1);

namespace Biometric2FA\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('PUBLIC_ACCESS')]
class JavascriptController extends AbstractController
{

    #[Route('/verify-biometrics-js', name: 'verify_biometrics_js', methods: ['GET'])]
    public function verifyBiometricJS(): Response
    {
        $response = $this->render('@Biometric2FABundle/security/biometric_auth.js.twig');

        $response->headers->set('Content-Type', 'text/javascript');

        $response->setCache([
            'no_cache' => true,
            'last_modified' => new \DateTime(),
        ]);

        return $response;
    }

    #[Route('/common-biometric-js', name: 'common_biometrics_js', methods: ['GET'])]
    public function commonBiometricJS(): Response
    {
        $response = $this->render('@Biometric2FABundle/common_biometric.js.twig');

        $response->headers->set('Content-Type', 'text/javascript');

        $response->setCache([
            'no_cache' => true,
            'last_modified' => new \DateTime(),
        ]);

        return $response;
    }

    #[Route('/register-biometric-js', name: 'register_biometrics_js', methods: ['GET'])]
    public function registerBiometricJS(): Response
    {
        $response = $this->render('@Biometric2FABundle/setting/register_biometric.js.twig');

        $response->headers->set('Content-Type', 'text/javascript');

        $response->setCache([
            'no_cache' => true,
            'last_modified' => new \DateTime(),
        ]);

        return $response;
    }
}

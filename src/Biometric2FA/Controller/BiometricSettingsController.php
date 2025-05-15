<?php

namespace Biometric2FA\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class BiometricSettingsController extends AbstractController
{
    #[Route('/settings/manage-bio-metrics', name: 'settings_manage_bio_metrics')]
    public function manageBioMetrics(Request $request): JsonResponse
    {
        $csrfToken = $request->request->getString('_token');
        $status = false;
        $errorMessage = null;
        $enabled = $request->request->getBoolean('bio_metrics');

        if (!$this->isCsrfTokenValid('bio_metrics_auth', $csrfToken)) {
            $errorMessage = 'Invalid CSRF token';
        } else {
            try {
                /** @var User $user */
                $user = $this->getUser();
                $user->setBiometric2FAEnabled($enabled);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $status = true;
            } catch (\Throwable $e) {
                $errorMessage = 'Failed to manage two factor auth, ' . $e->getMessage();
            }
        }

        return $this->json([
            'status' => $status,
            'errorMessage' => $errorMessage
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Biometric2FA\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class BiometricSettingsController extends AbstractController
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly EntityManagerInterface $entityManager) {
    }

    #[Route('/settings', name: 'bio_metrics_settings', methods: ['GET'])]
    public function settings(): Response
    {
        return $this->render('@Biometric2FA/setting/settings.html.twig', [
            'logout_path' => $this->parameterBag->get('biometric_2fa.logout_path'),
        ]);
    }

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
                $this->addFlash('success', 'Biometric authentication settings updated successfully.');
            } catch (\Throwable $e) {
                $errorMessage = 'Failed to manage two factor auth, ' . $e->getMessage();
                $this->addFlash('danger', $errorMessage);
            }
        }

        return $this->json([
            'status' => $status,
            'errorMessage' => $errorMessage
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Biometric2FA\Controller;

use Biometric2FA\Entity\UserDevice;
use Biometric2FA\Helper\UserDeviceHelper;
use Biometric2FA\Repository\UserDeviceRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class BiometricController extends AbstractController
{
    public function __construct(
        private readonly UserDeviceHelper $helper,
        private readonly UserDeviceRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $parameterBag,
    ) {}

    #[Route('/create-args', name: 'bio_metrics_create_args', methods: ['POST'])]
    public function createArgs(): JsonResponse
    {
        $user = $this->getUser();

        try {
            $args = $this->helper->createArgsAndStoreChallengeIntoSession(
                (string) $user->getUserIdentifier(),
                $user->getUserIdentifier(),
                method_exists($user, 'getName') ? $user->getName() : 'User',
                30
            );
            return $this->json(['success' => true, 'createdArgs' => $args]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Route('/process-create', name: 'bio_metrics_process_create', methods: ['POST'])]
    public function processCreate(Request $request): JsonResponse
    {
        $success = false;
        $errorMessage = null;

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        try {
            $bodyData = json_decode($request->getContent(), true);

            if (!isset($bodyData['clientDataJSON'], $bodyData['attestationObject'])) {
                throw new InvalidArgumentException("Missing WebAuthn credential data.");
            }

            $clientDataJSON = base64_decode($bodyData['clientDataJSON']);
            $attestationObject = base64_decode($bodyData['attestationObject']);

            $this->helper->processCreateRequest($clientDataJSON, $attestationObject, $currentUser);
            $success = true;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
        }

        return $this->json([
            'success' => $success,
            'error' => $errorMessage,
        ]);
    }

    #[Route('/get-args', name: 'bio_metrics_get_args', methods: ['POST'])]
    public function getArgs(): JsonResponse
    {
        $user = $this->getUser();

        try {
            $args = $this->helper->getArgsForUser($user);
            return $this->json(['success' => true, 'getArgs' => $args]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Route('/verify', name: 'bio_metrics_verify', methods: ['POST'])]
    public function verify(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!isset($data['id'], $data['clientDataJSON'], $data['authenticatorData'], $data['signature'])) {
            return $this->json(['success' => false, 'error' => 'Invalid response data'], 400);
        }

        $credentialId = bin2hex(base64_decode($data['id']));

        $device = $this->repository->findOneBy([
            'user' => $user,
            'credentialId' => $credentialId,
        ]);

        if (!$device instanceof UserDevice) {
            return $this->json(['success' => false, 'error' => 'Device not found'], 404);
        }

        $stored = unserialize($device->getData());
        $publicKey = $stored->credentialPublicKey ?? null;

        if (!$publicKey) {
            return $this->json(['success' => false, 'error' => 'Missing credential key'], 400);
        }

        try {
            $this->helper->processGetRequest(
                base64_decode($data['clientDataJSON']),
                base64_decode($data['authenticatorData']),
                base64_decode($data['signature']),
                $publicKey,
                $device
            );

            $request->getSession()->set('biometric_verification', true);

            return $this->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            return $this->json(['status' => 'error', 'error' => $e->getMessage()], 500);
        }
    }

    #[Route('/auth', name: 'app_biometric_auth')]
    public function biometricsAuth(): Response
    {
        return $this->render('@Biometric2FA/security/biometrics_auth.html.twig',  [
            'logout_path' => $this->parameterBag->get('biometric_2fa.logout_path'),
        ]);
    }
}

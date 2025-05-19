<?php

declare(strict_types=1);

namespace Biometric2FA\Helper;

use Biometric2FA\Entity\UserDevice;
use Biometric2FA\Repository\UserDeviceRepositoryInterface;
use Biometric2FA\Security\BiometricUserInterface;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use lbuchs\WebAuthn\WebAuthn;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Throwable;

readonly class UserDeviceHelper
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private UserDeviceRepositoryInterface $userDeviceRepository,
        private string $rpName,
        private string $rpId,
        private string $deviceEntityClass,
    ) {}

    public function createArgsAndStoreChallengeIntoSession(
        string $userId,
        string $userIdentifier,
        string $userDisplayName,
        int $timeout,
    ): stdClass {
        $webauthn = $this->getWebAuthn();
        $args = $webauthn->getCreateArgs($userId, $userIdentifier, $userDisplayName, $timeout);
        $this->getSession()->set('webauthn_challenge', $webauthn->getChallenge());
        return $args;
    }

    public function processCreateRequest(string $clientDataJSON, string $attestationObject, BiometricUserInterface $user): void
    {
        try {
            $webAuthn = $this->getWebAuthn();
            $challenge = $this->getSession()->get('webauthn_challenge');

            if (!$challenge) {
                throw new InvalidArgumentException("Challenge not found in session.");
            }

            $data = $webAuthn->processCreate($clientDataJSON, $attestationObject, $challenge);

            $deviceClass = $this->deviceEntityClass;
            $device = new $deviceClass();

            if (!$device instanceof UserDevice) {
                throw new RuntimeException("Configured device entity must extend Biometric2FA\Entity\UserDevice.");
            }

            $device->setUser($user);
            $device->setCreatedAt(new \DateTime());
            $device->setCredentialId(bin2hex($data->credentialId));
            $device->setData(serialize($data));

            $this->entityManager->persist($device);
            $this->entityManager->flush();
        } catch (Throwable $e) {
            throw new RuntimeException('Biometric device registration failed: ' . $e->getMessage());
        }
    }

    public function getArgsForUser(BiometricUserInterface $user): stdClass
    {
        $credentials = $this->userDeviceRepository->getCredentialsForUser($user);
        if (empty($credentials)) {
            throw new RuntimeException("No registered devices.");
        }

        $ids = array_map('hex2bin', $credentials);
        $webauthn = $this->getWebAuthn();
        $args = $webauthn->getGetArgs($ids);
        $this->getSession()->set('webauthn_challenge', $webauthn->getChallenge());
        return $args;
    }

    public function processGetRequest(
        string $clientDataJSON,
        string $authenticatorData,
        string $signature,
        string $credentialPublicKey,
        UserDevice $device,
    ): void {
        $challenge = $this->getSession()->get('webauthn_challenge');

        if (!$challenge) {
            throw new InvalidArgumentException("Challenge not found in session.");
        }

        $webAuthn = $this->getWebAuthn();
        $webAuthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge);

        $device->setLastUsedAt(new \DateTime());
        $this->entityManager->flush();
    }

    private function getWebAuthn(): WebAuthn
    {
        return new WebAuthn($this->rpName, $this->rpId);
    }

    private function getSession(): SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            throw new RuntimeException("No request available.");
        }

        return $request->getSession();
    }
}

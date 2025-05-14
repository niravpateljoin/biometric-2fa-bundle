<?php

namespace Biometric2FA\Repository;

use Biometric2FA\Security\BiometricUserInterface;

interface UserDeviceRepositoryInterface
{
    /**
     * @return string[] list of credential IDs (hex)
     */
    public function getCredentialsForUser(BiometricUserInterface $user): array;
}

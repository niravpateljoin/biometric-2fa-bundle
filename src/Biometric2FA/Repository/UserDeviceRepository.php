<?php

namespace Biometric2FA\Repository;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserDeviceRepositoryInterface
{
    /**
     * @return string[] list of credential IDs (hex)
     */
    public function getCredentialsForUser(UserInterface $user): array;
}

<?php

namespace Biometric2FA\Repository;

use Biometric2FA\Security\BiometricUserInterface;
use Doctrine\ORM\EntityRepository;

abstract class UserDeviceRepository extends EntityRepository
{
    /**
     * Return an array of credential IDs (e.g., ['abc123', 'xyz789']) for the given user
     */
    abstract public function getCredentialsForUser(BiometricUserInterface $user): array;
}

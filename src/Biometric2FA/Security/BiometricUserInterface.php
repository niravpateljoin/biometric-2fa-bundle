<?php

declare(strict_types=1);

namespace Biometric2FA\Security;

interface BiometricUserInterface
{
    /**
     * Checks if biometric 2FA is enabled for the user.
     */
    public function isBiometric2FAEnabled(): bool;

    /**
     * Sets whether biometric 2FA is enabled.
     */
    public function setBiometric2FAEnabled(bool $enabled): self;
}

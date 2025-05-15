<?php

declare(strict_types=1);

namespace Biometric2FA\Security;

use Doctrine\ORM\Mapping as ORM;

trait BiometricUserTrait
{
    #[ORM\Column(type: 'boolean', name: 'biometric_2fa_enabled', options: ['default' => false])]
    private bool $biometric2FAEnabled = false;

    public function isBiometric2FAEnabled(): bool
    {
        return $this->biometric2FAEnabled;
    }

    public function setBiometric2FAEnabled(bool $enabled): static
    {
        $this->biometric2FAEnabled = $enabled;
        return $this;
    }
}

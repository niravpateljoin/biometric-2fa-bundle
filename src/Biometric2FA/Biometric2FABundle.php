<?php

namespace Biometric2FA;

use Biometric2FA\DependencyInjection\Biometric2FAExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Biometric2FABundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new Biometric2FAExtension();
    }
}

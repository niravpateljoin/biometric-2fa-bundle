<?php

declare(strict_types=1);

namespace Biometric2FA;

use Biometric2FA\DependencyInjection\Biometric2FAExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Biometric2FA\DependencyInjection\Compiler\DoctrineTypeCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Biometric2FABundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new Biometric2FAExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrineTypeCompilerPass());
    }
}

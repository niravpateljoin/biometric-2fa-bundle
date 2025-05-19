<?php

declare(strict_types=1);

namespace Biometric2FA\DependencyInjection\Compiler;

use Biometric2FA\Doctrine\DBAL\Types\BlobStringType;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineTypeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (Type::hasType('blob_string')) {
            return;
        }

        Type::addType('blob_string',BlobStringType::class);
    }
}

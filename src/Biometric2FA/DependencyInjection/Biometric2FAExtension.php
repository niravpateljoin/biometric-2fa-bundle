<?php

declare(strict_types=1);

namespace Biometric2FA\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Biometric2FAExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('biometric_2fa.rp_id', $config['rp_id']);
        $container->setParameter('biometric_2fa.rp_name', $config['rp_name']);
        $container->setParameter('biometric_2fa.device_entity', $config['device_entity']);
        $container->setParameter('biometric_2fa.redirect_path', $config['redirect_path']);
        $container->setParameter('biometric_2fa.logout_path', $config['logout_path']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        if (file_exists(__DIR__ . '/../Resources/config/services.yaml')) {
            $loader->load('services.yaml');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('twig', [
            'paths' => [
                __DIR__ . '/../Resources/views' => 'Biometric2FABundle',
            ],
        ]);
    }

    public function getAlias(): string
    {
        return 'biometric_2fa';
    }
}

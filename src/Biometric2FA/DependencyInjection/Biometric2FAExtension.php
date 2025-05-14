<?php

namespace Biometric2FA\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Biometric2FAExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Set parameters from config
        $container->setParameter('biometric_2fa.rp_id', $config['rp_id']);
        $container->setParameter('biometric_2fa.rp_name', $config['rp_name']);
        $container->setParameter('biometric_2fa.attestation_formats', $config['attestation_formats']);
        $container->setParameter('biometric_2fa.device_entity', $config['device_entity']);

        // Load services.yaml
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        if (file_exists(__DIR__ . '/../Resources/config/services.yaml')) {
            $loader->load('services.yaml');
        }
    }

    public function getAlias(): string
    {
        return 'biometric_2fa';
    }
}

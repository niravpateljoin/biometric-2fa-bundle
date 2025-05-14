<?php

namespace Biometric2FA\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('biometric_2fa');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('rp_id')->defaultValue('localhost')->end()
            ->scalarNode('rp_name')->defaultValue('My Application')->end()
            ->arrayNode('attestation_formats')
            ->scalarPrototype()->end()
            ->defaultValue(['packed', 'fido-u2f'])
            ->end()
            ->scalarNode('device_entity')->isRequired()->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
}

<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\MetadataExtensionsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your config files.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('klipper_metadata_extensions');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->append($this->getGuessersNode())
        ;

        return $treeBuilder;
    }

    /**
     * Get guessers node.
     */
    private function getGuessersNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('guessers');
        /** @var ArrayNodeDefinition $node */
        $node = $treeBuilder->getRootNode();
        $node
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('form')
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('mapping_input_types')
            ->useAttributeAsKey('mapping_input_type', false)
            ->normalizeKeys(false)
            ->scalarPrototype()->end()
            ->end()
            ->arrayNode('form_options')
            ->addDefaultsIfNotSet()
            ->normalizeKeys(false)
            ->ignoreExtraKeys(false)
            ->end()
            ->end()
            ->end()
            ->arrayNode('doctrine')
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('mapping_field_types')
            ->useAttributeAsKey('mapping_field_type', false)
            ->normalizeKeys(false)
            ->scalarPrototype()->end()
            ->end()
            ->arrayNode('mapping_association_types')
            ->useAttributeAsKey('mapping_association_type', false)
            ->normalizeKeys(false)
            ->scalarPrototype()->end()
            ->end()
            ->end()
            ->end()
            ->end()
        ;

        return $node;
    }
}

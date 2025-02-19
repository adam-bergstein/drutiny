<?php

namespace Drutiny\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

trait ProfileConfigurationTrait
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('profile');
        $treeBuilder->getRootNode()
          ->children()
            ->scalarNode('title')
              ->info('The human readable name of the profile.')
              ->isRequired()
              ->cannotBeEmpty()
              ->end()
            ->scalarNode('name')
              ->info('The machine-name of the profile.')
              ->isRequired()
              ->cannotBeEmpty()
              ->end()
            ->scalarNode('description')
              ->info('A description why the profile is valuable to use.')
              ->isRequired()
              ->defaultValue('')
              ->end()
            ->scalarNode('language')
              ->defaultValue('en')
              ->info('Language code')
              ->end()
            ->booleanNode('hidden')
              ->defaultFalse()
              ->end()

            // Configuration
            ->scalarNode('uuid')
              ->info('Unique identifier such as a URL.')
              ->isRequired()
              ->cannotBeEmpty()
              ->end()
            ->arrayNode('policies')
              ->info('A list of policies that this profile runs.')
              ->useAttributeAsKey('name')
                ->arrayPrototype()
                  ->children()
                    ->scalarNode('severity')
                      ->info('The severity override to use for the policy in this profile.')
                      ->defaultValue('normal')
                      ->end()
                    ->arrayNode('parameters')
                      ->info('The parameter overrides to use for the policy in this profile.')
                      ->variablePrototype()->end()
                      ->end()
                    ->integerNode('weight')
                      ->info('Weighting to influence policy ordering in the profile.')
                      ->defaultValue(0)
                      ->end()
                  ->end()
                ->end()
              ->end()
            ->arrayNode('include')
              ->scalarPrototype()->end()
            ->end()
            ->arrayNode('excluded_policies')
              ->scalarPrototype()->end()
            ->end()
            ->arrayNode('format')
              ->useAttributeAsKey('name')
              ->info('Configuration for a given format.')
                ->arrayPrototype()
                  ->children()
                    ->scalarNode('template')
                      ->info('The name of the twig template file to use for the html report.')
                      ->end()
                    ->variableNode('content')
                      ->info('The content structure to use for the html format.')
                      ->end()
                    ->end()
                  ->end()
                ->end()
              ->end();

        return $treeBuilder;
    }
}

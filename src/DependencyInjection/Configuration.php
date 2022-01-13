<?php

namespace Localfr\SalesforceClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('localfr_salesforce');
        $rootNode = method_exists($treeBuilder, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('localfr_salesforce');

        $rootNode
            ->children()
                ->scalarNode('client_id')->isRequired()->end()
                ->scalarNode('client_secret')->isRequired()->end()
                ->scalarNode('username')->isRequired()->end()
                ->scalarNode('private_key')->isRequired()->end()
                ->scalarNode('public_key')->isRequired()->end()
                ->booleanNode('sandbox')->defaultFalse()->end()
                ->scalarNode('api_version')->defaultValue('v52.0')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

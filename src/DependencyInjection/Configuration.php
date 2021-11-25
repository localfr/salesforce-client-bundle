<?php

namespace Localfr\SalesforceClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('localfr_salesforce');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('client_id')->isRequired()->end()
                ->scalarNode('client_secret')->isRequired()->end()
                ->scalarNode('username')->isRequired()->end()
                ->scalarNode('private_key')->isRequired()->end()
                ->scalarNode('public_key')->isRequired()->end()
                ->booleanNode('sandbox')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

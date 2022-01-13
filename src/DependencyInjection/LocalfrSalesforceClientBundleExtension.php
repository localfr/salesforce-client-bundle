<?php

namespace Localfr\SalesforceClientBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LocalfrSalesforceClientBundleExtension extends Extension
{
    public const EXTENSION_ALIAS = 'localfr_salesforce';

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return self::EXTENSION_ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('localfr_salesforce.client_id', $config['client_id']);
        $container->setParameter('localfr_salesforce.client_secret', $config['client_secret']);
        $container->setParameter('localfr_salesforce.username', $config['username']);
        $container->setParameter('localfr_salesforce.private_key', $config['private_key']);
        $container->setParameter('localfr_salesforce.public_key', $config['public_key']);
        $container->setParameter('localfr_salesforce.sandbox', $config['sandbox']);
        $container->setParameter('localfr_salesforce.api_version', $config['api_version']);
    }
}

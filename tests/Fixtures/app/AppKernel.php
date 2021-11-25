<?php

namespace Localfr\SalesforceClientBundle\Tests\Fixtures\app;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Localfr\SalesforceClientBundle\LocalfrSalesforceClientBundle;

class AppKernel extends Kernel
{
    public function __construct($env, $debug)
    {
        parent::__construct($env, $debug);

        (new Filesystem())->remove($this->getCacheDir());
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new LocalfrSalesforceClientBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config.yaml');
        $loader->load($this->getRootDir() . '/config/localfr_salesforce.yaml');
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return '/tmp/symfony-cache';
    }

    public function getLogDir()
    {
        return '/tmp/symfony-cache';
    } 
}
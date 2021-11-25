<?php

namespace Localfr\SalesforceClientBundle\Tests;

use Localfr\SalesforceClientBundle\DependencyInjection\LocalfrSalesforceClientBundleExtension;
use Localfr\SalesforceClientBundle\LocalfrSalesforceClientBundle;

class LocalfrSalesforceClientBundleTest extends TestCase
{
    public function testShouldReturnNewContainerExtension()
    {
        $testBundle = new LocalfrSalesforceClientBundle();

        $result = $testBundle->getContainerExtension();
        $this->assertInstanceOf(LocalfrSalesforceClientBundleExtension::class, $result);
    }
}
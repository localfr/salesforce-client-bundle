<?php

namespace Localfr\SalesforceClientBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Localfr\SalesforceClientBundle\DependencyInjection\LocalfrSalesforceClientBundleExtension;

class LocalfrSalesforceClientBundle extends Bundle
{
    /**
     * Overridden to allow for the custom extension alias.
     *
     * @return LocalfrSalesforceClientBundleExtension
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new LocalfrSalesforceClientBundleExtension();
        }

        return $this->extension;
    }
}

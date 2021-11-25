<?php

namespace Localfr\SalesforceClientBundle\Service\Salesforce\Token;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class TokenBuilder
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param string $privateKey
     * @param string $publicKey
     */
    public function __construct(string $privateKey, string $publicKey)
    {
        $this->configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKey),
            InMemory::plainText($publicKey)
        );
    }

    /**
     * @param string $iss
     * @param string $sub
     * @param string $aud
     * 
     * @return Lcobucci\JWT\Token\Plain
     */
    public function build(
        string $iss,
        string $sub,
        string $aud
    ): \Lcobucci\JWT\Token\Plain {
        $now = new \DateTimeImmutable();
        return $this->configuration->builder()
            // Configures the issuer (iss claim)
            ->issuedBy($iss)
            // Configures the subject (sub claim)
            ->relatedTo($sub)
            // Configures the audience (aud claim)
            ->permittedFor($aud)
            // Configures the issue date (iat claim)
            ->issuedAt($now)
            // Configures the expiry date (exp claim)
            ->expiresAt($now->modify("+300 seconds"))
            ->getToken(
                $this->configuration->signer(),
                $this->configuration->signingKey()
            );
    }
}
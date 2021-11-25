<?php

namespace Localfr\SalesforceClientBundle\Service\Salesforce\AuthProvider;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CachedSalesforceProvider extends SalesforceProvider
{
    /**
     * @var string
     */
    const KEY_PREFIX = 'salesforce_token_';

    /**
     * @var FilesystemAdapter
     */
    private $adapter;

    /**
     * @param HttpClientInterface $httpClient
     * @param string $clientId
     * @param string $clientSecret
     * @param string $username
     * @param string $privateKey
     * @param string $publicKey
     * @param bool $sandbox
     */
    public function __construct(
        HttpClientInterface $httpClient,
        string $clientId,
        string $clientSecret,
        string $username,
        string $privateKey,
        string $publicKey,
        bool $sandbox = false
    ) {
        parent::__construct($httpClient, $clientId, $clientSecret, $username, $privateKey, $publicKey, $sandbox);
        $this->adapter = new FilesystemAdapter();
    }

    /**
     * @inheritdoc
     */
    public function authorize(bool $reauth = false): void
    {
        $this->accessToken = $this->adapter->get(
            $this->getCachedTokenKey(),
            function (ItemInterface $item) {
                parent::authorize();
                $item->expiresAfter($this->accessToken->getExpires() - time() - 300);
                return $this->accessToken;
            }
        );
    }

    /**
     * @return string
     */
    private function getCachedTokenKey(): string
    {
        return sprintf('%s%s', self::KEY_PREFIX, $this->clientId);
    }
}
<?php

namespace Localfr\SalesforceClientBundle\Service\Salesforce\Token;

use InvalidArgumentException;
use RuntimeException;

class AccessToken
{
    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var int
     */
    protected $expires;

    /**
     * @var string
     */
    protected $tokenType;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $scopes = [];

    /**
     * @var string
     */
    protected $instanceUrl;

    /**
     * @var int
     */
    private static $timeNow;

    /**
     * Set the time now. This should only be used for testing purposes.
     *
     * @param int $timeNow the time in seconds since epoch
     * @return void
     */
    public static function setTimeNow($timeNow): void
    {
        self::$timeNow = $timeNow;
    }

    /**
     * Reset the time now if it was set for test purposes.
     *
     * @return void
     */
    public static function resetTimeNow(): void
    {
        self::$timeNow = null;
    }

    /**
     * @return int
     */
    public function getTimeNow(): int
    {
        return self::$timeNow ? self::$timeNow : time();
    }

    /**
     * Constructs an access token.
     *
     * @param array $options An array of options returned by the service provider
     *     in the access token request. The `access_token` option is required.
     * @throws InvalidArgumentException if `access_token` is not provided in `$options`.
     */
    public function __construct(array $options = [])
    {
        if (empty($options['access_token'])) {
            throw new InvalidArgumentException('Required option not passed: "access_token"');
        }

        $this->accessToken = $options['access_token'];

        if (!empty($options['id'])) {
            $this->id = $options['id'];
        }

        if (!empty($options['token_type'])) {
            $this->tokenType = $options['token_type'];
        }

        if (!empty($options['instance_url'])) {
            $this->instanceUrl = $options['instance_url'];
        }

        if (!empty($options['scope'])) {
            $this->scopes = explode(' ', $options['scope']);
        }

        // We need to know when the token expires. Show preference to
        // 'expires_in' since it is defined in RFC6749 Section 5.1.
        // Defer to 'expires' if it is provided instead.
        if (isset($options['expires_in'])) {
            if (!is_numeric($options['expires_in'])) {
                throw new \InvalidArgumentException('expires_in value must be an integer');
            }

            $this->expires = $options['expires_in'] != 0 ? $this->getTimeNow() + $options['expires_in'] : 0;
        } elseif (!empty($options['expires'])) {
            // Some providers supply the seconds until expiration rather than
            // the exact timestamp. Take a best guess at which we received.
            $expires = $options['expires'];

            if (!$this->isExpirationTimestamp($expires)) {
                $expires += $this->getTimeNow();
            }

            $this->expires = $expires;
        }
    }

    /**
     * Check if a value is an expiration timestamp or second value.
     *
     * @param integer $value
     * @return bool
     */
    protected function isExpirationTimestamp($value): int
    {
        // If the given value is larger than the original OAuth 2 draft date,
        // assume that it is meant to be a (possible expired) timestamp.
        $oauth2InceptionDate = 1349067600; // 2012-10-01
        return ($value > $oauth2InceptionDate);
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    /**
     * @return int
     */
    public function getExpires(): ?int
    {
        return $this->expires;
    }

    /**
     * @return bool
     */
    public function hasExpired(): bool
    {
        $expires = $this->getExpires();

        if (empty($expires)) {
            throw new RuntimeException('"expires" is not set on the token');
        }

        return $expires < time();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getToken();
    }

    /**
     * @return string
     */
    public function getInstanceUrl(): string
    {
        return $this->instanceUrl;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $parameters = [
            "scopes" => $this->scopes
        ];

        if ($this->accessToken) {
            $parameters['access_token'] = $this->accessToken;
        }

        if ($this->tokenType) {
            $parameters['token_type'] = $this->tokenType;
        }

        if ($this->expires) {
            $parameters['expires'] = $this->expires;
        }

        if ($this->id) {
            $parameters['id'] = $this->id;
        }

        if ($this->instanceUrl) {
            $parameters['instanceUrl'] = $this->instanceUrl;
        }

        return $parameters;
    }
}
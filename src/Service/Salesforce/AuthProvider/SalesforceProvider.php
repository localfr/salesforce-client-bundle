<?php

namespace Localfr\SalesforceClientBundle\Service\Salesforce\AuthProvider;

use Symfony\Contracts\HttpClient\{HttpClientInterface, ResponseInterface};
use Localfr\SalesforceClientBundle\Service\Salesforce\Token\{AccessToken, TokenBuilder};
use UnexpectedValueException;

class SalesforceProvider implements SalesforceProviderInterface
{
    /**
     * @var string
     */
    const CONTENT_TYPE = 'application/x-www-form-urlencoded';

    /**
     * @var string
     */
    const TOKEN_ENDPOINT = '/services/oauth2/token';

    /**
     * @var string
     */
    const INTROSPECT_ENDPOINT = '/services/oauth2/introspect';

    /**
     * @var string
     */
    const GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:jwt-bearer';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var TokenBuilder
     */
    protected $tokenBuilder;

    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * @var AccessToken
     */
    protected $accessToken;

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
        $this->httpClient = $httpClient;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->username = $username;
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
        $this->url = true === $sandbox ? 'https://test.salesforce.com' : 'https://login.salesforce.com';
        $this->createTokenBuilder();
    }

    /**
     * @return void
     */
    private function createTokenBuilder(): void
    {
        $this->tokenBuilder = new TokenBuilder(
            $this->privateKey,
            $this->publicKey
        );
    }

    /**
     * @inheritdoc
     */
    public function authorize(): void
    {
        if (!$this->accessToken instanceof AccessToken || $this->accessToken->hasExpired()) {
            $url = $this->url . self::TOKEN_ENDPOINT;
            $token = $this->tokenBuilder->build(
                $this->clientId,
                $this->username,
                $this->url
            );
            $body = \http_build_query([
                "grant_type" => self::GRANT_TYPE,
                "assertion" => $token->toString()
            ]);
            $options = [
                "body" => $body,
                "headers" => [
                    "Accept" => "application/json",
                    "Content-Type" => self::CONTENT_TYPE
                ]
            ];
            $response = $this->httpClient->request(
                'POST',
                $url,
                $options
            );

            $token = $this->getParsedResponse($response);
            $introspect = $this->introspect($token['access_token']);
            $token['expires'] = $introspect['exp'];
            $this->accessToken = new AccessToken($token);
        }
    }

    /**
     * @inheritdoc
     */
    public function revoke(): void
    {
        $this->accessToken = null;
    }

    /**
     * @inheritdoc
     */
    public function getToken(): string
    {
        if (!$this->accessToken instanceof AccessToken) {
            $this->authorize();
        }

        return $this->accessToken->getToken();
    }

    /**
     * @inheritdoc
     */
    public function getTokenType(): string
    {
        if (!$this->accessToken instanceof AccessToken) {
            $this->authorize();
        }

        return $this->accessToken->getTokenType();
    }

    /**
     * @inheritdoc
     */
    public function getInstanceUrl(): string
    {
        if (!$this->accessToken instanceof AccessToken) {
            $this->authorize();
        }

        return $this->accessToken->getInstanceUrl();
    }

    /**
     * @inheritdoc
     */
    public function getAuhtorizationHeader(): array
    {
        if (!$this->accessToken instanceof AccessToken) {
            $this->authorize();
        }
        return [
            'Authorization' => sprintf('%s %s', $this->getTokenType(), $this->getToken())
        ];
    }

    /**
     * @param string $token
     * @return array
     */
    private function introspect(string $token): array
    {
        $url = $this->url . self::INTROSPECT_ENDPOINT;
        $body = \http_build_query([
            "client_id" => $this->clientId,
            "client_secret" => $this->clientSecret,
            "token" => $token,
            "token_type_hint" => "access_token"
        ]);
        $options = [
            "body" => $body,
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => self::CONTENT_TYPE
            ]
        ];
        $response = $this->httpClient->request(
            'POST',
            $url,
            $options
        );
        return $this->getParsedResponse($response);
    }

    /**
     * Returns the parsed response.
     *
     * @param  ResponseInterface $response
     * @throws IdentityProviderException
     * @return mixed
     */
    public function getParsedResponse(ResponseInterface $response)
    {
        return $this->parseResponse($response);
    }

    /**
     * Attempts to parse a JSON response.
     *
     * @param  string $content JSON content from response body
     * @return array Parsed JSON data
     * @throws UnexpectedValueException if the content could not be parsed
     */
    protected function parseJson($content)
    {
        $content = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException(sprintf(
                "Failed to parse JSON response: %s",
                json_last_error_msg()
            ));
        }

        return $content;
    }

    /**
     * Returns the content type header of a response.
     *
     * @param  ResponseInterface $response
     * @return string Semi-colon separated join of content-type headers.
     */
    protected function getContentType(ResponseInterface $response)
    {
        return join(';', $response->getHeaders()['content-type']);
    }

    /**
     * Parses the response according to its content-type header.
     *
     * @throws UnexpectedValueException
     * @param  ResponseInterface $response
     * @return array
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $content = $response->getContent();
        $type = $this->getContentType($response);

        if (strpos($type, 'urlencoded') !== false) {
            parse_str($content, $parsed);
            return $parsed;
        }

        // Attempt to parse the string as JSON regardless of content type,
        // since some providers use non-standard content types. Only throw an
        // exception if the JSON could not be parsed when it was expected to.
        try {
            return $this->parseJson($content);
        } catch (UnexpectedValueException $e) {
            if (strpos($type, 'json') !== false) {
                throw $e;
            }

            if ($response->getStatusCode() == 500) {
                throw new UnexpectedValueException(
                    'An OAuth server error was encountered that did not contain a JSON body',
                    0,
                    $e
                );
            }

            return $content;
        }
    }
}
<?php

namespace Localfr\SalesforceClientBundle\Service\Salesforce\Client;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\{HttpClientInterface, ResponseInterface};
use Localfr\SalesforceClientBundle\Service\Salesforce\AuthProvider\SalesforceProviderInterface;
use UnexpectedValueException;

class SalesforceClient
{
    /**
     * @var string
     */
    const API_VERSION = 'v52.0';

    /**
     * @var string
     */
    const DATA_ENDPOINT = '/services/data';

    /**
     * @var string
     */
    const SOBJECTS_ENDPOINT = '/sobjects';

    /**
     * @var string
     */
    const QUERY_ENDPOINT = '/query/?q=';

    /**
     * @var array
     */
    const DEFAULT_HEADERS = [
        "Accept" => "application/json"
    ];

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var SalesforceProviderInterface
     */
    private $salesforceProvider;

    /**
     * @var string
     */
    private $apiVersion;

    /**
     * @param HttpClientInterface $httpClient
     * @param SalesforceProviderInterface $salesforceProvider
     * @param string|null $apiVersion
     */
    public function __construct(
        HttpClientInterface $httpClient,
        SalesforceProviderInterface $salesforceProvider,
        ?string $apiVersion = null
    ) {
        $this->httpClient = $httpClient;
        $this->salesforceProvider = $salesforceProvider;
        $this->apiVersion = $apiVersion ?: self::API_VERSION;
    }

    /**
     * @param string $sObjectType
     * @param string $id
     * @param array $fields
     * 
     * @throws ClientException
     * 
     * @return \stdClass
     */
    public function get(string $sObjectType, string $id, array $fields = ['Id']): \stdClass
    {
        $url = sprintf(
            '%s/%s/%s?%s',
            $this->buildSobjectsUrl(),
            $sObjectType,
            $id,
            \http_build_query(
                [
                    'fields' => implode(",", $fields),
                ]
            )
        );
        
        $response = $this->httpClient->request(
            'GET',
            $url,
            [
                "headers" => array_merge(
                    self::DEFAULT_HEADERS,
                    $this->salesforceProvider->getAuhtorizationHeader()
                )
            ]
        );
        return (object) $this->getParsedResponse($response);
    }

    private function buildSobjectsUrl(): string
    {
        return sprintf(
            '%s%s/%s%s',
            $this->salesforceProvider->getInstanceUrl(),
            self::DATA_ENDPOINT,
            $this->apiVersion,
            self::SOBJECTS_ENDPOINT
        );
    }

    private function buildQueryUrl(): string
    {
        return sprintf(
            '%s%s/%s%s',
            $this->salesforceProvider->getInstanceUrl(),
            self::DATA_ENDPOINT,
            $this->apiVersion,
            self::QUERY_ENDPOINT
        );
    }

    /**
     * Returns the parsed response.
     *
     * @param  ResponseInterface $response
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
                    'Aserver error was encountered that did not contain a JSON body',
                    0,
                    $e
                );
            }

            return $content;
        }
    }
}
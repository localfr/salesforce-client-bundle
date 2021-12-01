<?php

namespace Localfr\SalesforceClientBundle\Service\Salesforce\Client;

use Generator;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Localfr\SalesforceClientBundle\Model\{
    CreateResponse,
    QueryResult,
    SObject
};
use Localfr\SalesforceClientBundle\Service\Salesforce\AuthProvider\SalesforceProviderInterface;

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
    const QUERY_ENDPOINT = '/query';

    /**
     * @var array
     */
    const DEFAULT_HEADERS = [
        "Accept" => "application/json",
        "Content-Type" => "application/json"
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $apiVersion;

    /**
     * @param HttpClientInterface $httpClient
     * @param SalesforceProviderInterface $salesforceProvider
     * @param SerializerInterface $serializer
     * @param string|null $apiVersion
     */
    public function __construct(
        HttpClientInterface $httpClient,
        SalesforceProviderInterface $salesforceProvider,
        SerializerInterface $serializer,
        ?string $apiVersion = null
    ) {
        $this->httpClient = $httpClient;
        $this->salesforceProvider = $salesforceProvider;
        $this->serializer = $serializer;
        $this->apiVersion = $apiVersion ?: self::API_VERSION;
    }

    /**
     * @param string $sObjectType
     * @param string $id
     * @param array $fields
     * 
     * @throws ClientException
     * 
     * @return SObject
     */
    public function get(string $sObjectType, string $id, array $fields = ['Id']): SObject
    {
        $response = $this->getRaw($sObjectType, $id, $fields);

        return $this->serializer->deserialize(
            $response,
            SObject::class,
            'json'
        );
    }

    /**
     * @param string $sObjectType
     * @param string $id
     * @param array $fields
     * 
     * @throws ClientException
     * 
     * @return string
     */
    public function getRaw(string $sObjectType, string $id, array $fields = ['Id']): string
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

        return $response->getContent();
    }

    /**
     * @param QueryResult|string $query
     * 
     * @return QueryResult
     */
    public function query($query): QueryResult
    {
        $response = $this->queryRaw($query);

        return $this->serializer->deserialize(
            $response,
            QueryResult::class,
            'json'
        );
    }

    /**
     * @param QueryResult|string $query
     * 
     * @return string
     */
    public function queryRaw($query): string
    {
        if ($query instanceof QueryResult) {
            if ($query->isDone()) {
                return $query;
            }

            $url = sprintf(
                '%s%s',
                $this->salesforceProvider->getInstanceUrl(),
                $query->getNextRecordsUrl()
            );
        } else {
            $url = sprintf(
                '%s/?%s',
                $this->buildQueryUrl(),
                \http_build_query(
                    [
                        'q' => $query
                    ]
                )
            );
        }

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

        return $response->getContent();
    }

    /**
     * @param string $query
     * 
     * @return Generator
     */
    public function queryIter(string $query): Generator
    {
        $results = $this->query($query);
        
        while (!$results->isDone()) {
            /** @var SObject $record */
            foreach ($results->getRecords() as $record) {
                yield $record;
            }

            $results = $this->query($results);
        }
    }

    /**
     * @param string $sObjectType
     * @param SObject $sObject
     *
     * @throws ClientException
     */
    public function persist(string $sObjectType, SObject $sObject)
    {
        $id = $sObject->Id;
        $method = null !== $id ? 'PATCH' : 'POST';
        $url = $this->buildSobjectsUrl() . '/' . $sObjectType;
        if (null !== $id) {
            $url = $url . '/' . $id;
        }
        $sObject->Id = null;

        $body = $this->serializer->serialize(
            $sObject->getFields(),
            'json',
            [ AbstractObjectNormalizer::SKIP_NULL_VALUES => true ]
        );

        $response = $this->httpClient->request(
            $method,
            $url,
            [
                "body" => $body,
                "headers" => array_merge(
                    self::DEFAULT_HEADERS,
                    $this->salesforceProvider->getAuhtorizationHeader()
                )
            ]
        );

        if('POST' === $method) {
            /** @var CreateResponse $createResponse */
            $createResponse = $this->serializer->deserialize(
                $response->getContent(),
                CreateResponse::class,
                'json'
            );
            
            if ($createResponse->isSuccess()) {
                $sObject->Id = $createResponse->getId();
            }
        } else {
            $response->getContent();
            $sObject->Id = $id;
        }
    }

    /**
     * @param string $sObjectType
     * @param SObject $sObject
     *
     * @throws \RuntimeException
     * @throws ClientException
     */
    public function remove(string $sObjectType, SObject $sObject)
    {
        if (null === $sObject->Id) {
            throw new \RuntimeException("The sObject provided does not have an ID set.");
        }

        $url = $this->buildSobjectsUrl() . '/' . $sObjectType . '/' . $sObject->Id;

        $response = $this->httpClient->request(
            'DELETE',
            $url,
            [
                "headers" => array_merge(
                    self::DEFAULT_HEADERS,
                    $this->salesforceProvider->getAuhtorizationHeader()
                )
            ]
        );

        $response->getContent();
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
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
}
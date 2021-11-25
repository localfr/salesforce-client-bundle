<?php

namespace Localfr\SalesforceClientBundle\Service\Salesforce\AuthProvider;

interface SalesforceProviderInterface
{
    /**
     * @return void
     */
    public function authorize(): void;

    /**
     * @return void
     */
    public function revoke(): void;

    /**
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * @return string|null
     */
    public function getTokenType(): ?string;

    /**
     * @return string|null
     */
    public function getInstanceUrl(): ?string;

    /**
     * @return array
     */
    public function getAuhtorizationHeader(): array;
}
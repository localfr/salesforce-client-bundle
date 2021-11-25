<?php

namespace Localfr\SalesforceClientBundle\Model;

class Attributes
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $url;

    /**
     * @param array|null $payload
     */
    public function __construct(?array $payload = [])
    {
        $this->type = $payload['type'] ?? null;
        $this->url = $payload['url'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null
     * @return self
     */
    public function setType(?string $type = null): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null
     * @return self
     */
    public function setUrl(?string $url = null): self
    {
        $this->url = $url;
        return $this;
    }
}
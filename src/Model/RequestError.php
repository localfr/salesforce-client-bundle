<?php

namespace Localfr\SalesforceClientBundle\Model;

use Doctrine\Common\Collections\{ArrayCollection, Collection};

class RequestError
{
    /**
     * @var Collection|string[]
     */
    private $fields;

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var string|null
     */
    private $statusCode;

    /**
     * @return Collection|string[]|null
     */
    public function getFields(): ?Collection
    {
        return $this->fields;
    }

    /**
     * @param string $field
     *
     * @return self
     */
    public function addField(string $field): self
    {
        if (null === $this->fields) {
            $this->fields = new ArrayCollection();
        }

        if (!$this->fields->contains($field)) {
            $this->fields[] = $field;
        }
        return $this;
    }

    /**
     * @param string $field
     *
     * @return self
     */
    public function removeField(string $field): self
    {
        if (null === $this->fields) {
            return $this;
        }

        if ($this->fields->contains($field)) {
            $this->fields->removeElement($field);
        }
        return $this;
    }

    /**
     * @return null|string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param null|string $message
     *
     * @return RequestError
     */
    public function setMessage(?string $message): RequestError
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getStatusCode(): ?string
    {
        return $this->statusCode;
    }

    /**
     * @param null|string $statusCode
     *
     * @return RequestError
     */
    public function setStatusCode(?string $statusCode): RequestError
    {
        $this->statusCode = $statusCode;

        return $this;
    }
}
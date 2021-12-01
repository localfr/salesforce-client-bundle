<?php

namespace Localfr\SalesforceClientBundle\Model;

use Doctrine\Common\Collections\{ArrayCollection, Collection};

class CreateResponse
{
    /**
     * @var bool
     */
    protected $success;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var Collection|RequestError[]
     */
    protected $errors;

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     *
     * @return self
     */
    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param null|string $id
     *
     * @return self
     */
    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Collection|RequestError[]|null
     */
    public function getErrors(): ?Collection
    {
        return $this->errors;
    }

    /**
     * @param RequestError $error
     *
     * @return self
     */
    public function addError(RequestError $error): self
    {
        if (null === $this->errors) {
            $this->errors = new ArrayCollection();
        }

        if (!$this->errors->contains($error)) {
            $this->errors[] = $error;
        }
        return $this;
    }

    /**
     * @param RequestError $error
     *
     * @return self
     */
    public function removeError(RequestError $error): self
    {
        if (null === $this->errors) {
            return $this;
        }

        if ($this->errors->contains($error)) {
            $this->errors->removeElement($error);
        }
        return $this;
    }
}
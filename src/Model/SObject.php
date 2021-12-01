<?php

namespace Localfr\SalesforceClientBundle\Model;

class SObject
{
    /**
     * @var Attributes
     */
    private $attributes;

    /**
     * @param array|null $payload
     */
    public function __construct(?array $payload = [])
    {
        foreach ($payload as $field => $value) {
            $this->$field = $value;
        }
    }

    /**
     * @return Attributes|null
     */
    public function getAttributes(): ?Attributes
    {
        return $this->attributes;
    }

    /**
     * @param Attributes|null $attributes
     * 
     * @return self
     */
    public function setAttributes(?Attributes $attributes = null): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, mixed $value)
    {
        $this->$name = $value;
    }

    /**
     * @param string $name
     * 
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->$name ?? null;
    }

    /**
     * @param string $name
     * @param array $args
     * 
     * @return mixed
     */
    public function __call(string $name, array $args): mixed
    {
        $property = substr($name, 3);
        if ('get' === substr($name, 0, 3)) {
            return $this->$property ?? null;
        } elseif ('set' === substr($name, 0, 3)) {
            $this->$property = $args[0] ?? null;
            return $this;
        }
    }
}
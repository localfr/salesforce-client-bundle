<?php

namespace Localfr\SalesforceClientBundle\Model;

class SObject extends \stdClass
{
    /**
     * @var Attributes
     */
    private $attributes;

    /**
     * @var array<string,mixed>
     */
    private $fields;

    /**
     * @param array<string,mixed>|null $payload
     */
    public function __construct(?array $payload = [])
    {
        $this->attributes = null;
        if (array_key_exists('attributes', $payload)) {
            $this->attributes = $payload['attributes'];
            unset($payload['attributes']);
        }
        
        $this->setFields($payload);
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
     * @return array<string,mixed>|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @param array<string,mixed> $fields
     * 
     * @return self
     */
    public function setFields(array $fields): self
    {
        $this->fields = null;
        foreach ($fields as $field => $value) {
            $this->fields[$field] = $value;
        }
        return $this;
    }

    

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, mixed $value)
    {
        $this->fields[$name] = $value;
    }

    /**
     * @param string $name
     * 
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->fields[$name] ?? null;
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

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $arr = [];
        foreach (get_object_vars($this) as $property => $value) {
            $arr[$property] = $value;
        }

        return $arr;
    }
}
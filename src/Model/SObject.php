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
        $this->attributes = $payload['attributes'] ?? null;
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
     * @param $name
     *
     * @return string
     */
    protected static function normalizeFieldName($name): string
    {
        return ucwords($name);
    }

    public function __set($name, $value)
    {
        $name = self::normalizeFieldName($name);
        $this->$name = $value;
    }

    public function __get($name)
    {
        $name = self::normalizeFieldName($name);
        return $this->$name ?: null;
    }

    public function __call($name, $arguments)
    {
        $prefix = substr($name, 0, 3);

        if ("get" === $prefix) {
            $field = substr($name, 3);

            return $this->$field;
        }

        if ("set" === $prefix) {
            $field = substr($name, 3);

            $this->$field = array_shift($arguments);

            return $this;
        }
    }
}
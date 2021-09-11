<?php

namespace CraigPaul\Moneris;

use InvalidArgumentException;

trait Gettable
{
    /**
     * Retrieve a property off of the class.
     * @throws \InvalidArgumentException
     */
    public function __get (string $property): mixed
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        throw new InvalidArgumentException(sprintf(
            "[%s] does not contain a property named [%s]",
            self::class,
            $property
        ));
    }
}

<?php

namespace Adagio\DataStore\Exception;

final class NotFound extends \OutOfBoundsException
{
    /**
     *
     * @param string $identifier
     *
     * @return NotFound
     */
    static public function fromIdentifier($identifier)
    {
        return new self("No data found for identifier '$identifier'.");
    }

    /**
     *
     * @param string $name
     * @param string $comparator
     * @param mixed $value
     *
     * @return NotFound
     */
    static public function fromProperty($name, $comparator, $value)
    {
        return new self("No data found where '$value $comparator $name'.");
    }
}

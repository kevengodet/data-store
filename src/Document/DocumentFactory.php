<?php

namespace Adagio\DocumentStore\Document;

use Adagio\DocumentStore\Document;
use Adagio\Rad\Traits\GuessIdentifier;

final class DocumentFactory
{
    use GuessIdentifier;

    /**
     *
     * @param array $properties
     * @param string $identifier
     *
     * @return Document
     */
    public function fromArray(array $properties, $identifier = null)
    {
        return new Document($this->findOrCreateIdentifier($properties, $identifier), $properties);
    }

    /**
     *
     * @param object $object
     * @param string $identifier
     *
     * @return Document
     */
    public function fromObject($object, $identifier = null)
    {
        // Normalize $object
        $data = [];
        foreach ((array) $object as $k => $v) {
            if (false !== $pos = strrpos($k, "\0")) {
                $data[substr($k, $pos + 1)] = $v;
            } else {
                $data[$k] = $v;
            }
        }

        return new Document($this->findOrCreateIdentifier($object, $identifier), $data);
    }

    /**
     *
     * @param array|object $data
     * @param string $identifier
     *
     * @return string
     */
    private function findOrCreateIdentifier($data, $identifier)
    {
        if (!is_null($identifier)) {
            return $identifier;
        }

        if (!is_null($guessed = $this->guessIdentifier($data))) {
            return $guessed;
        }

        return md5(uniqid('', true)); // Poor random function
    }
}

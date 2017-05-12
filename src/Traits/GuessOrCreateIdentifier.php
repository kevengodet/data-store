<?php

namespace Adagio\DataStore\Traits;

use Adagio\Rad\Traits\GuessIdentifier;

trait GuessOrCreateIdentifier
{
    use GuessIdentifier;

    /**
     *
     * @param array $data
     * @param string $identifier
     *
     * @return string
     */
    private function guessOrCreateIdentifier($data, $identifier)
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

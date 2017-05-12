<?php

namespace Adagio\DataStore\Traits;

trait ArrayAccess
{
    /**
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     *
     * @param string $offset
     *
     * @return array
     *
     * @throws \Adagio\DataStore\Exception\NotFound
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     *
     * @param string $offset
     * @param data $value
     */
    public function offsetSet($offset, $value)
    {
        $this->store($value, $offset);
    }

    /**
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}

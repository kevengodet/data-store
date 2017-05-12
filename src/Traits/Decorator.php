<?php

namespace Adagio\DataStore;

use Adagio\DataStore\DataStore;
use Adagio\DataStore\Exception\NotFound;

trait Decorator
{
    /**
     *
     * @var DataStore
     */
    private $store;

    /**
     *
     * @param DataStore $store
     */
    private function setStore(DataStore $store)
    {
        $this->store = $store;
    }

    /**
     *
     * @param array $data
     * @param string $identifier
     *
     * @return $identifier
     */
    public function store(array $data, $identifier = null)
    {
        return $this->store->store($data, $identifier);
    }

    /**
     *
     * @param mixed $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return $this->store->has($identifier);
    }

    /**
     *
     * @param string $identifier
     */
    public function remove($identifier)
    {
        $this->remove($identifier);
    }

    /**
     *
     * @param string $identifier
     *
     * @return array
     *
     * @throws NotFound
     */
    public function get($identifier)
    {
        return $this->store->get($identifier);
    }

    /**
     *
     * @param string $property
     * @param mixed $value
     * @param string $comparator
     *
     * @return array
     */
    public function findBy($property, $value, $comparator = '=')
    {
        return $this->store->findBy($property, $value, $comparator);
    }


    /**
     *
     * @param string $property
     * @param mixed $value
     * @param string $comparator
     *
     * @return array
     *
     * @throws NotFound
     */
    public function findOneBy($property, $value, $comparator = '=')
    {
        return $this->findOneBy($property, $value, $comparator);
    }
}

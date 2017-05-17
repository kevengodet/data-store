<?php

namespace Adagio\DataStore;

use Adagio\DataStore\Exception\NotFound;

interface DataStore
{
    /**
     *
     * @param array $data
     * @param string $identifier
     *
     * @return $identifier
     */
    public function store(array $data, $identifier = null);

    /**
     *
     * @param mixed $identifier
     *
     * @return bool
     */
    public function has($identifier);

    /**
     *
     * @param string $identifier
     */
    public function remove($identifier);

    /**
     *
     * @param string $identifier
     *
     * @return array
     *
     * @throws NotFound
     */
    public function get($identifier);

    /**
     *
     * @param string $property
     * @param mixed $value
     * @param string $comparator
     *
     * @return array
     */
    public function findBy($property, $value, $comparator = '=');


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
    public function findOneBy($property, $value, $comparator = '=');

    /**
     *
     * @return object[]
     */
    public function findAll();
}

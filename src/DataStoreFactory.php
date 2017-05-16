<?php

namespace Adagio\DataStore;

interface DataStoreFactory
{
    /**
     *
     * @param string $name
     *
     * @return DataStore
     */
    public function create($name);
}

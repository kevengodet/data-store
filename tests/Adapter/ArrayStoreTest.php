<?php

namespace Adagio\Tests\DataStore\Adapter;

use Adagio\DataStore\Adapter\ArrayStore;

class ArrayStoreTest extends AbstractStoreTest
{
    function setUp()
    {
        $this->store = new ArrayStore;
    }

    function tearDown()
    {
        $this->store = null;
        unset($this->store);
    }
}

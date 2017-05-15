<?php

namespace Adagio\Tests\DataStore\Adapter;

use Doctrine\DBAL\DriverManager;
use Adagio\DataStore\Adapter\DbalStore;

//class DbalStoreTest extends AbstractStoreTest
//{
//    function setUp()
//    {
//        $conn = DriverManager::getConnection(['url' => 'pgsql://repo:repo@localhost/repo']);
//        $this->store = new DbalStore($conn);
//    }
//
//    function tearDown()
//    {
//        $this->store = null;
//        unset($this->store);
//    }
//}

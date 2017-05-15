<?php

require_once __DIR__.'/bootstrap.php';

use Doctrine\DBAL\DriverManager;
use Adagio\DataStore\Adapter\DbalStore;

$conn = DriverManager::getConnection(['url' => 'pgsql://repo:repo@localhost/repo']);
$store = new DbalStore($conn);

var_dump($store->store(['foo' => 'bar'], $k = 'foo_'.uniqid()));
var_dump($store->get($k));
var_dump($store->findOneBy('foo', 'bar'));
var_dump($store->findBy('foo', 'bar'));
var_dump($store->has($k));
$store->remove($k);
var_dump($store->has($k));

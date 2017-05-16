<?php

namespace Adagio\DataStore\Factory;

use Adagio\DataStore\DataStoreFactory;
use Adagio\DataStore\Adapter\DbalStore;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

final class DbalDataStoreFactory implements DataStoreFactory
{
    /**
     *
     * @var Connection
     */
    private $connection;

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var DataSotre[]
     */
    private $stores = [];

    /**
     *
     * @param Connection $connection
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $connection, LoggerInterface $logger = null)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     *
     * @param string $name
     *
     * @return DataStore
     */
    public function create($name)
    {
        if (array_key_exists($name, $this->stores)) {
            return $this->stores[$name];
        }

        return $this->stores[$name] = new DbalStore($this->connection, $name, $this->logger);
    }
}

<?php

namespace Adagio\DataStore\Adapter;

use Adagio\DataStore\DataStore;
use Adagio\DataStore\Exception\NotFound;
use Adagio\DataStore\Traits\GuessOrCreateIdentifier;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Statement;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class JsonbType extends Type
{
    public function getName() {return 'jsonb';}
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {return 'JSONB';}
}

final class DbalStore implements DataStore
{
    use GuessOrCreateIdentifier;

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
     * @var string
     */
    private $tableName;

    /**
     *
     * @param Connection $connection
     * @param string $tableName
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $connection, $tableName = 'data_store', LoggerInterface $logger = null)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->logger = $logger ?: new NullLogger;

        if (!Type::hasType('jsonb')) {
            Type::addType('jsonb', JsonbType::class);
        }

        if (!$this->tableExists($tableName, $connection)) {
            $this->createTable($tableName, $connection);
        }
    }

    /**
     *
     * @param string $tableName
     * @param Connection $connection
     *
     * @return bool
     */
    private function tableExists($tableName, Connection $connection)
    {
        return in_array($tableName, $connection->getSchemaManager()->listTableNames());
    }

    /**
     *
     * @param string $tableName
     * @param Connection $connection
     * @throws DBALException
     */
    private function createTable($tableName, Connection $connection)
    {
        if (!$this->isPostgresql94($connection)) {
            throw new DBALException("Type 'Jsonb' requires PostgreSQL 9.4+");
        }

        $this->logger->info("Create table '$tableName' as a data store.");

        $schema = new Schema;
        $table = $schema->createTable($tableName);
        $table->addColumn('id', Type::STRING);
        $table->setPrimaryKey(['id'], $tableName.'_id_pk');
        $table->addColumn('data', 'jsonb'); // jsonb only on PgSQL 9.4+
        $queries = $schema->toSql($connection->getSchemaManager()->getDatabasePlatform()); // get queries to create this schema.

        // Hack specific to PostgreSQL 9.4+
        $queries[] = "CREATE INDEX {$tableName}_data_idx ON {$tableName} USING gin (data);";

        // Execute creation queries
        $connection->beginTransaction();
        foreach ($queries as $query) {
            $this->logger->debug(sprintf("Execute query `%s`", $query));
            $connection->exec($query);
        }
        $connection->commit();
    }

    /**
     *
     * @param Connection $connection
     *
     * @return boolean
     */
    private function isPostgreSQL94(Connection $connection)
    {
        if (!$connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSqlPlatform) {
            return false;
        }

        sscanf($connection->fetchColumn('SELECT version();'), 'PostgreSQL %s ', $version);

        return version_compare($version, '9.4', '>=');
    }

    /**
     *
     * @param string $id
     * @param array $data
     *
     * @return Statement
     */
    private function buildInsertStatement($id, array $data)
    {
        $query = $this->connection->createQueryBuilder()
                            ->insert($this->tableName);
        $values = [
            'id' => '?',
            'data' => '?',
        ];

        $query->setParameter(0, $id);
        $query->setParameter(1, json_encode($data));

        return $query->values($values);
    }

    // DataStore implementation

    /**
     *
     * @param array $data
     * @param string $identifier
     *
     * @return $identifier
     */
    public function store(array $data, $identifier = null)
    {
        $id = $this->guessOrCreateIdentifier($data, $identifier);
        $this->buildInsertStatement($id, $data)->execute();

        return $id;
    }

    /**
     *
     * @param mixed $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        $queryBuilder = $this->connection
            ->createQueryBuilder()
            ->select('1')
            ->from($this->tableName)
            ->where('id = :id');

        return false !== $this->connection->fetchAssoc($queryBuilder->getSQL(), ['id' => $identifier]);
    }

    /**
     *
     * @param string $identifier
     */
    public function remove($identifier)
    {
        $this->connection->delete($this->tableName, ['id' => $identifier]);
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
        $queryBuilder = $this->connection
            ->createQueryBuilder()
            ->select('data')
            ->from($this->tableName)
            ->where('id = :id');

        $result = $this->connection->fetchColumn($queryBuilder->getSQL(), ['id' => $identifier]);

        if (false === $result) {
            throw NotFound::fromIdentifier($identifier);
        }

        return json_decode($result, true);
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
        if ($comparator != '=') {
            throw new \InvalidArgumentException('Only = operator implemented in '.__METHOD__.' at the moment.');
        }

        $queryBuilder = $this->connection
            ->createQueryBuilder()
            ->select('id', 'data')
            ->from($this->tableName)
            ->where('data::jsonb @> :json');

        $results = $this->connection->fetchAll($queryBuilder->getSQL(), [':json' => json_encode([$property => $value])]);

        $entities = [];
        foreach ($results as $row) {
            $entities[$row['id']] = json_decode($row['data'], true);
        }

        return $entities;
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
        $results = $this->findBy($property, $value, $comparator);

        if (!$results) {
            // Not a single entity found
            throw NotFound::fromProperty($property, $comparator, $value);
        }

        return reset($results);
    }

    /**
     *
     * @return object[]
     */
    public function findAll()
    {
        $queryBuilder = $this->connection
            ->createQueryBuilder()
            ->select('id', 'data')
            ->from($this->tableName);

        $results = $this->connection->fetchAll($queryBuilder->getSQL());

        $entities = [];
        foreach ($results as $row) {
            $entities[$row['id']] = json_decode($row['data'], true);
        }

        return $entities;
    }
}

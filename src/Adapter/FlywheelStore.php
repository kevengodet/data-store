<?php

namespace Adagio\DataStore\Adapter;

use Adagio\DataStore\DataStore;
use Adagio\DataStore\Exception\NotFound;
use Adagio\DataStore\Traits\GuessOrCreateIdentifier;

use JamesMoss\Flywheel\Repository;
use JamesMoss\Flywheel\Document;

final class FlywheelStore implements DataStore
{
    use GuessOrCreateIdentifier;

    /**
     *
     * @var Repository
     */
    private $repository;

    /**
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
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
        $id = $this->guessOrCreateIdentifier($data, $identifier);
        $doc = new Document($data);
        $doc->setId($id);

        if ($this->has($id)) {
            $this->repository->update($doc);
        } else {
            $this->repository->store($doc);
        }
    }

    /**
     *
     * @param mixed $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        $path = $this->repository->getPathForDocument($identifier);

        return file_exists($path);
    }

    /**
     *
     * @param string $identifier
     */
    public function remove($identifier)
    {
        if (!$this->has($identifier)) {
            return;
        }

        $this->repository->delete($identifier);
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
        if (false === $data = $this->repository->findById($identifier)) {
            throw NotFound::fromIdentifier($identifier);
        }

        return get_object_vars($data);
    }

    /**
     *
     * @param string $property
     * @param mixed $value
     * @param string $comparator
     *
     * @return array
     */
    public function findBy($property, $value = null, $comparator = '=')
    {
        $properties = is_array($property) ?
                $property :
                [[ $property, $value, $comparator ]];

        $query = $this->repository->query();
        foreach ($properties as $property) {
            list($propertyName, $value, $comparator) = $property;

            if (!$comparator) {
                $comparator = '==';
            } elseif ($comparator === '=') {
                $comparator = '==';
            }

            $query->where($propertyName, $comparator, $value);
        }

        $entries = [];
        foreach ($query->execute() as $entry) {
            $entries[$entry->getId()] = get_object_vars($entry);
        }

        return $entries;
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
    public function findOneBy($property, $value = null, $comparator = '=')
    {
        $properties = is_array($property) ?
                $property :
                [[ $property, $value, $comparator ]];

        $query = $this->repository->query()->limit(1);
        foreach ($properties as $property) {
            @list($propertyName, $value, $comparator) = $property;

            if (!$comparator) {
                $comparator = '==';
            } elseif ($comparator === '=') {
                $comparator = '==';
            }

            $query->where($propertyName, $comparator, $value);
        }

        $result = $query->execute();

        if (!$result->count()) {
            throw NotFound::fromProperty(isset($propertyName) ? $propertyName : '?', $comparator, $value);
        }

        return get_object_vars($result->first());
    }

    /**
     *
     * @return object[]
     */
    public function findAll()
    {
        $entries = [];
        foreach ($this->repository->findAll() as $entry) {
            $entries[$entry->getId()] = get_object_vars($entry);
        }

        return $entries;
    }
}

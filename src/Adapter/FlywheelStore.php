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
    public function findBy($property, $value, $comparator = '=')
    {
        if ('=' === $comparator) {
            $comparator = '==';
        }

        $entries = [];
        foreach ($this->repository
                ->query()
                ->where($property, $comparator, $value)
                ->execute() as $key => $entry) {
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
    public function findOneBy($property, $value, $comparator = '=')
    {
        if ('=' === $comparator) {
            $comparator = '==';
        }

        $result = $this->repository
                    ->query()
                    ->where($property, $comparator, $value)
                    ->limit(1)
                    ->execute();

        if (!$result->count()) {
            throw NotFound::fromProperty($property, $comparator, $value);
        }

        return get_object_vars($result->first());
    }
}

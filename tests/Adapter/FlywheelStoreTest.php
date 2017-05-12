<?php

namespace Adagio\Tests\DataStore\Adapter;

use Adagio\DataStore\Adapter\FlywheelStore;
use JamesMoss\Flywheel\Repository;
use JamesMoss\Flywheel\Config;

class FlywheelStoreTest extends AbstractStoreTest
{
    function setUp()
    {
        $this->repository = new Repository('test', new Config(sys_get_temp_dir().'/flywheel-store-test/'));
        $this->store = new FlywheelStore($this->repository);
    }

    function tearDown()
    {
        $path = $this->repository->getPath();
        if (!$path) {
            return;
        }
        if (!file_exists($path)) {
            return;
        }
        shell_exec(sprintf("rm -rf $path"));
    }
}

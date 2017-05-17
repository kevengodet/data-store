<?php

namespace Adagio\Tests\DataStore\Adapter;

abstract class AbstractStoreTest extends \PHPUnit\Framework\TestCase
{
    function test()
    {
        $this->store->store(['foo' => 'bar1'], 'baz1');

        $this->assertTrue($this->store->has('baz1'));
        $this->assertFalse($this->store->has('wat'));
        $this->assertEquals(['foo' => 'bar1'], $this->store->get('baz1'));

        $this->store->store(['foo' => 'bar1'], 'baz2');
        $this->store->store(['foo' => 'bar2'], 'baz3');
        $this->store->store(['foo' => 'bar2'], 'baz4');
        $this->store->store(['foo' => 'bar3'], 'baz5');

        $this->assertEquals(['baz2' => ['foo' => 'bar1'], 'baz1' => ['foo' => 'bar1']], $this->store->findBy('foo', 'bar1'), '', 0.0, 10, true);

        $this->assertEquals(['foo' => 'bar3'], $this->store->findOneBy('foo', 'bar3'));

        $this->assertEquals(
                [
                    'baz1' => ['foo' => 'bar1'],
                    'baz2' => ['foo' => 'bar1'],
                    'baz3' => ['foo' => 'bar2'],
                    'baz4' => ['foo' => 'bar2'],
                    'baz5' => ['foo' => 'bar3'],
                ],
                $this->store->findAll(), '', 0.0, 10, true
        );
    }

    /**
     *
     * @expectedException \Adagio\DataStore\Exception\NotFound
     */
    function testNotFound()
    {
        $this->store->get('not-in-store');
    }

    /**
     *
     * @expectedException \Adagio\DataStore\Exception\NotFound
     */
    function testFondOneNotFound()
    {
        $this->store->findOneBy('name', 'not-in-store');
    }
}

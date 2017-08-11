<?php

namespace DataMapper\Tests;

$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use DataMapper\UserMapper;
use DataMapper\User;

/**
 * UserMapperTest用于测试数据映射模式
 */
class DataMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UserMapper
     */
    protected $mapper;

    /**
     * @var DBAL
     */
    protected $dbal;

    protected function setUp()
    {
        $this->dbal = $this->getMockBuilder('DataMapper\DBAL')
                ->disableAutoload()
                ->setMethods(array('insert', 'update', 'find', 'findAll'))
                ->getMock();

        $this->mapper = new UserMapper($this->dbal);
    }

    public function getNewUser()
    {
        return array(array(new User(null, 'Odysseus', 'Odysseus@ithaca.gr')));
    }

    public function getExistingUser()
    {
        return array(array(new User(1, 'Odysseus', 'Odysseus@ithaca.gr')));
    }

    /**
     * @dataProvider getNewUser
     */
    public function testPersistNew(User $user)
    {
        $this->dbal->expects($this->once())
                ->method('insert');
        $this->mapper->save($user);
    }

    /**
     * @dataProvider getExistingUser
     */
    public function testPersistExisting(User $user)
    {
        $this->dbal->expects($this->once())
                ->method('update');
        $this->mapper->save($user);
    }

    /**
     * @dataProvider getExistingUser
     */
    public function testRestoreOne(User $existing)
    {
        $row = array(
            'userid'   => 1,
            'username' => 'Odysseus',
            'email'    => 'Odysseus@ithaca.gr'
        );
        $rows = new \ArrayIterator(array($row));
        $this->dbal->expects($this->once())
                ->method('find')
                ->with(1)
                ->will($this->returnValue($rows));

        $user = $this->mapper->findById(1);
        $this->assertEquals($existing, $user);
    }

    /**
     * @dataProvider getExistingUser
     */
    public function testRestoreMulti(User $existing)
    {
        $rows = array(array('userid' => 1, 'username' => 'Odysseus', 'email' => 'Odysseus@ithaca.gr'));
        $this->dbal->expects($this->once())
                ->method('findAll')
                ->will($this->returnValue($rows));

        $user = $this->mapper->findAll();
        $this->assertEquals(array($existing), $user);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage User #404 not found
     */
    public function testNotFound()
    {
        $this->dbal->expects($this->once())
                ->method('find')
                ->with(404)
                ->will($this->returnValue(array()));

        $user = $this->mapper->findById(404);
    }
}
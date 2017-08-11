<?php

namespace Proxy\Tests;

$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use Proxy\Record;
use Proxy\RecordProxy;

class ProxyTest extends \PHPUnit\Framework\TestCase
{
    public function testSetAttribute(){
        $data = [];
        $proxy = new RecordProxy($data);
        $proxy->xyz = false;
        $this->assertTrue($proxy->xyz===false);
    }
}
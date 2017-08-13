<?php
    
    namespace ChainOfResponsibilities\Tests;
    
    $autoLoadFilePath = '../../vendor/autoload.php';
    require_once $autoLoadFilePath;
    
    use ChainOfResponsibilities\Request;
    use ChainOfResponsibilities\Responsible\FastStorage;
    use ChainOfResponsibilities\Responsible\SlowStorage;
    use ChainOfResponsibilities\Responsible;
    
    /**
     * ChainTest用于测试责任链模式
     */
    class ChainTest extends \PHPUnit\Framework\TestCase
    {
    
        /**
         * @var FastStorage
         */
        protected $chain;
    
        protected function setUp()
        {
            $this->chain = new FastStorage(array('bar' => 'baz'));
            $this->chain->append(new SlowStorage(array('bar' => 'baz', 'foo' => 'bar')));
        }
    
        public function makeRequest()
        {
            $request = new Request();
            $request->verb = 'get';
    
            return array(
                array($request)
            );
        }
    
        /**
         * @dataProvider makeRequest
         */
        public function testFastStorage($request)
        {
            $request->key = 'bar';
            $ret = $this->chain->handle($request);
    
            $this->assertTrue($ret);
            $this->assertObjectHasAttribute('response', $request);
            $this->assertEquals('baz', $request->response);
            // despite both handle owns the 'bar' key, the FastStorage is responding first
            $className = 'ChainOfResponsibilities\Responsible\FastStorage';
            $this->assertEquals($className, $request->forDebugOnly);
        }
    
        /**
         * @dataProvider makeRequest
         */
        public function testSlowStorage($request)
        {
            $request->key = 'foo';
            $ret = $this->chain->handle($request);
    
            $this->assertTrue($ret);
            $this->assertObjectHasAttribute('response', $request);
            $this->assertEquals('bar', $request->response);
            // FastStorage has no 'foo' key, the SlowStorage is responding
            $className = 'ChainOfResponsibilities\Responsible\SlowStorage';
            $this->assertEquals($className, $request->forDebugOnly);
        }
    
        /**
         * @dataProvider makeRequest
         */
        public function testFailure($request)
        {
            $request->key = 'kurukuku';
            $ret = $this->chain->handle($request);
    
            $this->assertFalse($ret);
            // the last responsible :
            $className = 'ChainOfResponsibilities\Responsible\SlowStorage';
            $this->assertEquals($className, $request->forDebugOnly);
        }
    }
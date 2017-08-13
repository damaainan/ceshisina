<?php
    
    namespace DesignPatterns\Tests\Visitor\Tests;
    
    $autoLoadFilePath = '../../vendor/autoload.php';
    require_once $autoLoadFilePath;


    
    use Visitor;
    
    /**
     * VisitorTest 用于测试访问者模式
     */
    class VisitorTest extends \PHPUnit\Framework\TestCase
    {
    
        protected $visitor;
    
        protected function setUp()
        {
            $this->visitor = new Visitor\RolePrintVisitor();
        }
    
        public function getRole()
        {
            return array(
                array(new Visitor\User("Dominik"), 'Role: User Dominik'),
                array(new Visitor\Group("Administrators"), 'Role: Group: Administrators')
            );
        }
    
        /**
         * @dataProvider getRole
         */
        public function testVisitSomeRole(Visitor\Role $role, $expect)
        {
            $this->expectOutputString($expect);
            $role->accept($this->visitor);
        }
    
        /**
         * @expectedException \InvalidArgumentException
         * @expectedExceptionMessage Mock
         */
        public function testUnknownObject()
        {
            $mock = $this->getMockForAbstractClass('Visitor\Role');
            $mock->accept($this->visitor);
        }
    }
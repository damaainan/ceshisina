    <?php 
    
    class SystemA
    {
      public function operationA()
      {
          echo "operationA <br>";
      }
    }
    
    class SystemB
    {
      public function operationB()
      {
          echo "operationB <br>";
      }
    }
    
    class SystemC
    {
      public function operationC()
      {
          echo "operationC <br>";
      }
    }
    
    class Facade
    {
      protected $systemA;
      protected $systemB;
      protected $systemC;
    
      function __construct()
      {
          $this->systemA = new SystemA();
          $this->systemB = new SystemB();
          $this->systemC = new SystemC();
      }
    
      public function myOperation()
      {
          $this->systemA->operationA();
          $this->systemB->operationB();
          $this->systemC->operationC();
      }
    }
    
    $facade = new Facade();
    $facade->myOperation();
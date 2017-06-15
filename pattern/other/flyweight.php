    <?php
    interface Flyweight{
      public function operation();
    }
    
    class MyFlyweight implements Flyweight
    {
      protected $intrinsicState;
      function __construct($str)
      {
          $this->intrinsicState = $str;
      }
    
      public function operation()
      {
          echo 'MyFlyweight['.$this->intrinsicState.'] do operation. <br>';
      }
    }
    
    class FlyweightFactory
    {
      protected static $flyweightPool;
      function __construct()
      {
          if (!isset(self::$flyweightPool)) {
              self::$flyweightPool = [];
          }
      }
      public function getFlyweight($str)
      {
    
          if (!array_key_exists($str,self::$flyweightPool)) {
              $fw = new MyFlyweight($str);
              self::$flyweightPool[$str] = $fw;
              return $fw;
          } else {
              echo "aready in the pool,use the exist one: <br>";
              return self::$flyweightPool[$str];
          }
    
      }
    }
    
    $factory = new FlyweightFactory();
    $fw = $factory->getFlyweight('one');
    $fw->operation();
    
    $fw1 = $factory->getFlyweight('two');
    $fw1->operation();
    
    $fw2 = $factory->getFlyweight('one');
    $fw2->operation();
    
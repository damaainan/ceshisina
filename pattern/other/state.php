    <?php 
    
    class Context{
      protected $state;
      function __construct()
      {
          $this->state = StateA::getInstance();
      }
      public function changeState(State $state)
      {
          $this->state = $state;
      }
    
      public function request()
      {
          $this->state->handle($this);
      }
    }
    
    abstract class State{
      abstract function handle(Context $context);
    }
    
    class StateA extends State
    {
      private static $instance;
      private function __construct(){}
      private function __clone(){}
    
      public static function getInstance()
      {
          if (!isset(self::$instance)) {
              self::$instance = new self;
          }
          return self::$instance;
      }
    
      public function handle(Context $context)
      {
          echo "doing something in State A.\n done,change state to B <br>";
          $context->changeState(StateB::getInstance());
      }
    }
    
    class StateB extends State
    {
      private static $instance;
      private function __construct(){}
      private function __clone(){}
    
      public static function getInstance()
      {
          if (!isset(self::$instance)) {
              self::$instance = new self;
          }
          return self::$instance;
      }
    
      public function handle(Context $context)
      {
          echo "doing something in State B.\n done,change state to A <br>";
          $context->changeState(StateA::getInstance());
      }
    }
    
    $context = new Context();
    $context->request();
    $context->request();
    $context->request();
    $context->request();
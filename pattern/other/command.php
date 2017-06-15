    <?php 
    class Receiver
    {
      public function Action()
      {
          echo "Receiver->Action";
      }
    }
    
    abstract class Command{
      protected $receiver;
      function __construct(Receiver $receiver)
      {
          $this->receiver = $receiver;
      }
      abstract public function Execute();
    }
    
    class MyCommand extends Command
    {
      function __construct(Receiver $receiver)
      {
          parent::__construct($receiver);
      }
    
      public function Execute()
      {
          $this->receiver->Action();
      }
    }
    
    class Invoker
    {
      protected $command;
      function __construct(Command $command)
      {
          $this->command = $command;
      }
    
      public function Invoke()
      {
          $this->command->Execute();
      }
    }
    
    $receiver = new Receiver();
    $command = new MyCommand($receiver);
    $invoker = new Invoker($command);
    $invoker->Invoke();
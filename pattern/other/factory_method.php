    <?php 
    interface Animal{
      public function run();
      public function say();
    }
    class Cat implements Animal
    {
      public function run(){
          echo "I ran slowly <br>";
      }
      public function say(){
          echo "I am Cat class <br>";
      }
    }
    class Dog implements Animal
    {
      public function run(){
          echo "I'm running fast <br>";
      }
      public function say(){
          echo "I am Dog class <br>";
      }
    }
    abstract class Factory{
      abstract static function createAnimal();
    }
    class CatFactory extends Factory
    {
      public static function createAnimal()
      {
          return new Cat();
      }
    }
    class DogFactory extends Factory
    {
      public static function createAnimal()
      {
          return new Dog();
      }
    }
    
    $cat = CatFactory::createAnimal();
    $cat->say();
    $cat->run();
    
    $dog = DogFactory::createAnimal();
    $dog->say();
    $dog->run();
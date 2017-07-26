<?php 

header("Content-type:text/html; Charset=utf-8");

/**
 *
 *
 *
 *
 * 
 */

/*游戏原来的角色类
class Person{
    public function clothes(){
        echo "长衫".PHP_EOL;
    }
}
*/

//装饰器接口
interface Decorator
{
   public function beforeDraw();
   public function afterDraw();
}
//具体装饰器1-宇航员装饰
class AstronautDecorator implements Decorator
{
    public function beforeDraw()
    {
        echo "穿上T恤".PHP_EOL;
    }
    function afterDraw()
    {
        echo "穿上宇航服".PHP_EOL;
        echo "穿戴完毕".PHP_EOL;
    }
}
//具体装饰器2-警察装饰
class PoliceDecorator implements Decorator{
    public function beforeDraw()
    {
        echo "穿上警服".PHP_EOL;
    }
    function afterDraw()
    {
        echo "穿上防弹衣".PHP_EOL;
        echo "穿戴完毕".PHP_EOL;
    }
}
//被装饰者
class Person{
    protected $decorators = array(); //存放装饰器
    //添加装饰器
    public function addDecorator(Decorator $decorator)
    {
        $this->decorators[] = $decorator;
    }
    //所有装饰器的穿长衫前方法调用
    public function beforeDraw()
    {
        foreach($this->decorators as $decorator)
        {
            $decorator->beforeDraw();
        }
    }
    //所有装饰器的穿长衫后方法调用
    public function afterDraw()
    {
        $decorators = array_reverse($this->decorators);
        foreach($decorators as $decorator)
        {
            $decorator->afterDraw();
        }
    }
    //装饰方法
    public function clothes(){
        $this->beforeDraw();
        echo "穿上长衫".PHP_EOL;
        $this->afterDraw();
    }
}
//警察装饰
$police = new Person;
$police->addDecorator(new PoliceDecorator);
$police->clothes();
//宇航员装饰
$astronaut = new Person;
$astronaut->addDecorator(new AstronautDecorator);
$astronaut->clothes();
//混搭风
$madman = new Person;
$madman->addDecorator(new PoliceDecorator);
$madman->addDecorator(new AstronautDecorator);
$madman->clothes();
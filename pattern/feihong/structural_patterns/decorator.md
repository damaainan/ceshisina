### 装饰器模式
装饰模式(Decorator)又名包装(Wrapper)模式。装饰模式以对客户端透明的方式扩展对象的功能，是继承关系的一个替代方案。

装饰模式动态地给一个对象附加上更多的责任。装饰模式并不会改变原来的方法，只会去扩展。

Python从语言层面支持[装饰器](http://www.liaoxuefeng.com/wiki/0014316089557264a6b348958f449949df42a6d3a2e542c000/0014318435599930270c0381a3b44db991cd6d858064ac0000)：假设我们要增强`now()`函数的功能，比如，在函数调用前后自动打印日志，但又不希望修改`now()`函数的定义，这种在代码运行期间动态增加功能的方式，称之为“装饰器”（Decorator）。

接下来介绍PHP里实现的例子。

装饰器理解起来比较简单，但实现起来代码会比较多。
![](http://images2015.cnblogs.com/blog/663847/201706/663847-20170624171646773-1474083001.png)

在装饰模式中的角色有：

- 抽象构件(Component)角色：给出一个抽象接口，以规范准备接收附加责任的对象。
- 具体构件(ConcreteComponent)角色：定义一个将要接收附加责任的类。
- 装饰(Decorator)角色：持有一个构件(Component)对象的实例，并定义一个与抽象构件接口一致的接口。
- 具体装饰(ConcreteDecorator)角色：负责给构件对象“贴上”附加的责任。

我们以MVC框架返回结果为例（具体查看github代码https://github.com/52fhy/design_patterns）：


我们可能需要返回网页，或者JSON，或者XML。我们可以使用装饰器模式根据配置实现不同的输出：

其中：
1、我们要定义Component：这里我写的是`Yjc\IResponse`，里面有个`output()方法`;
2、具体要被装饰器的构件：这里我写的是`Yjc\App`类；
3、装饰角色：这里我写的是`Yjc\Decorator\Decorator`类；
4、具体装饰角色：示例代码里是`Json`、`Xml`、`Template`等类；

`IResponse`:
``` php
namespace Yjc;

interface IResponse
{
    public function output($data);
}
```

`App`类需要继承`IResponse`：
```
namespace Yjc;

use Yjc\Decorator\Json;
use Yjc\Decorator\Template;
use Yjc\Decorator\Xml;

class App implements IResponse
{

    protected $view_data;

    public function run(){
    
        //这里省略路由解析代码...
        
        //res是控制器方法返回的结果
        $this->output($res);
    }

    public function output($data)
    {
        return (new Json($this))->output($data);
    }

    //模板变量分配，代码已省略
    public function assign($key, $view_data){}

    //模板显示，代码已省略
    public function display($file = ''){}
}
```

装饰`Decorator`类：
``` php
namespace Yjc\Decorator;

use Yjc\IResponse;

class Decorator implements IResponse
{
    private $reponse;

    public function __construct(IResponse $response)
    {
        $this->reponse = $response;
    }

    public function output($data)
    {
        $this->reponse->output($data);
    }
}
```

具体装饰类实例：
``` php
namespace Yjc\Decorator;

use Yjc\IResponse;

class Json extends Decorator
{
    public function output($data)
    {
        header('Content-type: application/json');
        echo json_encode($data);
        exit;
    }
}
```
`Xml`、`Template`的实现请查看源码。

通过修改`App`类里的`output`方法实现不同格式的输出。测试代码：
```
namespace App;

use Yjc\App;

class DecoratorTest extends App
{
    public function index(){
        $res = ['name' => 'yjc'];
        return $res;
//        $this->assign('name', 'vvv');
//        $this->display();
    }

}
```
http://localhost:8888/DecoratorTest/index

当选择Json时，输出：
```
{"code":1,"info":"\u6210\u529f","data":{"name":"yjc"}}
```
当选择Xml时，输出：
```
<?xml version="1.0" encoding="utf-8"?><xml><code>1</code><info>成功</info><data><name>yjc</name></data></xml>
```
当选择Template时，我们需要在项目根目录建立`/DecoratorTest/index.php`模板文件。模板文件可以使用传过来的变量：
``` php
<?php

var_dump($name);
```
也可以使用`$this->assign()`分配变量，这时候需要使用`$this->display()`显示模板。
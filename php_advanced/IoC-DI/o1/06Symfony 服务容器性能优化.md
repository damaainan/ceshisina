## Symfony 服务容器性能优化

来源：[http://blog.phpzendo.com/?p=342](http://blog.phpzendo.com/?p=342)

时间 2018-05-14 22:16:38

 
本文是依赖注入（Depeendency Injection）系列教程的最后一篇文章，本系列教程主要讲解如何使用 PHP 实现一个轻量级服务容器，教程包括：

 
* [第 1 篇：什么是依赖注入？][2]  
* [第 2 篇：是否需要使用依赖注入容器？][3]  
* [第 3 篇：Symfony 服务容器入门][4]  
* [第 4 篇：Symfony 服务容器：使用建造者创建服务][5]  
* [第 5 篇：Symfony 服务容器：使用 XML 或 YAML 文件描述服务][6]  
* [第 6 篇：Symfony 服务容器性能优化][7]  
 
 
## 术语

 
* **`Depeendency Injection`**  译作 **`依赖注入`**   
* **`Depeendency Injection Container`**  译作 **`依赖注入容器`**   
* **`Container`**  译作 **`容器`**   
* **`Service Container`**  译作 **`服务容器`**   
* **`Session`**  译作 **`会话`**   
* **`Object-Oriented`**  译作 **`面向对象`**   
* **`mock`**  译作 **`模拟`**   
* **`anti-patterns`**  译作 **`反模式`**   
* **`hardcoded`**  译作 **`硬编码`**   
* **`dumper`**  译作 **`转存器`**   
* **`loader`**  译作 **`加载器`**   
 
 
## 正文
 
在本系列关于依赖注入的前五篇文章中，我们逐步介绍了这个简单实用的设计模式背后的主要概念。我们还谈到了一个将用于 Symfony 2 的轻量级 PHP 容器的实现。
 
但随着 XML 和 YAML 配置文件的引入，您可能会对容器本身的性能产生怀疑。即使服务是延迟加载，在每个请求中读取一堆 XML 或 YAML 文件，并通过使用自省（Introspection）来创建对象在 PHP 中可能效率不高。由于容器几乎是应用程序的基石，它的速度确实很重要。
 
一方面，使用 XML 或 YAML 来描述服务及其配置是非常强大和灵活的：

```xml
<container xmlns="http://symfony-project.org/2.0/container">
foo</parameter>
bar</parameter>
Zend_Mail</parameter>
  </parameters>
  <services>
    <service id="mail.transport" class="Zend_Mail_Transport_Smtp" shared="false">
      <argument>smtp.gmail.com</argument>
      <argument type="collection">
        <argument key="auth">login</argument>
        <argument key="username">%mailer.username%</argument>
        <argument key="password">%mailer.password%</argument>
        <argument key="ssl">ssl</argument>
        <argument key="port">true </argument>
      </argument>
    </service>
    <service id="mailer" class="%mailer.class%">
      <call method="setDefaultTransport">
        <argument type="service" id="mail.transport">
      </argument></call>
    </service>
  </services>
</container>
```
 
但是，另一方面，将服务容器定义为普通的 PHP 类会为您提供最好的性能，正如本系列第二篇文章中所见：

```php
<?php
class Container extends sfServiceContainer
{
  static protected $shared = array();

  protected function getMailTransportService()
  {
    return new Zend_Mail_Transport_Smtp('smtp.gmail.com', array(
      'auth'     => 'login',
      'username' => $this['mailer.username'],
      'password' => $this['mailer.password'],
      'ssl'      => 'ssl',
      'port'     => 465,
    ));
  }

  protected function getMailerService()
  {
    if (isset(self::$shared['mailer']))
    {
      return self::$shared['mailer'];
    }

    $class = $this['mailer.class'];

    $mailer = new $class();
    $mailer->setDefaultTransport($this->getMailTransportService());

    return self::$shared['mailer'] = $mailer;
  }
}
```
 
上面的代码尽可能地提供了灵活性，这要归功于配置变量，并且保证了较好的性能。
 
有没有鱼和熊掌可兼得的方法呢？很简单。Symfony 依赖注入组件提供了另一个内置的「转存器」：一个 PHP 转存器。这个转存器可以将任何服务容器转换为普通的 PHP 代码。没错，它可以自动生成类似手动编写的服务容器创建代码。
 
让我们再次使用我们的 Zend_Mail 例子，为了简洁起见，让我们使用前一篇文章中创建的 XML 配置文件：

```php
$sc = new sfServiceContainerBuilder();

$loader = new sfServiceContainerLoaderFileXml($sc);
$loader->load('/somewhere/container.xml');

$dumper = new sfServiceContainerDumperPhp($sc);

$code = $dumper->dump(array('class' => 'Container'));

file_put_contents('/somewhere/container.php', $code);
```
 
类似其它转存器一样，sfServiceContainerDumperPhp 类将容器作为其构造函数的第一个参数。该 dump() 方法接受一组选项，其中一个是要生成的类的名称。
 
这里是生成的代码：

```php
class Container extends sfServiceContainer
{
  protected $shared = array();

  public function __construct()
  {
    parent::__construct($this->getDefaultParameters());
  }

  protected function getMailTransportService()
  {
    $instance = new Zend_Mail_Transport_Smtp('smtp.gmail.com', array(
      'auth' => 'login',
      'username' => $this->getParameter('mailer.username'),
      'password' => $this->getParameter('mailer.password'),
      'ssl' => 'ssl',
      'port' => 465
    ));

    return $instance;
  }

  protected function getMailerService()
  {
    if (isset($this->shared['mailer'])) return $this->shared['mailer'];

    $class = $this->getParameter('mailer.class');
    $instance = new $class();
    $instance->setDefaultTransport($this->getMailTransportService());

    return $this->shared['mailer'] = $instance;
  }

  protected function getDefaultParameters()
  {
    return array (
      'mailer.username' => 'foo',
      'mailer.password' => 'bar',
      'mailer.class' => 'Zend_Mail',
    );
  }
}
```
 
如果仔细查看「转存器」生成的代码，您会发现代码与我们手写的代码非常相似。
 
生成的代码不会使用快捷方式表示法来访问参数和服务以尽可能快。
 
通过使用 **`sfServiceContainerDumperPhp`**  ，您可以获得两全其美的效果：XML 或 YAML 格式的灵活性来描述和配置您的服务，以及自动生成的性能更优的 PHP 文件。
 
当然，由于项目对于不同的环境几乎总是不同的设置，因此您可以根据环境或调试设置生成不同的容器类。下面是一小段 PHP 代码，演示了如何为第一个请求动态构建容器，并在不处于调试模式时在后续请求中使用缓存：

```php
$name = 'Project'.md5($appDir.$isDebug.$environment).'ServiceContainer';
$file = sys_get_temp_dir().'/'.$name.'.php';

if (!$isDebug && file_exists($file))
{
  require_once $file;
  $sc = new $name();
}
else
{
  // build the service container dynamically
  $sc = new sfServiceContainerBuilder();
  $loader = new sfServiceContainerLoaderFileXml($sc);
  $loader->load('/somewhere/container.xml');

  if (!$isDebug)
  {
    $dumper = new sfServiceContainerDumperPhp($sc);

    file_put_contents($file, $dumper->dump(array('class' => $name));
  }
}
```
 
至此有关 Symfony 2依赖注入容器的介绍就差不多完成了。
 
在结束本系列之前，我还想向您介绍「转存器」的另一个重要功能。「转存器」可以做很多不同的事情，为了演示组件如何完成代码解耦，我实现了 「Graphviz 转存器」。它是做什么的？帮助您可视化您的服务及其依赖关系。
 
首先，让我们看看如何在我们的示例容器上使用它：

```php
$dumper = new sfServiceContainerDumperGraphviz($sc);
file_put_contents('/somewhere/container.dot', $dumper->dump());
```
 
Graphviz 转存器为容器生成一个dot 文件：

```
digraph sc {
  ratio="compress"
  node [fontsize="11" fontname="Myriad" shape="record"];
  edge [fontsize="9" fontname="Myriad" color="grey" arrowhead="open" arrowsize="0.5"];

  node_service_container [label="service_container\nsfServiceContainerBuilder\n", shape=record, fillcolor="#9999ff", style="filled"];
  node_mail_transport [label="mail.transport\nZend_Mail_Transport_Smtp\n", shape=record, fillcolor="#eeeeee", style="dotted"];
  node_mailer [label="mailer\nZend_Mail\n", shape=record, fillcolor="#eeeeee", style="filled"];
  node_mailer -> node_mail_transport [label="setDefaultTransport()" style="dashed"];
}
```
 
该文件可以通过使用 [dot 程序][8] 转换为图片：

```
$ dot -Tpng /somewhere/container.dot > /somewhere/container.png
```
 
![][0]
 
对于这个简单的例子，可视化没有真正的附加价值，但只要你开始有不止一些的服务，就会变得非常有用。
 
Graphviz 转存器的 dump() 方法需要很多不同的选项来调整图形的输出。查看源代码以发现它们中的每一个的默认值：
 
graph：整个图形的默认选项 node：节点的默认选项 edge：边缘的默认选项 node.instance：由对象实例直接定义的服务的默认选项 node.definition：通过服务定义实例定义的服务的默认选项 node.missing：缺失服务的默认选项
 
下图是为即将发布的 Symfony 组件生成的图片：
 
![][1]
 
这就是依赖注入这个系列的全部内容。我希望您能够有所收获。我也希望你能很快尝试 Symfony 2 服务容器组件并给我反馈你的使用情况。另外，如果您为某些现有的开源库创建「功能」，请考虑与该社区分享它们。您也可以将您的功能分享给我，我会将它们放在容器组件的以便于重用。
 
原文： [http://fabien.potencier.org/symfony-service-container-the-need-for-speed.html][9]


[2]: http://blog.phpzendo.com/?p=313
[3]: http://blog.phpzendo.com/?p=318
[4]: http://blog.phpzendo.com/?p=321
[5]: http://blog.phpzendo.com/?p=334
[6]: http://blog.phpzendo.com/?p=338
[7]: http://blog.phpzendo.com/?p=342
[8]: http://graphviz.org/
[9]: http://fabien.potencier.org/symfony-service-container-the-need-for-speed.html
[0]: ./aAfqA3n.png
[1]: ./BJraAnj.png
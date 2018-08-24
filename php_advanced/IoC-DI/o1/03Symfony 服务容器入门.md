## Symfony 服务容器入门

来源：[http://blog.phpzendo.com/?p=321](http://blog.phpzendo.com/?p=321)

时间 2018-05-04 22:14:25


本文是依赖注入（Depeendency Injection）系列教程的第 3 篇文章，本系列教程主要讲解如何使用 PHP 实现一个轻量级服务容器，教程包括：


* [第 1 篇：什么是依赖注入？][0]
    
* [第 2 篇：是否需要使用依赖注入容器？][1]
    
* [第 3 篇：Symfony 服务容器入门][2]
    
* [@TODO 第 4 篇：Symfony 服务容器：使用建造者创建服务][3]
    
* [@TODO 第 5 篇：Symfony 服务容器：使用 XML 或 YAML 文件描述服务][4]
    
* [@TODO 第 6 篇：性能优化][5]
    
  


## 术语


* **`Depeendency Injection`** 译作 **`依赖注入`**     
* **`Depeendency Injection Container`** 译作 **`依赖注入容器`**     
* **`Container`** 译作 **`容器`**     
* **`Service Container`** 译作 **`服务容器`**     
* **`Session`** 译作 **`会话`**     
* **`Object-Oriented`** 译作 **`面向对象`**     
* **`mock`** 译作 **`模拟`**     
* **`anti-patterns`** 译作 **`反模式`**     
* **`hardcoded`** 译作 **`硬编码`**     
  

从本系列的开篇到现在我们基本还是围绕「依赖注入」的基本概念展开的。前两篇入门的文章对于理解本文及后续教程至关重要。现在，是时候该去探索 Symfony 2 服务容器是如何实现这个主题了。

Symfony 中的「依赖注入容器」定义的类名为「sfServiceContainer」。这是一个非常轻量级的类,实现了 [上一篇]() 文章中讲解到的基本功能。

Symfony 服务容器可以到官方 Svn 版本库中获得：    [http://svn.symfony-project.com/components/dependency_injection/trunk/。注意][6]
， Symfony 组件依旧保持更新，这也意味着它的实现可能与本文有所出入。（译注： @todo）

在 Symfony 中，任何服务的实例都有容器管理。前一篇文章中提到的 **`Zend_Mail`** 实例中，就需要使用到两个服务： **`mailer`** 服务和 **`mail_transport`** 服务。

```php
<?php

class Container
{
    static protected $sharged = array();

    protected $parameters = array();

    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    public function getMailTransport()
    {
        return new Zend_Mail_Transport_Smtp('smpt.gmail.com', array(
            'auth'     => 'login',
            'username' => $this->parameters['mailer.username'],
            'password' => $this->parameters['mailer.password'],
            'ssl'      => 'ssl',
            'port'     => 465,
        ));
    }

    public function getMailer()
    {
        if (isset(self::$shared['mailer'])) {
            return self::$shared['mailer'];
        }

        $class = $this->parameters['mailer.class'];

        $mailer = new $class();
        $mailer->setDefaultTransport($this->getMailTransport());

        return self::$shared['mailer'] = $mailer;
    }
}
```

让容器类 **`Container`** 继承 **`sfServiceContainer`** 类的话，可以有效精简代码：

```php
<?php

class Container extends sfServiceContainer
{
    static protected $shared = array();

    protected function getMailTransportService()
    {
        return new Zend_Mail_Transport_Smtp('smpt.gmail.com', array(
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

似乎与之前相差无几，但通过继承 **`spServiceProvider`** 的容器类拥有更多功能并且代码更整洁。这里列几点主要的异同点：


* 定义的方法名加上了 **`Service`** 后缀名。依据惯例优先原则，一个服务方法的定义由 **`get`** 前缀和 **`Service`** 缀名共同组成。每个服务同时定义唯一的标识符，标识符命名规则为去除前后缀的方法名并且采用「下划线命名法」命名。比如我们在容器中定义一个 **`getMailTransportServer()`** 方法，容器同时会定义一个名为 **`mail_transport`** 的服务标识符。    
* 所有定义的方法改为 **`protected`** 可见范围修饰符。稍后会讲解如何从容器获取相关服务。    
* 容器可以像数组一样直接获取参数值（$this['mailer.class']）。
  

一个服务标识符必须唯一，并且仅可以包含字母、数字、下划线和 **`.（英文点号）`** 。 **`.`** 号在容器内的功能类似于「命名空间」（如 **`mail.mailer`** 和 **`mail.transport`** 实例）。

接下来是如何使用新的容器类：

```php
<?php
require_once 'PATH/TO/sf/lib/sfServiceContainerAutoloader.php';
sfServiceContainerAutoloader::register();

$sc = new Container(array(
  'mailer.username' => 'foo',
  'mailer.password' => 'bar',
  'mailer.class'    => 'Zend_Mail',
));

$mailer = $sc->mailer;
```

现在，由于我们继承 **`spServiceContainer`** 容器类，我们可以使用更为整洁的接口功能：


* 服务可以有统一的接口访问：
  

```php
<?php

if ($sc->hasService('mailer')) {
    $mailer = $sc->getService('mailer'); 
}

$sc->setService('mailer', $mailer);
```


* 或者，直接通过类的成员变量获取服务：
  

```php
<?php
if (isset($sc->mailer)) {
    $mailer = $sc->mailer;
}

$sc->mailer = $mailer;
```


* 参数名也能通过统一的接口访问：
  

```php
<?php
if (!$sc->hasParameter('mailer_class')) {
    $sc->setParameter('mailer_class', 'Zend_Mail');
}

echo $sc->getParameter('mailer_class');

// 重写容器所有参数
$sc->setParameters($parameters);

// 向容器添加参数
$sc->addParameters($parameters);
```


* 或者，直接通过类的成员变量已类似数组的方式获取：
  

```php
<?ph
if (!isset($sc['mailer.class'])) {
    $sc['mailer.class'] = 'Zend_Mail';
}

$mailerClass = $sc['mailer.class'];
```


* 还可以迭代获取容器内所有服务：
  

```php
<?php
foreach ($sc as $id => $service)
{
    echo sprintf("Service %s is an instance of %s.\n", $id, get_class($service));
}
```

当项目容器需要管理不太多的服务时，通过继承 **`spServiceContainer`** 类是非常明智的选择；即使，这样依旧需要处理大量的基础工作或直接从已有项目中复制代码过来。而当系统引入大量的服务时，我们就需要使用更好的方法来组织和管理这些服务。

这就是为什么多数时候我们并不会直接使用 **`spServiceContainer`** 类的原因。但是我们花这个时间来讲解 **`spServiceContainer`** 类的用法的理由是，它是 Symfony 依赖注入容器实现的基石。

下一篇文章，我们将来看看可以简化服务定义过程的 **`sfServiceContainerBuilder`** 类。

原文：    [http://fabien.potencier.org/introduction-to-the-symfony-service-container.html][7]


[0]: http://blog.phpzendo.com/?p=313
[1]: http://blog.phpzendo.com/?p=318
[2]: http://blog.phpzendo.com/?p=321
[3]: http://fabien.potencier.org/symfony-service-container-using-a-builder-to-create-services.html
[4]: http://fabien.potencier.org/symfony-service-container-using-xml-or-yaml-to-describe-services.html
[5]: http://fabien.potencier.org/symfony-service-container-the-need-for-speed.html
[6]: http://svn.symfony-project.com/components/dependency_injection/trunk/%E3%80%82%E6%B3%A8%E6%84%8F
[7]: http://fabien.potencier.org/introduction-to-the-symfony-service-container.html
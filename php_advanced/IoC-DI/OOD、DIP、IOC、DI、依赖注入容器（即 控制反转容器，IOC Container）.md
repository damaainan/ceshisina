## OOD、DIP、IOC、DI、依赖注入容器（即 控制反转容器，IOC Container）

来源：[https://segmentfault.com/a/1190000010978964](https://segmentfault.com/a/1190000010978964)


## 1. 名词介绍



* OOD，面向对象设计


* DIP，依赖倒置（软件设计原则）


* IOC，控制反转（软件设计模式）


* DI，依赖注入


* IOC Container，控制反转容器，也是依赖注入容器



## 2. 组成部分



* 服务清单（功能清单，service list）


* 服务（高层类，service ，对外提供服务）


* 服务提供者（底层类，service provider ，实际提供服务的对象）



## 2. 依赖倒置原则(DIP)
### 2.0 介绍
 **`依赖倒置原则，它转换了依赖，高层模块不依赖于低层模块的实现，而低层模块依赖于高层模块定义的接口`** 

[详细介绍请点我][0]
### 2.1 场景描述

提供一个计算机储存的服务。需要根据不同的用户需求，使用不同的存储设备。
### 2.2 没有遵循依赖倒置原则的例子
#### 2.2.1 定义好服务提供者（实际提供服务）

```php
// 定义一个 硬盘存储类 (服务提供者)
class HardDiskStorage {
    public function saveToHardDisk(){
        
    }
    
    public function readFromHardDisk(){
        
    }
}

// 定义一个 U盘存储类（服务提供者）
class UStorage {
    public function saveToU(){
        
    }
    
    public function readFromU(){
        
    }
}
```
#### 2.2.2 定义 服务（对外提供服务的对象）

```php
/**
 * 定义一个 ComputerStorage 类（存储服务）
 */ 

// 第一种：使用硬盘作为提供实际服务的对象
class ComputerStorage {
    protected $_storage = null;
    
    function __construct(){
        $this->_storage = new HardDiskStorage();
    }
    
    public function save(){
        $this->_storage->saveToHardDisk();
    }
    
    public function read(){
        $this->_storage->readFromHardDisk();
    }
}

// 第二种：使用 U 盘作为提供实际服务的对象
class ComputerStorage {
    protected $_storage = null;
    
    function __construct(){
        $this->_storage = new UStorage();
    }
    
    public function save(){
        $this->_storage->saveToU();
    }
    
    public function read(){
        $this->_storage->readFromU();
    }
}

// 读取
$cs = new ComputerStorage();
$cs->read();
```
#### 2.2.3 代码分析

根据上面的代码，当切换服务提供者时，服务类的代码需要做较多的改动。服务（`ComputerStorage`）本省作为一个高层类，对外提供访问，却受制于提供具体服务的服务提供者（`HardDiskStorage`、`UStorage`）定义的实现（`saveToHardDisk`、`saveToU`、`readFromHardDisk`、`readFromU`）， **`高层模块依赖底层模块实现，违背了依赖倒置原则。`** 
### 2.3 遵循依赖倒置原则的例子
#### 2.3.1 场景

同`2.1`介绍中场景。
#### 2.3.2 定义服务清单（高层模块定义接口）

```php
interface ServiceList {
    public function save();
    public function read();
}
```
#### 2.3.3 定义服务提供者

```php
// 硬盘
class HardDiskStorage implements ServiceList {
    public function save(){
        
    }
    
    public function read(){
        
    }
}

// U 盘
class UStorage implements ServiceList {
    public function save(){
        
    }
    
    public function read(){
        
    }
}
```
#### 2.3.4 定义服务

```php
class ComputerStorage {
    protected $_storage = null;
    
    function __construct(){
        $this->_storage = new HardDiskStorage();        
    }
    
    public function save(){
        $this->_storage->save();
    }
    
    public function read(){
        $this->_storage->read();
    }
}

$cs = new ComputerStorage();
$cs->read();
```
#### 2.3.5 代码分析

上述代码中，事先定义了好了服务清单(接口，`ServiceList`)，然后服务提供者实现这些接口（`HardDiskStorage`、`UStorage`），服务（`ComputerStorage`）只需要切换服务提供者即可（`HardDiskStorage`、`UStorage`），完全无需理会他们的实现（`readFromHardDisk`、`readFromU`...等）。 **`高层模块不依赖于底层模块定义的实现，遵循了依赖倒置原则`** 
## 3. 控制反转（IOC） + 依赖注入（DI）
### 3.0 介绍
 **`控制反转（IoC），它为相互依赖的组件提供抽象，将依赖（低层模块）对象的获得交给第三方（系统）来控制，即依赖对象不在被依赖模块的类中直接通过new来获取`** 

[详细介绍请点我][1]
### 3.1 场景

同`2`场景
### 3.2 没有实现控制反转的例子
`2`中的例子就是没有实现控制反转的例子。`2`中`ComputerStorage`获取依赖（`HardDiskStorage`或`UStorage`）的途径都是在`contruct`构造函数中获取的，即 类内部实例化依赖获取。
### 3.3 实现控制反转的例子

以下代码是根据`2`中的代码做了些许的调整。

```php
class ComputerStorage {
    protected $_storage = null;
    
    /**
     * 内部只获取依赖的实例
     */
    public function setStorage($storage){
        $this->_storage = $storage;
    }

    public function save(){
        $this->_storage->save();
    }
    
    public function read(){
        $this->_storage->read();
    }
}

// 外部实例化依赖
$hardDiskStorage = new HardDiskStorage();

$cs = new ComputerStorage();
// 注入依赖
$cs->setStorage($hardDiskStorage);
```
## 4. 依赖注入容器（IOC 容器）
### 4.0 场景

同`2`场景。
### 4.1 使用 IOC容器

```php
class Container {
    // 注册表
    protected static $_registry = null;
    
    // 保存到注册表
    public static function set($classname , Callable $create){
        self::$_registry[$classname] = $create;
    }
    
    // 获取注册表对应类的实例
    public static function get($key){
        call_user_func(self::$_registry[$key]);
    }
}

class ComputerStorage {
    protected $_storage = null;
    
    function __construct(){
        $this->_storage = Container::get('HardDiskStorage');
    }
    
    public function read(){
        $this->_storage->read();
    }
    
    public function save(){
        $this->_storage->save();
    }
}

/**
 * 注册依赖
 */
Container::set('HardDiskStorage' , function(){
    return new HardDiskStorage();
});

Container::set('UStorage' , function(){
    return new UStorage();
});

// 测试
$cs = new ComputerStorage();

$cs->read();

```

[0]: http://www.cnblogs.com/liuhaorain/p/3747470.html#title_2
[1]: http://www.cnblogs.com/liuhaorain/p/3747470.html#title_3
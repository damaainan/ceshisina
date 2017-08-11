# PHP 设计模式系列 —— 状态模式（State）

 Posted on [2016年1月10日][0] by [学院君][1]

### **1、模式定义**

[状态模式][2]（State）又称状态对象模式，主要解决的是当控制一个对象状态转换的条件表达式过于复杂时的情况。状态模式允许一个对象在其内部状态改变的时候改变其行为，把状态的判断逻辑转移到表示不同的一系列类当中，从而把复杂的逻辑判断简单化。

用一句话来表述，状态模式把所研究的对象的行为包装在不同的状态对象里，每一个状态对象都属于一个抽象状态类的一个子类。状态模式的意图是让一个对象在其内部状态改变的时候，其行为也随之改变。

### **2、UML类图**

![State-Design-Pattern-Uml][3]

### **3、实例代码**

#### **OrderController.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\State;
    
    /**
     * OrderController类
     */
    class OrderController
    {
        /**
         * @param int $id
         */
        public function shipAction($id)
        {
            $order = OrderFactory::getOrder($id);
            try {
                $order->shipOrder();
            } catch (Exception $e) {
                //处理错误!
            }
            // 发送响应到浏览器
        }
    
        /**
         * @param int $id
         */
        public function completeAction($id)
        {
            $order = OrderFactory::getOrder($id);
            try {
                $order->completeOrder();
            } catch (Exception $e) {
                //处理错误!
            }
            // 发送响应到浏览器
        }
    }
```
#### **OrderFactory.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\State;
    
    /**
     * OrderFactory类
     */
    class OrderFactory
    {
        private function __construct()
        {
            throw new \Exception('Can not instance the OrderFactory class!');
        }
    
        /**
         * @param int $id
         *
         * @return CreateOrder|ShippingOrder
         * @throws \Exception
         */
        public static function getOrder($id)
        {
            //从数据库获取订单伪代码
            $order = 'Get Order From Database';
    
            switch ($order['status']) {
                case 'created':
                    return new CreateOrder($order);
                case 'shipping':
                    return new ShippingOrder($order);
                default:
                    throw new \Exception('Order status error!');
                    break;
            }
        }
    }
```
#### **OrderInterface.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\State;
    
    /**
     * OrderInterface接口
     */
    interface OrderInterface
    {
        /**
         * @return mixed
         */
        public function shipOrder();
    
        /**
         * @return mixed
         */
        public function completeOrder();
    }
```
#### **ShippingOrder.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\State;
    
    /**
     * ShippingOrder类
     */
    class ShippingOrder implements OrderInterface
    {
        /**
         * @var array
         */
        private $order;
    
        /**
         * @param array $order
         *
         * @throws \Exception
         */
        public function __construct(array $order)
        {
            if (empty($order)) {
                throw new \Exception('Order can not be empty!');
            }
            $this->order = $order;
        }
    
        /**
         * @return mixed|void
         * @throws \Exception
         */
        public function shipOrder()
        {
            //当订单发货过程中不能对该订单进行发货处理
            throw new \Exception('Can not ship the order which status is shipping!');
        }
    
        /**
         * @return mixed
         */
        public function completeOrder()
        {
            $this->order['status'] = 'completed';
            $this->order['updatedTime'] = time();
    
            // 将订单状态保存到数据库
            return $this->updateOrder($this->order);
        }
    }
```
#### **CreateOrder.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\State;
    
    /**
     * CreateOrder类
     */
    class CreateOrder implements OrderInterface
    {
        /**
         * @var array
         */
        private $order;
    
        /**
         * @param array $order
         *
         * @throws \Exception
         */
        public function __construct(array $order)
        {
            if (empty($order)) {
                throw new \Exception('Order can not be empty!');
            }
            $this->order = $order;
        }
    
        /**
         * @return mixed
         */
        public function shipOrder()
        {
            $this->order['status'] = 'shipping';
            $this->order['updatedTime'] = time();
    
            // 将订单状态保存到数据库
            return $this->updateOrder($this->order);
        }
    
        /**
         * @return mixed|void
         * @throws \Exception
         */
        public function completeOrder()
        {
            // 还未发货的订单不能设置为完成状态
            throw new \Exception('Can not complete the order which status is created!');
        }
    }
```
> 注：由于代码中使用了伪代码，所以这里就不进行测试了。

### **4、总结**

在软件开发过程中，应用程序可能会根据不同的情况作出不同的处理。最直接的解决方案是将这些所有可能发生的情况全都考虑到。然后使用if… ellse语句来做状态判断来进行不同情况的处理。但是对复杂状态的判断就显得“力不从心了”。随着增加新的状态或者修改一个状体（if else(或switch case)语句的增多或者修改）可能会引起很大的修改，而程序的可读性，扩展性也会变得很弱。维护也会很麻烦。那么就要考虑使用状态模式。

状态模式的主要优点在于封装了转换规则，并枚举可能的状态，它将所有与某个状态有关的行为放到一个类中，并且可以方便地增加新的状态，只需要改变对象状态即可改变对象的行为，还可以让多个环境对象共享一个状态对象，从而减少系统中对象的个数；其缺点在于使用状态模式会增加系统类和对象的个数，且状态模式的结构与实现都较为复杂，如果使用不当将导致程序结构和代码的混乱，对于可以切换状态的状态模式不满足“开闭原则”的要求。

[0]: http://laravelacademy.org/post/2971.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e7%8a%b6%e6%80%81%e6%a8%a1%e5%bc%8f
[3]: http://laravelacademy.org/wp-content/uploads/2016/01/State-Design-Pattern-Uml.png
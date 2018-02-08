# [面向对象设计原则之单一职责原则——Single Responsibility Principle](https://www.onmpw.com/tm/xwzj/algorithm_248.html)

发布时间： 2017-12-05 作者： 迹忆 

单一职责原则是面向对象设计中最简单的原则。所谓单一职责，说白了就是一个类只负责一个职能。单一职责原则是这样定义的：

单一职责原则（Single Responsibility Principle）：一个类只负责一个功能领域中的相应职责，并且该功能职责应该完全由这个类封装起来。

在我们的程序设计过程中，一个类的方法不易太多，能完成自己对应的功能即可。如果说，一个类包含了两个功能，那就有可能在我们修改其中一个功能的时候从而影响到另一个功能。

假设我们有一个购物车的实例

```php
    //购物车
    class Cart
    {
        // 添加商品
        public function addItem(){ /* ... */}
    
        // 移除商品
        public function deleteItem(){/* ... */}
    
        // 获取商品
        public function getItem(){/* ... */}
    
        // 设定订单信息
        public function setOrderInfo($info){/* ... */}
    
        // 取得付款信息
        public function getPaymentInfo(){/* ... */}
    
        // 保存订单
        public function saveOrder(){/* ... */}
    
        // 发送订单确认邮件
        public function sendMail(){/* ... */}
    
    }
    
    $cart = new Cart();
    $cart->addItem(); // 添加商品
    $cart->getPaymentInfo(); //获取付款信息
```

我们可以看到，在一个购物车的类中，除了应该有的添加商品、删除商品和获取商品方法之外。还包含了设定订单信息、取得付款信息、保存订单以及发送确认订单邮件这些方法。很明显，这个类显然做了一些不应该它负责的事情。如果我们在其他的地方使用订单的话，我们还得去实例化一个购物车的实例，非常奇怪。所以说，根据单一职责原则，我们需要拆分这个类。把订单的方法拿出去，单独定义一个Order类。

```php
    // 订单
    class Order
    {
    
        // 设定订单信息
    
        public function setOrderInfo($info){/* ... */}
        
        // 取得付款信息
    
        public function getPaymentInfo(){/* ... */}
        
        // 保存订单
    
        public function saveOrder(){/* ... */}
    
        // 发送订单确认邮件
    
        public function sendMail(){/* ... */}
    
    }
    
    // 重构后的购物车
    class Cart
    {
        // 添加商品
        public function addItem(){ /* ... */}   
    
        // 移除商品
        public function deleteItem(){/* ... */} 
    
        // 获取商品
        public function getItem(){/* ... */}
    }
    
    $cart = new Cart();
    $order = new Order();
    $cart->addItem(); // 添加商品
    $order->getPaymentInfo(); //获取付款信息
```

这样一拆分，当我们需要在其他的地方使用订单的时候，只需要实例化一个Order对象，而不是Cart对象。即使我们的购物车中也需要订单，那就直接实例化一个Order对象，用其里面的方法即可。

这样，即使以后需要修改订单的功能，也不会影响到购物车的基本功能的使用。这样的代码以后维护起来也是非常方便的。而且可读性也是非常高的。

总的一句话，单一职责原则其实说白了就是不要越俎代庖，做好自己应该做的事情就好。


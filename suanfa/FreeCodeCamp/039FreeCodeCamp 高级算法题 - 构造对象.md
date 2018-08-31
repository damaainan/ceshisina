## FreeCodeCamp 高级算法题 - 构造对象

来源：[http://singsing.io/blog/fcc/advanced-make-a-person/](http://singsing.io/blog/fcc/advanced-make-a-person/)

时间 2018-02-10 14:50:06



* 不同于其他题目，在这道题中，我们需要写一个构造函数。这个构造函数接收一个字符串参数`firstAndLast`
* 如果`str`为`"Bob Ross"`，则构造出实例的全名为 “Bob Ross”，姓氏为 “Ross”，名字为 “Bob”    

## 解题思路  

* 这道题其实难度很小。如果你熟悉 “构造器” (constructor) 的概念，以及 JavaScript 中的`new`操作符，那代码很快就可以写出来    
* 简单来说，在 JavaScript 中，构造器就是一个函数 (但函数可以不用作构造器)。比如题目中给出了：

```js

var bob = new Person('Bob Ross');

```

* 也就相当于用`new`操作符调用`Person`构造器，并把返回值赋值给变量`bob`，会发生以下三件事：

* 创建一个对象，这个对象的原型继承自`Person.prototype`
* 对象拥有`Person`函数中用`this`定义的属性或方法        
* 如果`Person`函数本身又返回值，那这个返回值就会赋值给`bob`。否则，就把第一步创建的对象赋值给`bob`

* 题目中，第一个测试实例说到，`Object.keys(bob).length`应该返回`6`。这句话的意思是，我们只应该给实例绑定题目中列出的六个方法。至于传入的`firstAndLast`，虽然这就是全名，但我们也不应该用`this`把这个值绑定给实例

* 需要注意的是，如果用`prototype`给构造函数添加属性，那么生成的实例是可以通过属性名来获取到对应的值的。而且，这个属性不会直接出现在实例中，而是藏在`__proto__`属性里面，而`Object.keys`是不会计算`__proto__`原型对象中的属性的。在这道题目中，我们其实不需要去折腾原型链。只要把名字作为局部变量放在构造函数里就可以了    
* 根据题目要求，有时候我们需要返回全名，有时候我们只需要返回姓或名。因此，比较简单的处理方式就是把姓和名放到数组中来保存，如果需要返回全名，直接`join`一下就可以了    
* 为了方便，我们也可以把全名作为字符串赋值给一个局部变量。当然，如果这样做，更新的时候不要忘了同时更新这个变量和数组

## 参考资料  

* [new 操作符][0]

## 代码  

```js

var Person = function(firstAndLast) {
    var nameArr = firstAndLast.split(' ');

    this.getFirstName = function() {
        return nameArr[0];
    };
    this.getLastName = function() {
        return nameArr[1];
    }
    this.getFullName = function() {
        return nameArr.join(' ');
    }

    this.setLastName = function(lastName) {
        nameArr[1] = lastName;
    }
    this.setFirstName = function(firstName) {
        nameArr[0] = firstName;
    }
    this.setFullName = function(fullName) {
        nameArr = fullName.split(' ');
    }
}

var bob = new Person('Bob Ross');

```

[0]: https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Operators/new
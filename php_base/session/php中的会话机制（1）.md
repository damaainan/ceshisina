# [php中的会话机制（1）][0]

* [php][1]

[**phplife**][2] 2014年04月13日发布 



#### ①什么是会话机制：

这个太过抽象，也挺复杂的，只能说一下自己的理解。在b/s架构下，会话机制，其实就是服务器（server）和浏览器（browser）之间的对话的一种方式！这种对话方式，能够使得web站点能够对用户的行为进行追踪，在同一个站点下用户所需的数据实现共享！

#### ②为什么要使用会话机制

归根到底是由于，b/s的访问方式是基于http协议的，而http协议本身又是无状态的，所谓无状态，就是指服务器端无法区分发起请求的是否是同一个人（有兴趣的同学可以自己好好研究一下http的无状态性）！每次请求都会被当做独立的请求，并不能将两次访问联系到一起！

#### ③核心设计思想

核心设计思想：**允许服务器对同一个客户端的的连续请求进行跟踪，对同一个访问者的请求数据，在多个页面之间实现共享！**

#### ④php中实现会话机制的方法：

1）在两个页面（较少页面之间）通过`$_GET`或者`$_POST`数组之间实现数据的共享！  
2）使用`cookie`将用户的信息存放在客户端的计算机中，用于保存并不重要的数据  
3）通过`session`将用户的信息保存在服务器中

通过`$_GET`和`$_POST`方式获得数据较为简单，这里就不再介绍！

- - -

### cookie会话机制实现的注意点

1）当我们通过`setCookie()`函数来新增或者改变cookie中的值的时候，`setCookie()`函数前面不能够有任何实际的输出，即使是空格也不可以！  
这是因为`setCookie（）`函数最终是改变http响应头信息**（**我们有理由相信setCookie（）方法，底层就是通过header（）方法进行的设置的头信息**）**，我们都应该知道在header函数前面是不能够有任何实际的输出的（除非是开启了ob缓存）！

2）在cookie中是只能够保存字符串的，但是，如果我们想将一个数组变量保存到cookie中，在不进行序列化的情况下，其实也是可以办到的，代码如下：

```php
    <?php
    $expires = time()+3600;
    setcookie('user["name"]["xing"]','liang');
    setcookie('user["name"]["ming"]', 'bo');
    
    setcookie('user["age"]', '23', $expires);
    setcookie('user["addr"]','吉林', $expires);
    ?>
```

```php
    <?php
    $name = $_COOKIE['name'];
    var_dump($name);
    ?>
```


    > 得到的结果如下：
    >     array(3) {
    >       [""name""]=>
    >           array(2) {
    >             [""xing""]=>
    >             string(5) "liang"
    >             [""ming""]=>
    >             string(2) "bo"
    >           }
    >       [""age""]=>
    >       string(2) "23"
    >       [""addr""]=>
    >       string(6) "吉林"
    >     }
    

3）cookie的$path参数，只有在指定的路径下的网页才可以获取cookie中的值！demo如下：

```php
    <?php
    if(!$_COOKIE['name']) {
    $expires = time()+3600;
    setcookie('name','liangbo', $expires, '/talkphp/secondtalk/');
    }
```

该页面所处的路径**"/"**,也就是网站的根目录！

接受的代码如下：

```php
    <?php
        header('Content-type:text/html;charset=utf-8');
        $name = $_COOKIE['user'];
        var_dump($name);
```

改代码文件所在的路径如下：/talkphp/secondtalk/  
执行结果如下：string(7) "liangbo"

同样的接受代码：但是所处的路径不同，是在根目录**“/”**下,  
得到的结果如下：**null**

4）cookie的跨域问题：  
个人认为跨域问题，主要是值存在同一个网站下，有多个二级域名，在多个二级域名下cookie数据的共享问题！  
在cookie中，如果设置的domain参数是一级域名的话，那么cookie中的数据在各个二级域名之间是都可用的！demo如下：

```php
    <?php
    if(!$_COOKIE['name']) {
    $expires = time()+3600;
    setcookie('name','liangbo', $expires, '/talkphp/secondtalk/', '.test.com');
    }
```

- - -

```php
    <?php
        $name = $_COOKIE['name'];
        var_dump($name);
    ?>
```

该代码所在的网站域名是：php.test.com 页面路径是：/talkphp/secondtalk/getcookie.php  
运行结果如下：**string(7) "liangbo"**

同样的代码，该代码所在的域名是：jquery.test.com 页面所在的路径是：/talkphp/secondtalk/getcookie.php  
运行结果如下：**string(7) "liangbo"**  
**可见，如果domain参数中设置的是一级域名的话，那么在各个二级域名之间$_COOKIE中的数据是可以共享的**  
**在一级域名中**.test.com**,中test前的"."其实是可以省略的，但是加上的话，浏览器的兼容会更好！**

我们来看另外一种情况：

```php
    <?php
    if(!$_COOKIE['name']) {
    $expires = time()+3600;
    setcookie('name','liangbo', $expires, '/talkphp/secondtalk/','php.test.com');
    }
```

这里，我们将domain设置为了二级域名php.test.com

```php
    <?php
        $name = $_COOKIE['name'];
        var_dump($name);
```

该代码所在的网站域名是：php.test.com 页面路径是：/talkphp/secondtalk/getcookie.php  
运行结果如下：**string(7) "liangbo"**

同样的代码，该代码所在的域名是：jquery.test.com 页面所在的路径是：/talkphp/secondtalk/getcookie.php  
运行的结果如下：**null**  
**可见，如果设置的domain参数是二级域名的话，那么cookie中的数据只能够在该二级域名下面使用！**

[0]: /a/1190000000467467
[1]: /t/php/blogs
[2]: /u/phplife


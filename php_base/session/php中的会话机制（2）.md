# php中的会话机制（2）

 时间 2014-04-14 15:00:39  [SegmentFault][0]

_原文_[http://blog.segmentfault.com/phplife/1190000000468220][1]

 主题 [PHP][2]

### session 会话机制：

##### 1）如果是基于cookie的函数，在session_start（），调用之前是不能够有任何实际的输出的，即使是空格或者是空行！

因为`session_start()`函数调用的时候，其实是通过`setCookie()`函数向cookie中设置了`PHPSESSID`这个key，对应的value是一个随机的、   
唯一的32位字符串！ 而`setCookie`前面是不可以有任何实际的输出的！ 

注意：这里的`PHPSESSID`名字是在`php.ini`文件中进行的配置！配置如下图所示：

![sessionname][3]

##### 2）这里我们需要明白session_start()函数的作用究竟是什么：

**①如果session机制是基于cookie的，那么当脚本第一次运行的时候**  
A、 在客户端上`session_start()`函数会通过`setCookie()`函数向Cookie中保留一个key，默认情况下Key的名字是`PHPSESSID`，对应的值是一个32位的、唯一的、随机的字符串！   
B、 在服务器端，会产生一个以`PHPSESSID`的value值为名字的文件！其中保留的是session中的数据！同时，在脚本中创建`$_SESSION`超全局数组，将数据定义到`$_SESSION`数组中！ 

**②当脚本第二次，及以后运行的时候**  
A、 浏览器端会自动携带`COOKI`E中的`PHPSESSID`对应的`value`值，将数据送至服务器端！   
B、在服务器端，一旦开启`sessioin_start()`的时候，会根据客户端提供的`sessionid`去寻找对应的`session`文件，将session中的变量读取出来！在脚本中创建`$_SESSION`超全局数组，将数据定义到`$_SESSION`数组中！ 

注意： `$_SESSION`是超全局定义数组，那么它里面的变量在哪里都是可以直接使用的（函数内也是可以的）！   
这个数组是只有在调用`session_start()`函数 [确切的说，是开启`session`机制之后]才会存在`$_SESION`数组！ 

- - -

接下来，我们来看一点代码！当我们 **第一次运行这个脚本** ，脚本代码如下 脚本A中的代码： 

```php
     <?php
     session_start();
     $_SESSION['name']='maweibin';
     ?>
```

在 **服务器端** 保存session文件夹中的的文件入下： 

![请输入图片描述][4]

代码中的保值至如下：

![请输入图片描述][5]

在客户端保存了一个cookie文件，内容如下：

![请输入图片描述][6]

通过观察我们可以发现：`PHPSESSIONID`对应的value值和服务器端session文件的文件名是一致的！

当 **第二次及其以后运行这个脚本的时候** ，在服务器端，并没有什么变化 

在客户端，发起http请求的时候，我们可以看到：

![请输入图片描述][7]

请求的时候会字段的将`cookie`中的`PHPSESSID`带到服务器端！服务器端，会通过提供的`sessionid`值，将`session`文件中的数据读取出来!

##### 3）同cookie不同的是，session中的数据不仅可以存放字符串，还可以存放数组和对象！

```php
    <?php
    session_start();
    $name = array('name'=>'jay', 'age'=>'23', 'addr'=>'吉林省');
    $_SESSION['voice'] = $name;
    ?>
    <?php
    session_start();
    var_dump($_SESSION['voice']);
    ?>
```

运行的结果如下：

    array(3) { ["name"]=> string(3) "jay" ["age"]=> string(2) "23" ["addr"]=> string(9) "吉林省" }    

注意：这里我们有必要讨论一下，为什么cookie中不能存放数组之类的变量，而只能够存放字符串！我们来看几段代码：

```php
    <?php
    session_start();
    $name = array('name'=>'jay', 'age'=>'23', 'addr'=>'吉林省');
    $_SESSION['voice'] = $name;
    ?>
```

在session文件中存储的数据如下：

    name|s:8:"maweibin";voice|a:3:{s:4:"name";s:3:"jay";s:3:"age";s:2:"23";s:4:"addr";s:9:"吉林省";}

```php    
    <?php
    $expires = time()+3600;
    $name = array('name'=>'jay', 'age'=>'23', 'addr'=>'吉林省');
    setcookie('name',$name, $expires, '/talkphp/secondtalk/', 'php.test.com');
    ?>
```

此时，我们调用一下这段脚本：
```php
    <?php
    $name = $_COOKIE['name'];
    var_dump($name);
    //得到的结果是null，由此可见这样的写法是不支持的！
    ?>
```
我们再看一段代码：
```php
    <?php
    $expires = time()+3600;
    setcookie('person["name"]','liangbo' , $expires, '/talkphp/secondtalk/', 'php.test.com');
    setcookie('person["age"]','23' , $expires, '/talkphp/secondtalk/','php.test.com');
    ?>
```

运行结果如下：

    array(2) { [""name""]=> string(7) "liangbo" [""age""]=> string(2) "23" }   

虽然也取到了数据，但是，cookie中的数据却和session中的数据并不相同！但是，我们来看一下cookie文件中存储的数据   
```
person["name"]   
liangbo   
php.test.com/talkphp/secondtalk/   
0   
976582400   
30365600   
3634030379   
30365591 
```

-  -  -

```
person["age"]   
23   
php.test.com/talkphp/secondtalk/   
0   
976582400   
30365600   
3634060380   
30365591 
```
 由此可见，cookie之所以不能够保存数组或者是对象等变量，是因为cookie本身并没有序列化，和反序列化这一步！这也提示我们，如果，我们手动将将变量进行了序列化和反序列化，就可以用cookie来存储变量了！

##### 4）删除session数组需要注意的地方：

我们可以使用`unset（）`方法干掉`$_SESSION[‘key’]` ,这样可以单独的干掉一个值，此时`$_SESSION`数组依然存在！   
但是，如果我们需要清空session中的全部数据的时候，是不能够直接`unset($_SESSION)`。这样在当前脚本周期之内，超全局定义数组`$_SESSION` 就不存在了！我们也就没有办法操作session中的数据了！ 

我们来看一段代码：
```php
    <?php
    var_dump($_SESSION);
    session_start();
    var_dump($_SESSION);
    unset($_SESSION);
    session_start();
    var_dump($_SESSION);
    ?>
```

 运行如下：
    
    NULL     array(0) { }    NULL
    

从上面的代码中，我们至少可以得出两个结论：   
① 开启session机制前，`$_SESSION`数组是不存在的!   
② `$_SESSION` 数组在脚本周期内，一旦被干掉，就不会再产生！即使重新开始session机制之后，该数组也并没有出现！ 

因此，我们想要清空`$_SESSION` 中的数据的话，就需要使用`$_SESSION = array()`的形式，这样能够在脚本周期之内，保证`$_SESSION`数组的存在！

[0]: /sites/3uEjYv
[1]: http://blog.segmentfault.com/phplife/1190000000468220
[2]: /topics/11120000
[3]: ../img/nEjaui.png
[4]: ../img/6reaAnA.png
[5]: ../img/yEFjae.png
[6]: ../img/viiuiy.png
[7]: ../img/aY7Z7z.png
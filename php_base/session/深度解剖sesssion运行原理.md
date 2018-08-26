# 深度解剖sesssion运行原理

 时间 2017-12-17 22:48:00  

原文[http://www.cnblogs.com/st-leslie/p/8016951.html][1]


已经大半年没有更新博客了，一方面有比博客更重要的事情要做，另外一方面也没有时间来整理知识，所以希望在接下来的日子里面能够多多的写博客来与大家交流

 什么是session

session的官方定义是：Session:在计算机中，尤其是在网络应用中，称为“会话控制”。Session 对象存储特定用户会话所需的属性及配置信息。

说白了session就是一种可以维持服务器端的数据存储技术。session主要有以下的这些特点：

1. session保存的位置是在服务器端

2. session一般来说是要配合cookie使用，如果是浏览器禁用了cookie功能，也就只能够使用URL重写来实现session存储的功能

3. 单纯的使用session来维持用户状态的话，那么当同时登录的用户数量较多的时候，或者存在较多的数量的session会导致查询慢的问题

#### 本质上：session技术就是一种基于后端有别于数据库的临时存储数据的技术

 为什么要有session

主要的一个原因就是HTTP的无状态性

因为HTTP的无状态性，所以我们没有办法在HTTP发送请求的时候知道当前用户的状态，也就是比如说，当前是哪个用户的之类的这种信息，所以这个时候我们需要session来标识当前的状态

 seesion的工作原理

接下来，通过一个模拟用户登录的流程图来初步理解session的原理，假设这个时候用户执行登录操作，具体的session工作流程如下:

![][4]

整个流程大概分成这样的几步：

1. 第一步将本地的cookie中的session标识和用户名，密码带到后台中

2. 第二步后台检测有没有对应的session标识，我们以php为例，那么就是检测有没有接收到对应的PHPSESSID

3. 没有的话直接生成一个新的session。有的话，检测对应的文件是否存在并且有效

3. 失效的话，我们需要清除session然后生成新的session。不失效，使用当前的session

看到这里你可能对session的工作原理有一个初步的理解

session的原理图如下：

 ![][5]

 session的常见配置

我们这里以PHP为例来讲解一下关于session的配置

首先我们要在PHP的安装目录下面找到php.ini文件，这个文件主要的作用是对PHP进行一些配置，具体以后涉及到再详讲。

 1. 设置session存放在cookie中中标识的字段名，php中默认为 PHPSESSID 

 对应的设置为： session.name = PHPSESSID 

 2. 如果客户端禁用了cookie，可以通过设置  session.use_trans_sid来使标识的交互方式从cookie变为url传递

对应的设置为： session.use_trans_sid = 0

3. 设置session的保存位置

 对应的设置是  session.save_path="D:\phpStudy\PHPTutorial\tmp\tmp"

 PHP中session实战

首先我们需要安装wamp或者是phpstudy,具体方式自行百度

为了方便观察session文件的变化，我们需要找到session的保存路径（在php.ini中找到session.save_path），如下：

 ![][6]

然后找到所指向的目录，注意一般来说session是使用files的形式来保存的，但是我们也可以根据自己的实际情况进行修改。我们可以在php.ini文件中进行修改和查看。

 ![][7]

使用session的第一步，我们要打开session，使用session_start(),然后我们给创建的session添加一个变量，我们假设为demo1,值为default ,代码如下：

```php
    <?php
    /**
     * Created by PhpStorm.
     * Date: 2017/12/16
     */
    session_start();// 打开session
    $_SESSION["demo1"] = "default";
    ?>
```

执行效果如下：

 ![][8]

打开对应的文件，里面的内容如下：

![][9]

s:7 表示的是类型为string类型，长度为7个长度的字符串

如果我们对session中的内容进行重新编辑的话，效果如下：

 ![][10]

我们观察最近一条的修改日期，我们可以发现就是日期发生了变化，但是文件名没有变化，也就是说，修改session中的内容不会导致文件被新建，而是执行对文件的重新写入操作

#### session的销毁

销毁session一般有两种方式，unset和session_destroy，我们先来说说第一种

代码如下：

```php
    <?php
    /**
     * Created by PhpStorm.
     * Date: 2017/12/16
     */
    session_start();// 打开session
    $_SESSION["demo1"] = "default_1";
    //session的销毁
    unset($_SESSION);
    ?>
```

这一个相当于没有删除session文件，但是使得即使有对应的PHPSESSID也无法获取到相应的session

session_destroy()相对来说比较彻底，直接删除对应的session文件

```php
    <?php
    /**
     * Created by PhpStorm.
     * Date: 2017/12/16
     */
    session_start();// 打开session
    $_SESSION["demo1"] = "default_1";
    var_dump(session_name());
    //session的销毁
    session_destroy();
    ?>
```

运行的效果如下：

 ![][11]

对于个人来说比较推荐使用第二种方法，因为当要销毁session的时候，那么也就意味着session已经失效了，所以这个时候我们把它给删掉才是最好的处理方式，一方面可以减少对硬盘的存储，另外一方面可以相对优化session的查询速度。

好了，这个时候我们应该要设置传递给浏览器端的cookie了,默认是自动传送，但是我们应该要学习的是怎样通过后端设置cookie过去

其中有两个方法与session有关的方法我们需要记住，第一个是session_name()，这个是获取cookie的key值得，第二个是session_id，这个是session的文件名

设置的示例代码：

```php
    <?php
    /**
     * Created by PhpStorm.
     * Date: 2017/12/16
     */
    session_start();// 打开session
    $_SESSION["demo1"] = "default_1";
    setCookie(session_name(),session_id(),time()-1000);
    ?>
```

在设置cookie的时候，我们为了程序的安全性，我们应该要禁止JS可以对cookie进行重写，所以需要设置HTTP ONLY，具体的设置方法在Php.ini中找到session.cookie_httponly

然后将其的值设置为1或者true即可

除此之外还可以通过setCookie和ini_set()来动态设置HTTPONLY属性

在使用session的时候，虽然会从浏览器把PHPSESSID传给后端，但是这个课程不需要人为的去参与。我们只需要保证HTTPONLY被设置就行了。下面是完整的代码：

```php
    <?php
    /**
     * Created by PhpStorm.
     * Date: 2017/12/16
     */
    session_start();// 打开session
    if ($_SESSION) {
        var_dump($_SESSION["demo1"]);
    } else {
        $_SESSION["demo1"] = "default_" . time();
        var_dump($_SESSION["demo1"]);
        setCookie(session_name(), session_id(), time(), NULL, NULL, NULL, true);
    }
    
    ?>
```

 session的一些相关注意事项

#### 1. 关闭浏览器session同样存在

如果我们没有人为的去设置cookie的生命周期的时候默认关闭浏览器session的状态是无法被保存下来的，因为没有设置cookie的生命周期，默认这个时候cookie为session cookie也就是在会话存在的时候cookie才有效，所以关闭浏览器cookie失效，导致后端拿不到对应的PHPSESSID,所以无法找到对应的session文件

#### 2. session性能瓶颈怎样解决？

如果是后端存在大量的session的时候，那么这个时候就会出现性能的瓶颈，例如：当后端同时存在有5000个session文件的时候，假设要找的文件是在第4999个，那么也就是说前面至少需要遍历4998次，这样就会浪费过多的时间在后端的循环遍历查找文件中，所以这个时候最有效的方法是使用redis或者mongodb,原理是通过将原本保存在本地的session文件写入到内存中，通过内存换空间的形式来达到提升速度

#### 3. 一般不使用URL重写的方法来传递PHPSESSID

其中主要有两个原因，一个是URL重写方式传递的话会导致URL混乱，影响美观。另一个是增大了用户误操作的几率

更多的session的相关配置请点击 [这里][12]


[1]: http://www.cnblogs.com/st-leslie/p/8016951.html

[4]: ../img/zYBFN3Q.gif
[5]: ../img/yQraYn2.gif
[6]: ../img/euimu22.gif
[7]: ../img/BJV3qmn.gif
[8]: ../img/uiA7Bna.gif
[9]: ../img/FfUzaiz.gif
[10]: ../img/vErEVjb.gif
[11]: ../img/rAruuyI.gif
[12]: http://blog.51cto.com/cmdschool/1714757
# [mysql绑定参数bind_param原理以及防SQL注入][0]

 2013-07-31 15:26  25022人阅读  


假设我们的用户表中存在一行.用户名字段为username.值为aaa.密码字段为pwd.值为pwd..

下面我们来模拟一个用户登录的过程..

```php
    <?php  
    $username = "aaa";  
    $pwd = "pwd";  
    $sql = "SELECT * FROM table WHERE username = '{$username}' AND pwd = '{$pwd}'";  
    echo $sql; //输出  SELECT * FROM table WHERE username = 'aaa' AND pwd = 'pwd'  
    ?>
```

这样去执行这个sql语句.显然是可以查询出来东西的.返回用户的这一列.登录成功!!  
然后我改一下..把密码改一下.随便一个值.如下.我改成了ppp.

```php
    <?php  
    $pwd = 'ppp';  
    $sql = "SELECT * FROM table WHERE username = '{$username}' AND pwd = '{$pwd}'";  
    echo $sql; //输出  SELECT * FROM table WHERE username = 'aaa' AND pwd = 'ppp'  
    ?>
```

这样很显然.如果去执行这个SQL语句..是查询不到东西的.也就是密码错误.登录失败!!  
但是有的人总是不老实的.他们会想尽一切办法来进行非法的登录.所谓非法就是在他不知道用户名密码的时候进行登录.并且登录成功..  
那么他们所做的原理是什么呢??其实原理都是利用SQL语句..SQL语句强大的同时也给我们带来了不少麻烦..  
我来举个最简单的例子.我们要运用到的SQL关键字是or  
还是上面的代码.我们只要修改一下密码即可

```php
    <?php  
    $username = "aaa";  
    $pwd = "fdsafda' or '1'='1";  //前面的密码是瞎填的..后来用or关键字..意思就是无所谓密码什么都执行  
    $sql = "SELECT * FROM table WHERE username = '{$username}' AND pwd = '{$pwd}'";  
    echo $sql;  //输出  SELECT * FROM table WHERE username = 'aaa' AND pwd = 'fdsafda' or '1'='1'  
    ?>
```

执行一下这个SQL语句..可怕的事情发生了..竟然可以查询到这一行数据..也就是登录成功了..  
这是多么可怕的事情..

**SQL注入演示教程，见博文：[http://blog.csdn.net/wusuopubupt/article/details/8818996][6]**

  
[PHP][7]为了解决这个问题.magic_quotes state..就是[php][7]会自动过滤传过来的GET.POST等等.  
题外话.实践证明这个东西是畸形的..大部分程序不得不为判断此功能而耗费了很多代码..  
在[Java][8]中可没有这个东西..那么Java中如何防止这种SQL注入呢??

Java的sql包中提供了一个名字叫PreparedStatement的类.  
这个类就是我要说的绑定参数!  
什么叫绑定参数??我继续给大家举例..(我用PHP举例)

```php
    <?php  
    $username = "aaa";  
    $pwd = "pwd";  
    $sql = "SELECT * FROM table WHERE username = ? AND pwd = ?";  
    bindParam($sql, 1, $username, 'STRING');  //以字符串的形式.在第一个问号的地方绑定$username这个变量  
    bindParam($sql, 2, $pwd, 'STRING');       //以字符串的形式.在第二个问号的地方绑定$pwd这个变量  
    echo $sql;  
    ?>
```

当然.到此.你肯定不知道会输出什么..更无法知道绑定参数有什么好处!这样做的优势是什么.更不知道bindParam这个函数到底做了什么.  
下面我简单的写一下这个函数：

```php
    <?php  
    /**  
     * 模拟简单的绑定参数过程  
     *  
     * @param string $sql    SQL语句  
     * @param int $location  问号位置  
     * @param mixed $var     替换的变量  
     * @param string $type   替换的类型  
     */ 
    $times = 0;  
    //这里要注意，因为要“真正的"改变$sql的值，所以用引用传值
    function bindParam(&$sql, $location, $var, $type) {  
        global $times;  
        //确定类型  
        switch ($type) {  
            //字符串  
            default:                    //默认使用字符串类型  
            case 'STRING' :  
                $var = addslashes($var);  //转义  
                $var = "'".$var."'";      //加上单引号.SQL语句中字符串插入必须加单引号  
                break;  
            case 'INTEGER' :  
            case 'INT' :  
                $var = (int)$var;         //强制转换成int  
            //还可以增加更多类型..  
        }  
        //寻找问号的位置  
        for ($i=1, $pos = 0; $i<= $location; $i++) {  
            $pos = strpos($sql, '?', $pos+1);  
        }  
        //替换问号  
        $sql = substr($sql, 0, $pos) . $var . substr($sql, $pos + 1); 
    }  
    ?>
```

注:由于得知道去除问号的次数..所以我用了一个global来解决.如果放到类中就非常容易了.弄个私有属性既可

通过上面的这个函数.我们知道了..**绑定参数的防注入方式其实也是通过转义进行的**..只不过是对于变量而言的..  
我们来做一个实验：


```php
    <?php  
    $times = 0;  
    $username = "aaaa";  
    $pwd = "123";  
    $sql = "SELECT * FROM table WHERE username = ? AND pwd = ?";  
    bindParam($sql, 1, $username, 'STRING');  //以字符串的形式.在第一个问号的地方绑定$username这个变量  
    bindParam($sql, 2, $pwd, 'INT');       //以字符串的形式.在第二个问号的地方绑定$pwd这个变量  
    echo $sql;  //输出  SELECT * FROM table WHERE username = 'aaaa' AND pwd = 123  
    ?>
```

可以看到.生成了非常正规的SQL语句.那么好.我们现在来试下刚才被注入的那种情况


```php
    <?php  
    $times = 0;  
    $username = "aaa";  
    $pwd = "fdsafda' or '1'='1";  
    $sql = "SELECT * FROM table WHERE username = ? AND pwd = ?";  
    bindParam($sql, 1, $username, 'STRING');  //以字符串的形式.在第一个问号的地方绑定$username这个变量  
    bindParam($sql, 2, $pwd, 'STRING');       //以字符串的形式.在第二个问号的地方绑定$pwd这个变量  
    echo $sql; //输出  SELECT * FROM table WHERE username = 'aaa' AND pwd = 'fdsafda\' or \'1\'=\'1'  
    ?>
```

可以看到.pwd内部的注入已经被转义.当成一个完整的字符串了..这样的话.就不可能被注入了.

原文地址 ：[http://hi.baidu.com/woyigui/item/afc6ec2efaa49f0f73863e2e][9]

参考：[http://stackoverflow.com/questions/60174/how-can-i-prevent-sql-injection-in-php][10]

[0]: http://blog.csdn.net/wusuopubupt/article/details/9668501
[5]: #
[6]: http://blog.csdn.net/wusuopubupt/article/details/8818996
[7]: http://lib.csdn.net/base/php
[8]: http://lib.csdn.net/base/java
[9]: http://hi.baidu.com/woyigui/item/afc6ec2efaa49f0f73863e2e
[10]: http://stackoverflow.com/questions/60174/how-can-i-prevent-sql-injection-in-php
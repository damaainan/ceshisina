# [手把手教你SQL注入攻防（PHP语法）][0]

2013-04-18 15:07  4636人阅读  

版权声明：https://github.com/wusuopubupt

闲话不说，直接来！

理论补充：
 
 1.[http://blog.csdn.net/wusuopubupt/article/details/8752348][5]

 2.[http://www.cnblogs.com/hkncd/archive/2012/03/31/2426274.html][6]

**1.什么是SQL注入，[猛戳wikipedia查看][7]**

**2.本地[测试][8]代码：**

如果表单提交正确，就打印hello，“username”

否则，打印“404 not found!”
```php
    <?php 
        require 'config.php';
        $DBConnection = mysql_connect ( "$dbhost", "$dbuser", "$dbpwd" );
        mysql_select_db ( "$dbdatabase" );
        
        if(isset($_GET['submit']) && $_GET['submit']){    
        $sql="select * from test where name='".$_GET['username']."'and password='".$_GET['password']."'";
        //echo $sql;exit;
        $result=mysql_query($sql,$DBConnection);    
        $num=mysql_num_rows($result);       
        if($num>=1)
        {
            echo "hello,".$_GET['username'];
        }
        else {
            echo"404 not found";
        }
    }
    ?>
    <form action="login.php" method="GET">
    <table>
        <tr>
            <td>username</td>
            <td><input type="textbox" name="username"/></td>
            <td>password</td>
            <td><input type="textbox" name="password"></td>
            <td>submit</td>
            <td><input type="submit" name="submit"></td>
        </tr>
    </table>
    </form>
```
**3.浏览器界面显示：**

 

![][10]

4.重头戏，sql注入：

![][11]

![][12]

**5.原理--为什么用户名不正确，却可以显示hello?**

我可以echo一下：

    $sql="select * from test where name='".$_GET['username']."'and password='".$_GET['password']."'";
    echo $sql;exit;

显示：

![][13]

拿到我的[MySQL][14][数据库][14]中查询：

![][15]

可以看到，居然能查到信息， 因为sql语句中 ，前一半单引号被闭合，后一半单引号被 “--”给注释掉，中间多了一个永远成立的条件“1=1”，这就造成任何字符都能成功登录的结果。

**6.小结：**

1）其实这个sql注入过程上很简单，困难的地方在于提交SQL注入语句的灵活性上面，单引号的使用很关键，另外，多用echo打印调试也很值得一试~~

2）GET方式提交表单很危险，所以还是用 POST 方式吧！

参考：[http://blog.csdn.net/gideal_wang/article/details/4316691][16]

3）防止SQL注入：可以看出，sql注入就是用户提交一些非法的字符（如本文的单引号’和sql语句的注释号--，还有反斜杠\等），所以要用转义：  htmlspecialchars函数，mysql_read_escape_string函数 都可以实现。

4）[js][17]段验证表单了，JSP/[PHP][18]等后台还要验证码？

---需要，因为 friebug可以禁用JS ...

---------------------

update:

上面的方法，当password通过md5加密的话，就无法实现注入了，那么就在username上做手脚：

![][19]

username后面的内容就都被注释掉了。哈哈~

参考：[http://newaurora.pixnet.net/blog/post/166231341-sql-injection-%E7%AF%84%E4%BE%8B(%E7%99%BB%E5%85%A5%E7%AF%84%E4%BE%8B)][20]

by wusuopuBUPT

[0]: http://blog.csdn.net/wusuopubupt/article/details/8818996
[5]: http://blog.csdn.net/wusuopubupt/article/details/8752348
[6]: http://www.cnblogs.com/hkncd/archive/2012/03/31/2426274.html
[7]: https://zh.wikipedia.org/wiki/SQL%E8%B3%87%E6%96%99%E9%9A%B1%E7%A2%BC%E6%94%BB%E6%93%8A
[8]: http://lib.csdn.net/base/softwaretest
[9]: #
[10]: ../img/1366267382_5432.jpg
[11]: ../img/1366269562_2435.jpg
[12]: ../img/1366269512_8036.jpg
[13]: ../img/1366269350_2751.jpg
[14]: http://lib.csdn.net/base/mysql
[15]: ../img/1366269252_6200.jpg
[16]: http://blog.csdn.net/gideal_wang/article/details/4316691
[17]: http://lib.csdn.net/base/javascript
[18]: http://lib.csdn.net/base/php
[19]: http://img.blog.csdn.net/20140122144415484?watermark/2/text/aHR0cDovL2Jsb2cuY3Nkbi5uZXQvd3VzdW9wdUJVUFQ=/font/5a6L5L2T/fontsize/400/fill/I0JBQkFCMA==/dissolve/70/gravity/SouthEast
[20]: http://newaurora.pixnet.net/blog/post/166231341-sql-injection-%E7%AF%84%E4%BE%8B(%E7%99%BB%E5%85%A5%E7%AF%84%E4%BE%8B)
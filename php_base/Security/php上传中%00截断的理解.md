# php上传中%00截断的理解

 时间 2017-08-18 15:20:18  

原文[http://www.secange.com/2017/08/php上传中�截断的理解/][1]


Python脚本中：

```python
    def hex_to_asc(ch):
        return '{:c}'.format(int(float.fromhex(ch)))
     
    for i in range(100):
        s = '%02d' % i
        print "s:%s,hex_to_asc(s):%s" % (s,hex_to_asc(s))
```

可以得到如下结果：

    s:00,hex_to_asc(s):NULL
    ...
    s:29,hex_to_asc(s):)
    s:30,hex_to_asc(s):0
    s:31,hex_to_asc(s):1
    s:32,hex_to_asc(s):2
    s:33,hex_to_asc(s):3
    s:34,hex_to_asc(s):4
    s:35,hex_to_asc(s):5
    s:36,hex_to_asc(s):6
    s:37,hex_to_asc(s):7
    s:38,hex_to_asc(s):8
    s:39,hex_to_asc(s):9
    s:40,hex_to_asc(s):@
    s:41,hex_to_asc(s):A
    s:42,hex_to_asc(s):B
    s:43,hex_to_asc(s):C
    s:44,hex_to_asc(s):D
    s:45,hex_to_asc(s):E
    s:46,hex_to_asc(s):F
    s:47,hex_to_asc(s):G
    s:48,hex_to_asc(s):H
    s:49,hex_to_asc(s):I
    s:50,hex_to_asc(s):P
    s:51,hex_to_asc(s):Q
    s:52,hex_to_asc(s):R
    s:53,hex_to_asc(s):S
    s:54,hex_to_asc(s):T
    s:55,hex_to_asc(s):U
    s:56,hex_to_asc(s):V
    s:57,hex_to_asc(s):W
    s:58,hex_to_asc(s):X
    s:59,hex_to_asc(s):Y
    s:60,hex_to_asc(s):`
    s:61,hex_to_asc(s):a
    s:62,hex_to_asc(s):b

ascii码对照表中情况：

    标准I表：
       bin          dec           hex           缩写/字符                     解释
    0000 0000        0            00            NUL(null)                   空字符  
    0000 0001        1            01        SOH(start of headline)         标题开始 
       ...
    0010 0000        32           20             (space)                     空格
    0011 0000        48           30                0    
    0100 0001        65           41                A
    0110 0001        97           61                a
       ...

在计算机中，所有的数据在存储和运算时都要使用二进制数表示（因为计算机用高电平和低电平分别表示1和0），例如，像a、b、c、d这样的52个字母（包括大写）、以及0、1等数字还有一些常用的符号（例如`*`、`#`、`@`等）在计算机中存储时也要使用二进制数来表示，而具体用哪些二进制数字表示哪个符号，当然每个人都可以约定自己的一套（这就叫编码），而大家如果要想互相通信而不造成混乱，那么大家就必须使用相同的编码规则，于是美国有关的标准化组织就出台了ASCII编码，统一规定了上述常用符号用哪些二进制数来表示。

个人理解成`ascii`码只是一个对照关系，如果硬说`ascii`是多少，则把`ascii`码值当作“缩写/字符”一栏对应的值，即二进制（01000001）对应十进制（65）对应十六进制(41)，而它们对应的ascii码为键盘上的可控字符“A”。

所以%00截断时的下面两种情况：

#### 1.在url中加入%00，如http://xxoo/shell.php%00.jpg

2.在`burpsuite`中用burp自带的十六进制编辑工具将”shell.php .jpg”(中间有空格)中的空格由20改成00

在1中，url中的`%00`（形如`%xx`）,web server会把它当作十六进制处理，然后将该十六进制数据`hex（00）`“翻译”成统一的ascii码值“NUL（null）”，实现了截断。

在2中，burpsuite用burp自带的十六进制编辑工具将”shell.php .jpg”(中间有空格)中的空格由20改成00，如果burp中有二进制编辑工具。

所以在用python（或其它语言）中，要想“写出”截断符（null），也就是要写出ascii码值的NUL（null），也就是要由hex（十六进制）下的00变成ascii码值，这是语言和解释器以及计算机之间的关系,正如上面的python脚本。

而php中:

——–w3c:

定义和用法

chr() 函数从指定的 ASCII 值返回字符。

ASCII 值可被指定为十进制值、八进制值或十六进制值。八进制值被定义为带前置 0，而十六进制值被定义为带前置 0x。

——–w3c (w3c这里的说法把ascii值看作有不同的进制表示形态的各进制值)

其中`chr(61)`为`=`，`chr(061)`为`1`，`chr(0x61)`为`a`，`chr(128)=chr(0x80)`,`chr(255)=chr(0xff)`

#### script 1

```php
    <?php
    for($k=0;$k<=255;$k++)
    {
        $a='shell.php'.chr($k)."1.jpg";
        echo 'k:'.$k.'   '.'$a:'.$a.'   '.'iconv("UTF-8","gbk",$a):'.iconv("UTF-8","gbk",$a)."<br>";
    }
    ?>
```

其中`iconv(“UTF-8″,”gbk”,$a)`或是`iconv(“UTF-8″,”gb2313”,$a)`都会在`chr(128)`到`chr(255)`之间截断，使结果为shell.php,如图：

![][4]

其中128和255为十进制数据表示,此处`NUL(nul)(chr(0x00))`并不能截断`iconv`函数,而是`chr(128)-chr(255)`,也即`chr(0x80)-chr(0xff)`截断`iconv`函数.或者用如下代码中`hex2asc`函数:

#### script 2

```php
    <?php
      function asc2hex($str) {  
          return '/x'.substr(chunk_split(bin2hex($str), 2, '/x'),0,-2);  
      }
     
     
      function hex2asc($str) {  
          $str = join('',explode('/x',$str));  
          $len = strlen($str);  
          for ($i=0;$i<$len;$i+=2) $data.=chr(hexdec(substr($str,$i,2)));  
              return $data;  
          }  
         
         
          for($k=0;$k<256;$k++)
          {
              $a=sprintf("/x%02x",$k);
              echo '$k:'.$k.' ';
              echo '$a:'.$a.' ';
              echo 'hex2asc($a):'.hex2asc($a);
              $file_name="shell.php".hex2asc($a)."1.jpg";
              echo '$file_name:'.$file_name.'  ';
              $file_name=iconv("UTF-8","gb2312",$file_name);
              echo 'iconv("UTF-8","gb2312",$file_name):'.$file_name."<br>";
          }
      }
      /*
      echo hex2asc('/x00');
      */
      ?>
```
执行结果：

![][5]

同样是从`128-255`可以实现截断。

相同情况下的python脚本为：

```python
    def hex_to_asc(ch):
        return '{:c}'.format(int(float.fromhex(ch)))
     
    for i in range(256):
        s = '%02x' % i
        print "s:",s,"    ",
        print "hex_to_asc(s):",hex_to_asc(s)
        #print "s:%s,hex_to_asc(s):%s" % (s,hex_to_asc(s))
```
具体截断效果可用在post数据到web server中，看web server目录处理结果，可参考http://www.wooyun.org/bugs/wooyun-2014-048293

本文中代码可见：https://github.com/xinghuacai/-00-.git


[1]: http://www.secange.com/2017/08/php上传中�截断的理解/
[4]: http://img2.tuicool.com/73Qzmiz.png
[5]: http://img0.tuicool.com/ayYN3qJ.png
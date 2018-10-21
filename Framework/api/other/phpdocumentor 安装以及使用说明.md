## phpdocumentor 安装以及使用说明

来源：[http://www.cnblogs.com/cmderq/p/9822695.html](http://www.cnblogs.com/cmderq/p/9822695.html)

时间 2018-10-20 19:36:00

 
## 一  缘由
 
最近改版公司网站和app端的api，发现很多函数和方法都没写注释，搞得每次调用之前还需要看底层实现，有的方法名和功能还类似，区分不出使用哪个最优！为了避免给后人挖坑，除了将代码写得规范外，还想自动生成api文档，方便后来人！于是就研究上了phpdocumentor。今天说下它的安装以及使用。首先需要声明的是，网站的教程很多，但问题在于，按照网上的一些教程来，坑爹，老失败。于是干脆自己动手写一篇，记录下自己的经历。
 
## 二  安装
 
安装的环境是win10+xampp，
 
1      php.exe的路径  是：C:\xampp\php；首先就是查看自己的php.exe目录下有无pear这个文件，好吧，我这个是没有的。那么， 将 **[https://pear.php.net/go-pear.phar][4]**  另存为go-pear.phar文件，并保存到php.exe所在路径中
 
2     第二步很重要，很多文章中都是说，直接进入到php的安装目录，执行命令  `php go-pear.phar`
 
问题就出在这里：报错，提示 ：
```
PHP Warning: mkdir(): File exists in phar://C:/xampp/php/go-pear.phar/System.php on line 294
 
Warning: mkdir(): File exists in phar://C:/xampp/php/go-pear.phar/System.php on line 294
 
PHP Warning: mkdir(): No such file or directory in phar://C:/xampp/php/go-pear.phar/System.php on line 294
 
Warning: mkdir(): No such file or directory in phar://C:/xampp/php/go-pear.phar/System.php on line 294
 
Unable to create Temporary directory for processing C:\xampp\php\phpdoc\tmp.
 
Run this script as administrator or pick another location.
 
C:\xampp\php>PHP Warning: mkdir(): File exists in phar://C:/xampp/php/go-pear.phar/System.php on line 294
 
Could not open input file: Warning:
```
如图所示：
 
![][0]
 
找了好久，终于找到原因了：权限不够导致的！！！需要以管理员的权限进入到dos下，我直接在C:\Windows\System32目录下，以管理员的方式进入dos中：
 
![][1]
 
然后切换到刚才的目录下，执行 php go-pear.phar， 按回车默认system然后继续。后面操作均为默认。最后成功安装，查看php.exe同目录就可以看到pear这个文件了  ：
 
![][2]
 
安装成功后，再来执行`pear install phpdocumentor`即可。
 
## 三 使用phpdocumentor
 
输入`phpdoc -h` 会有如下提示：
 
![][3]
 
一般，用得多的几个参数解释如下：
 
-f 要进行分析的文件名，多个文件用逗号隔开
 
-d 要分析的目录，多个目录用逗号分割
 
-t 生成的文档的存放路径
 
-o 输出的文档格式，结构为输出格式：转换器名：模板目录。
 
例如，我这边生成doc的命令是：
 
    phpdoc -d "C:\www\web"  -t  "C:\www\web\doc"
 
然后在对应的目录下去查看生成的文档即可！


[4]: https://pear.php.net/go-pear.phar
[0]: https://img1.tuicool.com/bEfEfuj.png
[1]: https://img2.tuicool.com/J7beQzA.png
[2]: https://img1.tuicool.com/AnyEFzq.png
[3]: https://img2.tuicool.com/z22Ibea.png
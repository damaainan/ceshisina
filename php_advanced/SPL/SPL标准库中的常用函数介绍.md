PHP SPL标准库中提供了一些函数用来处理如[自动加载][0]、迭代器处理等。

spl_autoload_extensions()添加spl_autoload()可加载的文件扩展名  
spl_autoload_register()注册函数到SPL __autoload函数栈中。
```php 
/*test1.php*/  
<?php  
class Test1  
{  
}  
  
/*test2.lib.php*/  
<?php  
class Test2  
{  
}  
  
/*test.php*/  
<?php  
//设置可加载类的文件扩展名  
spl_autoload_extensions(".php,.inc.php,.class.php,.lib.php");  
//设置include_path,autoload会在这些path中去寻找类文件,可通过PATH_SEPARATOR添加多个path  
set_include_path(get_include_path().PATH_SEPARATOR.'libs/');  
//不提供参数，默认实现函数是spl_autoload()  
spl_autoload_register();  
  
$test1 = new Test1();  
$test2 = new Test2();
```
spl_autoload()它是__autoload()的默认实现，它会去include_path中加载文件(.php/.inc)
```php
/*test1.php*/  
<?php  
class Test1  
{  
}  
  
/*test.php*/  
<?php  
set_include_path(get_include_path().PATH_SEPARATOR.'libs/');  
spl_autoload('test1');  
$test1 = new Test1();
```
spl_autoload_call()调用所有spl_autoload_register注册函数来加载文件
```php
/*test1.php*/  
<?php  
class Test  
{  
public function getFilename()  
{  
echo 'test1.php';  
}  
}  
  
/*test2.lib.php*/  
<?php  
class Test  
{  
public function getFilename()  
{  
echo 'test2.lib.php';  
}  
}  
  
/*test.php*/  
<?php  
  
function loader($classname)  
{  
if($classname == 'Test1') {  
require __DIR__ . '/test1.php';  
}  
if($classname == 'Test2') {  
require __DIR__ . '/test2.lib.php';  
}  
}  
  
spl_autoload_register('loader');  
spl_autoload_call('Test2');  
  
  
$test = new Test();  
$test->getFilename(); //test2.lib.php
```
其它SPL 函数介绍:  
[class_implements][1] — 返回指定的类实现的所有接口。   
[class_parents][2]— 返回指定类的父类。  
[class_uses][3]— Return the traits used by the given class  
[iterator_apply][4] — 为迭代器中每个元素调用一个用户自定义函数  
[iterator_count][5]— 计算迭代器中元素的个数  
[iterator_to_array][6] — 将迭代器中的元素拷贝到数组  
[spl_autoload_functions][7] — 返回所有已注册的__autoload()函数  
[spl_autoload_unregister][8] — 注销已注册的__autoload()函数  
[spl_classes][9] — 返回所有可用的SPL类  
[spl_object_hash][10]— 返回指定对象的hash id

如iterator相关函数使用：
```php
$iterator = new ArrayIterator (array( 'recipe' => 'pancakes' , 'egg' , 'milk' , 'flour' ));  
  
print_r(iterator_to_array($iterator)); //将迭代器元素转化为数组  
echo iterator_count($iterator); //计算迭代器元素的个数  
print_r(iterator_apply($iterator, 'print_item', array($iterator)));//为迭代器每个元素调用自定义函数  
  
  
function print_item(Iterator $iterator)  
{  
echo strtoupper ( $iterator -> current ()) . "\n" ;  
return TRUE ;  
}
```
[0]: http://www.jb51.net/article/49984.htm
[1]: http://php.net/manual/zh/function.class-implements.php
[2]: http://php.net/manual/zh/function.class-parents.php
[3]: http://www.php.net/manual/zh/function.class-uses.php
[4]: http://php.net/manual/zh/function.iterator-apply.php
[5]: http://php.net/manual/zh/function.iterator-count.php
[6]: http://php.net/manual/zh/function.iterator-to-array.php
[7]: http://php.net/spl-autoload-functions
[8]: http://php.net/manual/zh/function.spl-autoload-unregister.php
[9]: http://php.net/manual/zh/function.spl-classes.php
[10]: http://php.net/manual/zh/function.spl-object-hash.php
 spl_autoload — __autoload()函数的默认实现

### 说明

> void  **spl_autoload** ( string $class_name [, string $file_extensions ] )

本函数提供了__autoload()的一个默认实现。如果不使用任何参数调用 spl_autoload_register() 函数，则以后在进行 __autoload() 调用时会自动使用此函数。 

### 参数

- class_name  
- file_extensions   
在默认情况下，本函数先将类名转换成小写，再在小写的类名后加上 .inc 或 .php 的扩展名作为文件名，然后在所有的包含路径(include paths)中检查是否存在该文件。 

### 返回值

没有返回值。


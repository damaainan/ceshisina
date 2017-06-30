 spl_autoload_extensions — 注册并返回spl_autoload函数使用的默认文件扩展名。

### 说明

> string  **spl_autoload_extensions** ([ string $file_extensions ] )

本函数用来修改和检查 __autoload() 函数内置的默认实现函数 spl_autoload() 所使用的扩展名。 

### 参数

- file_extensions   
当不使用任何参数调用此函数时，它返回当前的文件扩展名的列表，不同的扩展名用逗号分隔。要修改文件扩展名列表，用一个逗号分隔的新的扩展名列表字符串来调用本函数即可。中文注：默认的spl_autoload函数使用的扩展名是".inc,.php"。 

### 返回值

逗号分隔的 spl_autoload() 函数的默认文件扩展名。


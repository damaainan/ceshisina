 spl_autoload_call — 尝试调用所有已注册的__autoload()函数来装载请求类

### 说明

> void  **spl_autoload_call** ( string $class_name )

可以直接在程序中手动调用此函数来使用所有已注册的__autoload函数装载类或接口。 

### 参数

- class_name  
搜索的类名。 

### 返回值

没有返回值。


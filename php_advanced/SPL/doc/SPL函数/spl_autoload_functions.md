 spl_autoload_functions — 返回所有已注册的__autoload()函数。

### 说明

> array  **spl_autoload_functions** ( void )

获取所有已注册的 __autoload() 函数。 

### 参数

此函数没有参数。

### 返回值

包含所有已注册的__autoload函数的数组（array）。如果自动装载函数队列未激活，则返回**FALSE**。如果没有已注册的函数，则返回一个空数组。


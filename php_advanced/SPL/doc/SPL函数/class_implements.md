### 说明

> array  **class_implements** ( mixed $class [, bool $autoload ] )

本函数返回一个数组，该数组中包含了指定类class及其父类所实现的所有接口的名称。 

### 参数

- class  
对象（类实例）或字符串（类名称）。 

- autoload  
是否允许使用__autoload 魔术函数来自动装载该类。默认值为**TRUE**。 

### 返回值

调用成功则返回一个数组，否则返回**FALSE**。 

### 更新日志

版本 说明 5.1.0 增加了允许参数class为字符串的选项。增加了autoload参数。 

### 范例

**Example #1 **class_implements()**example**
```
 <?php  

 interface foo { }  
 class bar implements foo {}  
 print_r ( class_implements (new bar ));  
 // since PHP 5.1.0 you may also specify the parameter as a string 
 print_r ( class_implements ( 'bar' ));  
  
  
function __autoload ( $class_name ) {  
    require_once $class_name . '.php' ;  
}  
 // use __autoload to load the 'not_loaded' class 
 print_r ( class_implements ( 'not_loaded' , true ));  
 ?>  
```

 以上例程的输出类似于：

    Array
    (
        [foo] => foo
    )
    
    Array
    (
        [interface_of_not_loaded] => interface_of_not_loaded
    )


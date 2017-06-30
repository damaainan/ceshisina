 class_uses — Return the traits used by the given class

### 说明

> array  **class_uses** ( mixed $class [, bool $autoload = true ] )

This function returns an array with the names of the traits that the given class uses. This does however not include any traits used by a parent class. 

### 参数

- class    
An object (class instance) or a string (class name). 

- autoload  
Whether to allow this function to load the class automatically through the __autoload()] magic method. 

### 返回值

An array on success, or **FALSE** on error. 

### 范例

**Example #1 **class_uses()**example**
```
 <?php  
 trait foo { }  
class bar {  
use foo ;  
}  
 print_r ( class_uses (new bar ));  
 print_r ( class_uses ( 'bar' ));  
  
function __autoload ( $class_name ) {  
require_once $class_name . '.php' ;  
}  
 // use __autoload to load the 'not_loaded' class 
 print_r ( class_uses ( 'not_loaded' , true ));  
 ?>  
```
 以上例程的输出类似于：

    Array
    (
        [foo] => foo
    )
    
    Array
    (
        [foo] => foo
    )
    
    Array
    (
        [trait_of_not_loaded] => trait_of_not_loaded
    )


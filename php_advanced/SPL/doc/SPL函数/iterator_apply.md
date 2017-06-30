 iterator_apply — 为迭代器中每个元素调用一个用户自定义函数

### 说明

> int  **iterator_apply** ( Traversable $iterator , callable $function [, array $args ] )

循环迭代每个元素时调用某一回调函数。 

### 参数

- iterator  
需要循环迭代的类对象。 

- function  
迭代到每个元素时的调用的回调函数。 

> **Note**: 为了遍历 iterator 这个函数必须返回 **TRUE**。

- args  
传递到回调函数的参数。 

### 返回值

返回已迭代的元素个数。 

### 范例

**Example #1 **iterator_apply()**example**
```
 <?php 
 function print_caps ( Iterator $iterator ) {  
echo strtoupper ( $iterator -> current ()) . "\n" ;  
return TRUE ;  
}  
 $it = new ArrayIterator (array( "Apples" , "Bananas" , "Cherries" )); 
 iterator_apply ( $it , "print_caps" , array( $it )); ?>  
```
 以上例程会输出：

    APPLES
    BANANAS
    CHERRIES


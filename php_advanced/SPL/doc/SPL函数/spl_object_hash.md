 spl_object_hash — 返回指定对象的hash id

### 说明

> string  **spl_object_hash** ( object $obj )

本函数为指定对象返回一个唯一标识符。这个标识符可用于作为保存对象或区分不同对象的hash key。 

### 参数

- object   
Any object. 

### 返回值

字符串，对于每个对象它都是唯一的，并且对同一个对象它总是相同。 

### 范例

**Example #1 A **spl_object_hash()**example**
```
<?php  
$id = spl_object_hash ( $object ); 
$storage [ $id ] = $object ; 

?>  
```

### 注释

> **Note**: 

> When an object is destroyed, its hash may be reused for other objects.

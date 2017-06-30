 iterator_to_array — 将迭代器中的元素拷贝到数组

### 说明

> array  **iterator_to_array** ( Traversable $iterator [, bool $use_keys = true ] )

将迭代器中的元素拷贝到数组。 

### 参数

- iterator  
被拷贝的迭代器。 

- use_keys  
是否使用迭代器元素键作为索引。 

### 返回值

一个数组，包含迭代器中的元素。 

### 更新日志

版本 说明 5.2.1 添加了 use_keys 参数。 

### 范例

**Example #1 **iterator_to_array()**example**
```
 <?php  
$iterator = new ArrayIterator (array( 'recipe' => 'pancakes' , 'egg' , 'milk' , 'flour' )); 
var_dump ( iterator_to_array ( $iterator , true ));
 var_dump ( iterator_to_array ( $iterator , false )); 
 ?>  
```

以上例程会输出：

    array(4) {
      ["recipe"]=>
      string(8) "pancakes"
      [0]=>
      string(3) "egg"
      [1]=>
      string(4) "milk"
      [2]=>
      string(5) "flour"
    }
    array(4) {
      [0]=>
      string(8) "pancakes"
      [1]=>
      string(3) "egg"
      [2]=>
      string(4) "milk"
      [3]=>
      string(5) "flour"
    }


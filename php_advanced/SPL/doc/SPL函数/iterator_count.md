 iterator_count — 计算迭代器中元素的个数

### 说明

> int  **iterator_count** ( Traversable $iterator )

计算迭代器中的元素个数。 

### 参数

- iterator  
要计数的迭代器。 

### 返回值

迭代器iterator中的元素个数。 

### 范例

**Example #1 **iterator_count()**example**
```
 <?php  
$iterator = new ArrayIterator (array( 'recipe' => 'pancakes' , 'egg' , 'milk' , 'flour' )); 
var_dump ( iterator_count ( $iterator )); 
?>  
```
以上例程会输出：

    int(4)


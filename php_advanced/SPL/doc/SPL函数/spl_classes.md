 spl_classes — 返回所有可用的SPL类

### 说明

> array  **spl_classes** ( void )

本函数返回当前所有可用的 SPL 类的数组。 

### 参数

此函数没有参数。

### 返回值

Returns an array containing the currently available SPL classes. 

### 范例

**Example #1 **spl_classes()**example**
```
 <?php  
  
    print_r ( spl_classes ());  
 ?>  
```

 以上例程的输出类似于：

    Array
    (
        [ArrayObject] => ArrayObject
        [ArrayIterator] => ArrayIterator
        [CachingIterator] => CachingIterator
        [RecursiveCachingIterator] => RecursiveCachingIterator
        [DirectoryIterator] => DirectoryIterator
        [FilterIterator] => FilterIterator
        [LimitIterator] => LimitIterator
        [ParentIterator] => ParentIterator
        [RecursiveDirectoryIterator] => RecursiveDirectoryIterator
        [RecursiveIterator] => RecursiveIterator
        [RecursiveIteratorIterator] => RecursiveIteratorIterator
        [SeekableIterator] => SeekableIterator
        [SimpleXMLIterator] => SimpleXMLIterator
    )


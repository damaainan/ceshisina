### SPL是用于解决典型问题(standard problems)的一组接口与类的集合。 

#### 分类：
###### 数据结构  
###### 迭代器  
###### 接口  
###### 异常  
###### SPL 函数  
###### 文件处理  
###### 各种类及接口  


## 数据结构  

### Table of Contents

  - SplDoublyLinkedList
  - SplStack
  - SplQueue
  - SplHeap
  - SplMaxHeap
  - SplMinHeap
  - SplPriorityQueue
  - SplFixedArray
  - SplObjectStorage


**SPL提供了一组标准数据结构. They are grouped here by their underlying implementation which usually defines their general field of application.**


### 双向链表

A Doubly Linked List (DLL) is a list of nodes linked in both directions to each others. Iterator's operations, access to both ends, addition or removal of nodes have a cost of O(1) when the underlying structure is a DLL. It hence provides a decent implementation for stacks and queues. 

- SplDoublyLinkedList 
    - SplStack
    - SplQueue



### 堆

Heaps are tree-like structures that follow the heap-property: each node is greater than or equal to its children, when compared using the implemented compare method which is global to the heap. 

- SplHeap 
    - SplMaxHeap
    - SplMinHeap

- SplPriorityQueue 


### 阵列

Arrays are structures that store the data in a continuous way, accessible via indexes. Don't confuse them with PHP  

- SplFixedArray 


### 映射

A map is a datastructure holding key-value pairs. PHP arrays can be seen as maps from integers/strings to values. SPL provides a map from objects to data. This map can also be used as an object set. 

- SplObjectStorage 


## 迭代器  

### Table of Contents

- AppendIterator
- ArrayIterator
- CachingIterator
- CallbackFilterIterator
- DirectoryIterator
- EmptyIterator
- FilesystemIterator
- FilterIterator
- GlobIterator
- InfiniteIterator
- IteratorIterator
- LimitIterator
- MultipleIterator
- NoRewindIterator
- ParentIterator
- RecursiveArrayIterator
- RecursiveCachingIterator
- RecursiveCallbackFilterIterator
- RecursiveDirectoryIterator
- RecursiveFilterIterator
- RecursiveIteratorIterator
- RecursiveRegexIterator
- RecursiveTreeIterator
- RegexIterator



#### SPL 提供一系列迭代器以遍历不同的对象。


SPL Iterators Class Tree

- ArrayIterator 
    - RecursiveArrayIterator 
- EmptyIterator 
- IteratorIterator 
    - AppendIterator 
    - CachingIterator 
        - RecursiveCachingIterator 
    - FilterIterator 
        - CallbackFilterIterator 
            - RecursiveCallbackFilterIterator 
        - RecursiveFilterIterator 
            - ParentIterator 
        - RegexIterator 
            - RecursiveRegexIterator 
    - InfiniteIterator 
    - LimitIterator 
    - NoRewindIterator 
- MultipleIterator 
- RecursiveIteratorIterator 
    - RecursiveTreeIterator 
- DirectoryIterator (extends SplFileInfo) 
    - FilesystemIterator 
        - GlobIterator 
        - RecursiveDirectoryIterator 




## 接口  

### Table of Contents

- Countable
- OuterIterator
- RecursiveIterator
- SeekableIterator


#### SPL 提供一系列接口。

#### 可参考预定义接口。


#### 接口列表

- Countable
- OuterIterator
- RecursiveIterator
- SeekableIterator


## 异常  

### Table of Contents

- BadFunctionCallException
- BadMethodCallException
- DomainException
- InvalidArgumentException
- LengthException
- LogicException
- OutOfBoundsException
- OutOfRangeException
- OverflowException
- RangeException
- RuntimeException
- UnderflowException
- UnexpectedValueException


#### SPL 提供一系列标准异常。

#### 可参考预定义异常。


### SPL Exceptions Class Tree

- LogicException (extends Exception) 
    - BadFunctionCallException 
        - BadMethodCallException
    - DomainException
    - InvalidArgumentException
    - LengthException
    - OutOfRangeException
- RuntimeException (extends Exception) 
    - OutOfBoundsException
    - OverflowException
    - RangeException
    - UnderflowException
    - UnexpectedValueException



## SPL 函数  

### Table of Contents

- class_implements — 返回指定的类实现的所有接口。
- class_parents — 返回指定类的父类。
- class_uses — Return the traits used by the given class
- iterator_apply — 为迭代器中每个元素调用一个用户自定义函数
- iterator_count — 计算迭代器中元素的个数
- iterator_to_array — 将迭代器中的元素拷贝到数组
- spl_autoload_call — 尝试调用所有已注册的__autoload()函数来装载请求类
- spl_autoload_extensions — 注册并返回spl_autoload函数使用的默认文件扩展名。
- spl_autoload_functions — 返回所有已注册的__autoload()函数。
- spl_autoload_register — 注册给定的函数作为 __autoload 的实现
- spl_autoload_unregister — 注销已注册的__autoload()函数
- spl_autoload — __autoload()函数的默认实现
- spl_classes — 返回所有可用的SPL类
- spl_object_hash — 返回指定对象的hash id

class_implements.md class_parents.md class_uses.md iterator_apply.md iterator_count.md iterator_to_array.md spl_autoload_call.md spl_autoload_extensions.md spl_autoload_functions.md spl_autoload_register.md spl_autoload_unregister.md spl_autoload.md spl_classes.md spl_object_hash.md 
## 文件处理  

### Table of Contents

- SplFileInfo
- SplFileObject
- SplTempFileObject


##### SPL 提供 一些与文件相关的类。



## 各种类及接口  

### Table of Contents

- ArrayObject
- SplObserver
- SplSubject


##### 无法归入其它 SPL 分类的类和接口。

# PHP中为什么in_array(0, ['a', 'b', 'c'])返回true

阅读 64，3 小时前 发布，来源：[www.awaimai.com][0]

目录

* [1 类型转换][1]
* [2 严格比较][2]
* [3 false和null][3]
* [4 数组中有true][4]

在PHP中，数据会自动转换类型后进行比较。

所以会发现一个奇怪的现象，就是：

```php
in_array(0, ['a', 'b', 'c']); // 返回bool(true)，也就相当于数组中有0
array_search(0, ['a', 'b', 'c']); // 返回int(0)，也就是第一个值的下标
0 == 'abc'; // 返回bool(true)，也就相当于相等
```

这两个表达式都返回true。

直观上看，0没有在数组['a', 'b', 'c']中，也不会等于abc这个字符串。

**那怎么会返回**true**呢？**

## 1 类型转换 

原因就在于，比较时的PHP做了类型转换。

PHP官网上的说明：[http://php.net/manual/en/language.types.string.php#language.types.string.conversion][5]

也就是在比较前，string类型的数据会转换成int型，然后再比较。

而如果string类型数据第一个字符不是数字，就会转换成0。例如，

```php
echo intval("Bye");    // 输出0
```

因为in_array()和array_search()默认都是松散比较，相当于==，所以就得到true。

## 2 严格比较 

那如何得到false呢？

就是用严格比较，如下，

```php
in_array(0, ['a', 'b', 'c'], true); // 返回false
array_search(0, ['a', 'b', 'c'], true); // 返回false
0 === 'abc'; // 返回false
```

强制做类型比较，这样就能拿到精确的结果。

## 3 false和null 

那么，如果用false和null与字符串数组比较会如何呢？

它们是不会转换成int型的，所以结果是这样的：

```php
in_array(null, ['a', 'b', 'c']); //返回false
in_array(false, ['a', 'b', 'c']); //返回false
```

## 4 数组中有true 

还有另外一个看起来比较奇怪的现象：

```php
in_array('a', [true, 'b', 'c']); // 返回bool(true)，相当于数组里面有字符'a'
array_search('a', [true, 'b', 'c']); // 返回int(0)，相当于找到了字符'a'
```
这是为什么呢？

说起来也很好理解，松散比较下，任何string都等于true。

要想不相等，用严格比较。

**参考资料：**

1. [Php, in_array, 0 value][6]
1. [PHP in_array() / array_search() odd behaviour][7]
1. [String conversion to numbers][5]

[0]: /r/1250000009038170?shareId=1210000009038171
[1]: #1
[2]: #2
[3]: #3_falsenull
[4]: #4_true
[5]: http://php.net/manual/en/language.types.string.php#language.types.string.conversion
[6]: http://stackoverflow.com/questions/13846769/php-in-array-0-value
[7]: http://stackoverflow.com/questions/2739441/php-in-array-array-search-odd-behaviour
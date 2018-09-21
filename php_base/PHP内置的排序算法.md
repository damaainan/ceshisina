### PHP内置的排序算法

PHP有丰富的预定义函数库，也包含不同的排序函数。它有不同的功能来排序数组中的项目，你可以选择按值还是按键/索引进行排序。在排序时，我们还可以保持数组值与它们各自的键的关联。下面是这些函数的总结

| 函数名 | 功能 |
| - | - |
| sort() | 升序排列数组。value/key关联不保留 |
| rsort() | 按反向/降序排序数组。index/key关联不保留 |
| asort() | 在保持索引关联的同时排序数组 |
| arsort() | 对数组进行反向排序并维护索引关联 |
| ksort() | 按关键字排序数组。它保持数据相关性的关键。这对于关联数组是有用的 |
| krsort() | 按顺序对数组按键排序 |
| natsort() | 使用自然顺序算法对数组进行排序，并保持value/key关联 |
| natcasesort() | 使用不区分大小写的“自然顺序”算法对数组进行排序，并保持value/key关联。 |
| usort() | 使用用户定义的比较函数按值对数组进行排序，并且不维护value/key关联。第二个参数是用于比较的可调用函数 |
| uksort() | 使用用户定义的比较函数按键对数组进行排序，并且不维护value/key关联。第二个参数是用于比较的可调用函数 |
| uasort() | 使用用户定义的比较函数按值对数组进行排序，并且维护value/key关联。第二个参数是用于比较的可调用函数 |


对于sort()、rsort()、ksort()、krsort()、asort()以及 arsort()下面的常量可以使用


* SORT_REGULAR - 正常比较单元（不改变类型）
* SORT_NUMERIC - 单元被作为数字来比较
* SORT_STRING - 单元被作为字符串来比较
* SORT_LOCALE_STRING - 根据当前的区域（locale）设置来把单元当作字符串比较，可以用 setlocale() 来改变。
* SORT_NATURAL - 和 natsort() 类似对每个单元以“自然的顺序”对字符串进行排序。 PHP 5.4.0 中新增的。
* SORT_FLAG_CASE - 能够与 SORT_STRING 或 SORT_NATURAL 合并（OR 位运算），不区分大小写排序字符串。
<?php 

// 数组迭代器

// 数组迭代器实现SQL的LIMIT子句和OFFSET子句相同的迭代访问功能

$arr = array('a','b','c','e','f','g','h','i','j','k');
$arrIterator = new ArrayIterator($arr);
$limitIterator = new LimitIterator($arrIterator, 3, 4);
foreach($limitIterator as $v)
{
    echo $v;
}


// 在一个循环中抚今追昔访问两个或多个数组

$arrFirst = new ArrayIterator(array(1,2,3));
$arrSecond = new ArrayIterator(array(4,5,6));
$iterator = new AppendIterator();
$iterator->append($arrFirst);
$iterator->append($arrSecond);
foreach($iterator as $v)
{
    echo $v;
}


// 高级的数组合并功能
// 使用LimitIterator迭代器,AppendIterator迭代器和iterator_to_array()函数创建一个包含了每个输入数组前两元素的数组

$arrFirst = new ArrayIterator(array(1,2,3));
$arrSecond = new ArrayIterator(array(4,5,6));
$iterator = new AppendIterator();
$iterator->append(new LimitIterator($arrFirst, 0, 2));
$iterator->append(new LimitIterator($arrSecond, 0, 2));
print_r(iterator_to_array($iterator,false));
// 过滤数组接口FilterIterator

class GreaterThanThreeFilterIterator extends FilterIterator
{
    public function accept()
    {
        return ($this->current() > 3);
    }
}
$arr = new ArrayIterator(array(1,2,3,4,5,6,7,8));
$iterator = new GreaterThanThreeFilterIterator($arr);
print_r(iterator_to_array($iterator));
// 正则过滤数组接口regexIterator从FilterIterator类继承而来,允许使用几种正则表达式模式来匹配那些键值

$arr = array('apple','avocado','orange','pineapple');
$arrIterator = new ArrayIterator($arr);
$iterator = new RegexIterator($arrIterator, '/^a/');
print_r(iterator_to_array($iterator));
// 递归数组接口RecursiveArrayIterator,RecursiveIteratorIterator

$arr = array(
    0 => 'a',
    1 => array('a','b','c'),
    2 => 'b',
    3 => array('a','b','c'),
    4 => 'c'
);
$arrayIterator = new RecursiveArrayIterator($arr);
$it = new RecursiveIteratorIterator($arrayIterator);
print_r(iterator_to_array($it,false));
<?php 
/**
 * 策略模式
--------

现实例子
> 考虑排序的例子，我们实现了冒泡排序，但是数据开始增长，冒泡排序变得很慢。为了应对这个，我们实现了快速排序。但现在尽管快速排序算法对大数据集表现更好，小数据集却很慢。为了应对这一点，我们实现一个策略，冒泡排序处理小数据集，快速排序处理大数据集。

白话
> 策略模式允许你基于情况选择算法或策略。
 */


interface SortStrategy {
    public function sort(array $dataset) : array; 
}

class BubbleSortStrategy implements SortStrategy {
    public function sort(array $dataset) : array {
        echo "Sorting using bubble sort";
         
        // Do sorting
        return $dataset;
    }
} 

class QuickSortStrategy implements SortStrategy {
    public function sort(array $dataset) : array {
        echo "Sorting using quick sort";
        
        // Do sorting
        return $dataset;
    }
}


class Sorter {
    protected $sorter;
    
    public function __construct(SortStrategy $sorter) {
        $this->sorter = $sorter;
    }
    
    public function sort(array $dataset) : array {
        return $this->sorter->sort($dataset);
    }
}


$dataset = [1, 5, 4, 3, 2, 8];

$sorter = new Sorter(new BubbleSortStrategy());
$sorter->sort($dataset); // 输出 : Sorting using bubble sort

$sorter = new Sorter(new QuickSortStrategy());
$sorter->sort($dataset); // 输出 : Sorting using quick sort
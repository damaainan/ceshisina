<?php
/**
 * 列表
 * 
 * 列表的抽象数据类型定义
 * listSize（ 属性） 列表的元素个数
 * pos（ 属性） 列表的当前位置
 * 
 * length（ 属性） 返回列表中元素的个数 用listSize属性实现
 * clear（ 方法）  清空列表中的所有元素
 * toString（ 方法） 返回列表的字符串形式
 * getElement（ 方法） 返回当前位置的元素
 * insert（ 方法） 在现有元素后插入新元素
 * append（ 方法） 在列表的末尾添加新元素
 * remove（ 方法） 从列表中删除元素
 * front（ 方法） 将列表的当前位置移动到第一个元素
 * end（ 方法）   将列表的当前位置移动到最后一个元素
 * prev（ 方法） 将当前位置后移一位
 * next（ 方法） 将当前位置前移一位
 * currPos（ 方法） 返回列表的当前位置
 * moveTo（ 方法） 将当前位置移动到指定位置
 */

class DList{
	protected $listSize = 0;
    protected $pos = 0;
    protected $dataStore = []; // 初始化一个空数组来保存列表元素
    function append($element) {
	    $this->dataStore[$this->listSize++] = $element;
	}
	function find($element) {
	    for ($i = 0; $i < $this->dataStore->length; ++$i) {
	        if ($this->dataStore[$i] == $element) {
	            return $i;
	        }
	    }
	    return -1;
	}

	function remove($element) {
	    $foundAt = $this->find($element);
	    if ($foundAt > -1) {
	        $this->dataStore->splice($foundAt, 1);
	        --$this->listSize;
	        return true;
	    }
	    return false;
	}

	function length() {
	    return $this->listSize;
	}

	function toString() {
	    return $this->dataStore;
	}

	function insert($element, $after) {
	    $insertPos = $this->find($after);
	    if ($insertPos > -1) {
	        $this->dataStore->splice($insertPos + 1, 0, $element);
	        ++$this->listSize;
	        return true;
	    }
	    return false;
	}

	function clear() {
	    unset($this->dataStore);
	    $this->dataStore = [];
	    $this->listSize = $this->pos = 0;
	}

	function contains($element) {
	    for ($i = 0; $i < $this->dataStore->length; ++$i) {
	        if ($this->dataStore[$i] == $element) {
	            return true;
	        }
	    }
	    return false;
	}

	function front() {
	    $this->pos = 0;
	}

	function end() {
	    $this->pos = $this->listSize - 1;
	}

	function prev() {
	    if ($this->pos > 0) {
	        --$this->pos;
	    }
	}

	function next() {
	    if ($this->pos < $this->listSize - 1) {
	        ++$this->pos;
	    }
	}

	function currPos() {
	    return $this->pos;
	}

	function moveTo($position) {
	    $this->pos = $position;
	}

	function getElement() {
	    return $this->dataStore[$this->pos];
	}
}

$list=new DList();
$list->append(23);
$list->append(3);
$list->append(16);
$list->append(17);
var_dump($list->length());
var_dump($list->toString());
var_dump($list->currPos());
$list->next();
var_dump($list->currPos());
var_dump($list->getElement());

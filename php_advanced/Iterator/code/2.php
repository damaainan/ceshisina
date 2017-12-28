<?php
//迭代器的简单实现
class Xrange implements Iterator
{
	protected $start;
	protected $limit;
	protected $step;
	protected $i;
	public function __construct($start, $limit, $step =0)
	{
	        $this->start = $start;
	        $this->limit = $limit;
	        $this->step  = $step;
	}
	public function rewind()
	{
	        $this->i = $this->start;
	}
	public  function next()
	{
	        $this->i += $this->step;
	}
	public function current()
	{
	return $this->i;
	}
	public function key()
	{
	return $this->i +1;
	}
	public function valid()
	{
	return $this->i <= $this->limit;
	}
}
// 通过 foreach 遍历来看看这个迭代器的效果：
foreach(new Xrange(0,10,2)as $key => $value){
    printf("%d %d\n", $key, $value);
}
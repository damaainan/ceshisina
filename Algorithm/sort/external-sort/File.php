<?php

class File implements FileInterface
{

	public $file;
	public $list = [];
	public $fileSize;
	
	function __construct($file)
	{
		$this->file = $file;
		$this->fileSize = filesize($file); // 得到文件大小
	}

	public function read($offset= 0, $maxlen = 500000 )
	{
		// 将整个文件读入一个字符串
		$list = file_get_contents($this->file);
		$this->list = explode(',', $list); // 将字符串打散为数组
		$list = null;
	}

	public function write($file)
	{
		// 打开文件$file，如果不存在则创建它
		$file = fopen($file, 'w');
        // 把排好序的数组合并成字符串后写入文件
		$string = fwrite($file, implode("\n", $this->list) );
		fclose($file);
	}
}
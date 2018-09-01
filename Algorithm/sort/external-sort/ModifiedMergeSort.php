<?php

class ModifiedMergeSort implements SortingAlgorithmInterface
{
	protected $file;
	protected $sizeLimit;
	protected $tmpFiles = [];
	
	function __construct(FileInterface $file, $sizeLimit)
	{
		$this->file = $file;
		// 把兆转换为字节
		$this->sizeLimit = $sizeLimit * 1000000;
		echo "sizeLimit: {$this->sizeLimit} bytes\n";
	}

	public function sort($outputPath = 'output')
	{
		if ($this->file->fileSize > $this->sizeLimit) {

			return $this->externalSort();
		}

		// 如果文件在指定的大小范围内, 直接在内存中进行排序

		// 把文件中的数字读入到数组里面，保存在$this->file->list里面
		$this->file->read();
		echo "sorting file\n";
        // 对数组中的元素进行排序，排序之后$this->file->list就是有序的数组了
		sort($this->file->list);

		return $this->output($outputPath);
	}

	protected function externalSort() 
	{
		// 分成小块
		$this->chunk();
		// 文件拆分后，将它们按正确的顺序重新组合在一起
		$this->mergeFile();

		echo "External sort TBC\n";
	}
	

	protected function output($outputPath)
	{
		echo "writing file\n";
		$filePath = explode('/', $this->file->file);
        
		//array_pop($filePath)的返回值是random_***.txt
		// $elements是数组：[random_***, txt]
		$elements = explode('.', array_pop($filePath));

		// 构造输出文件的名字
		// array_shift 用来删除数组中的第一个元素，并返回被删除元素的值，即random_***
		$outputName = $outputPath . '/' . array_shift($elements) . '_sorted.txt';
		
		// 把排好序的数据写入$outputName文件里面
		return $this->file->write($outputName);
	}

	protected function chunk()
	{
		echo "Splitting file...\n";

		$handle = fopen($this->file->file, "r") or die("Couldn't get handle");

		if ($handle) {

			$tmpNum = 1;

            // 检测文件指针是否已到达文件末尾
		    while (!feof($handle)) {
		        $buffer = fgets($handle, $this->sizeLimit);

		        $list = explode(',', $buffer);

		        // 在把数据保存在临时文件之前排序
		        // $list数组里面是已经排好序的了
		        sort($list);

		        // 移除空域
		        $this->file->list = array_filter($list);

		        $fileName = 'tmp-' . $tmpNum . '.txt';

		        $this->file->write("output/$fileName");

		        $this->tmpFiles[] = $fileName;

		        ++$tmpNum;

                // fstat() 函数返回关于打开文件的信息，fstat($handle)['size']是文件大小
		        // $this->tmpFiles[$fileName]['size'] = fstat($handle)['size'];

		        echo "New file: output/$fileName\n";
		    }

		    fclose($handle);
		}
	}

	protected function mergeFile() {
		echo "正在归并...\n";

		// 判断归并是否结束
		$flag_num = 0;
		// 最小数字的文件索引
		$min_data_index = -1;
		// 最小数字
        $min_data = PHP_INT_MAX;

		$little_file_nums = count($this->tmpFiles);
        
        // 用来存放所有小文件的文件句柄
        $handle = [];

        // 用来存放大文件的文件句柄
        $big_file_handle = fopen('output/big_file.txt', 'w');
        // 用来存放小文件的第一个数字，即该文件的最小值
        $first_data = [];

        for ($i = 0; $i < $little_file_nums; ++$i) {
        	$handle[$i] = fopen('output/' . $this->tmpFiles[$i], "r");
        }

        for ($i = 0; $i < $little_file_nums; ++$i) {
        	$first_data[$i] = (int)fgets($handle[$i]);
            
        	if ($min_data > $first_data[$i]) {
        		$min_data_index = $i;
        		$min_data = $first_data[$i];
        	}
        }

        fwrite($big_file_handle, $min_data);
	    fwrite($big_file_handle, "\n");

        while (1) { // 文件全部读取完毕
            $first_data[$min_data_index] = (int)fgets($handle[$min_data_index]);
            if ($first_data[$min_data_index] == false) {
            	++$flag_num;
            	$first_data[$min_data_index] = PHP_INT_MAX;

            	if ($flag_num == $little_file_nums) {
            		break;
            	}
            }

		    $min_data_index = -1;
            $min_data = PHP_INT_MAX;

            // 找出数组中最小的数字
            for ($i = 0; $i < $little_file_nums; ++$i) {
                if ($min_data > $first_data[$i]) {
	        		$min_data_index = $i;
	        		$min_data = $first_data[$i];
	        	}
            }

	        fwrite($big_file_handle, $min_data);
	        fwrite($big_file_handle, "\n");
        }

        for ($i = 0; $i < $little_file_nums; ++$i) {
        	fclose($handle[$i]);
        }
	}
}
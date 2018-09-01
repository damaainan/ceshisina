## 外部排序--PHP版本

来源：[https://juejin.im/post/5acdf4bc6fb9a028d93784e8](https://juejin.im/post/5acdf4bc6fb9a028d93784e8)

时间 2018-04-12 10:03:21

 
发现网上搜不到PHP版本的外部排序，有点心疼。所以打算自己写一个，这里和大家分享下。这应该是网上第一个PHP版本的外部排序。
 
小伙伴们应该都写过或者听过一些排序算法，例如：`冒泡排序`、`选择排序`、`归并排序`、`快速排序`、`堆排序`。（建议小伙伴们都要掌握这几种最基本的排序算法，一定要随时可以写出来）
 
但是对于外部排序不一定都听过。因为学生嘛，平时写的项目大多是玩具，所以很少有大量的数据需要处理，自然就没必要用到外部排序了。
 
但是，从今天开始，小伙伴们就需要知道什么是外部排序了。
 
是这样的，一般我们要排序的数据量都很小，所以写个快速排序呀等等排序算法就可以把数据排好。而且这些排序算法都有一个特点，就是 **`只用到了内存`**  ，因此这些算法被称为内部排序。而 **`外部排序就是在排序过程中用到了外存，例如磁盘`**  。为什么需要用到外存？因为需要排序的数据量很大，例如好几十个G，但是可使用的内存却只有几个G，因此，我们不能直接使用内部排序，需要借助外存来完成整个排序的过程。
 
## 外部排序总体思路
 
1、从整个大文件里面循环读取一定量的数据到内存里面，然后把这部分数据排好序之后，写入一个小文件。达到把整个大文件分割成一定量的小文件的目的。
 
2、因为每个小文件里面的数据都是已经排好序的了，所以可以对小文件进行归并的操作，最终得到的大文件就是完全排好序的。
 
## 开始实现
 
首先我们定义了一些接口和类，来方便我们完成对文件的一些读写操作。
 
FileInterface.php文件：
 
```php
interface FileInterface
{
	public function read();
	public function write($file);
}
```
 
SortingAlgorithmInterface.php文件：
 
```php
<?php

interface SortingAlgorithmInterface
{
	public function sort($outputPath);
}
```
 
File.php文件：
 
```php
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
```
 
这里提一点，在`write`方法中，我使用了`implode("\n", $this->list)`，也就是说通过`"\n"`来对数组元素进行连接，写入文件里面。使用`"\n"`的原因是为了方便从文件中读取一个值。
 
OK，接下来解释关键性的操作了，也就是ModifiedMergeSort.php文件里面的内容。
 
先来看看ModifiedMergeSort类里面都有什么成员变量：
 
```php
class ModifiedMergeSort implements SortingAlgorithmInterface {
    protected $file;
	protected $sizeLimit;
	protected $tmpFiles = [];
}
```
 `$file`是用来保存文件对象的，也就是一个`File`对象。
 `$sizeLimit`用来存放我们可以使用多少内存来完成排序的值。
 `$tmpFiles`用来保存小文件的文件名。
 
OK，接下来我们来看看`ModifiedMergeSort`类里面的`sort`方法：
 
```php
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
```
 
在这里，有一个判断的操作：如果大文件的文件大小要大于我们可以使用的内存大小，则使用外部排序，即调用`externalSort`方法；否则把整个文件读取到内存中，然后直接在内存中进行内部排序即可。
 
我们重点看看`externalSort`方法。
 
```php
protected function externalSort() 
	{
		// 分成小块
		$this->chunk();
		// 文件拆分后，将它们按正确的顺序重新组合在一起
		$this->mergeFile();

		echo "External sort TBC\n";
	}
```
 
可以看到，这就是我们上面写的外部排序总体思路： **`先分块，后归并`**  。
 
OK，我们来看看如何把一个大文件拆分成多个小文件：
 
```php
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
```
 
重点看while循环里面的内容。
 
```php
$buffer = fgets($handle, $this->sizeLimit);
```
 
也就是说，我们每次循环都读取`sizeLimit`字节的数据。
 
```php
$list = explode(',', $buffer);
```
 
也就是说，我们把由这些数据构成的字符串变成数组。
 
```php
sort($list);
```
 
也就是说，对这个数组从小到大进行排序。
 
```php
$fileName = 'tmp-' . $tmpNum . '.txt';
$this->file->write("output/$fileName");
```
 
也就是说，把排好序的数据写入小文件。小文件的命名格式是`tmp-第几个小文件-.txt`。
 
OK，完成了文件拆分，接下来是归并的过程。
 
```php
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
```
 
这里，我们采用的是多路归并排序而不是两路归并排序。
 
多路归并排序思路：
 

* 将每个小文件里面的文件指针指向的数读入(由于有序，所以为该文件最小数)，存放在一个first_data数组中； 
* 找出first_data数组中最小的数min_data，及其对应的文件索引index； 
* 把从first_data数组中找到的那个最小的数写入大文件`big_file.txt`里面，然后更新数组first_data(根据index读取该文件下一个数，来代替min_data)；  
* 判断所有小文件的所有数据是否都读取完毕。如果没有，继续归并操作；如果全部读完了，跳出循环，结束归并的过程。 
 

所以，可以得到如下的流程图：
 
 ![][0]
这些步骤中，比较难办的一点就是如何判断所有的数据都读取完毕了。其中，一个最笨的办法就是每次都在循环中判断所有文件的文件指针是否都指向`EOF`了，如果都指向`EOF`，那么就说明所有数据读取完毕；否则没有，需要继续读。但是，这样的话，每次都需要对所有的文件指针进行遍历。如果小文件有几万个，那消耗的时间就多了，所以我没有采取这种办法。
 
于是我问了问我一个打算法比赛的室友，得到了一种思路：因为我们知道小文件一共有多少个，所以我们可以去统计一共有多少个文件的文件指针到达了文件末尾。一旦到达文件末尾的文件指针个数和小文件的个数相等，那么我们就可以判断所有数据被读取完毕了，也就是如下代码：
 
```php
if ($flag_num == $little_file_nums) {
    break;
}
```
 
OK，我再讲一讲一个细节上的问题：
 
```php
$first_data[$i] = (int)fgets($handle[$i]);
```
 
这里对从文件里面读取出来的数据进行了数据类型的转换，原因是：从文件里面读取出来的数字是字符串类型，而字符串类型做大小比较就有些坑了。例如，字符串`"123"`要比字符串`"5"`小。
 
下面是我的测试数据：
 
 ![][1]

（我对一个46兆左右的数据进行排序，使用的内存限制是1兆）
 
可以看出，耗时为185秒左右，也就是3分钟左右。
 
好了，外部排序大致就是这样了，其实还是有很大的优化空间的，特别是在归并的过程中，可以使用胜者树，败者树来进行优化。有时间我会补充。
 
[获得源码][2]

**`external-sort`**
 
更多PHP知识，可以关注我的个人博客
 
参考：
 
[大文件拆分成小文件][3]
 
[外部排序][4]
 


[2]: https://github.com/huanghantao/external-sort
[3]: https://github.com/abdimaye/external-sort
[4]: https://www.jianshu.com/p/dce6a43d4678
[0]: ./img/YnENRjM.png 
[1]: ./img/AjuEN3y.png 
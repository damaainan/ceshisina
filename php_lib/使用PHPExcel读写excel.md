## 使用PHPExcel读写excel

来源：[https://tlanyan.me/use-phpexcel-to-read-and-write-excel/](https://tlanyan.me/use-phpexcel-to-read-and-write-excel/)

时间 2018-01-24 04:07:40

 
[PHPOffice][1] 出品的PHPExcel是PHP读取和生成Excel的极佳工具。本文参考官方文档，对PHPExcel进行简要总结，希望对使用PHPExcel操作Excel的同行有帮助。
 
## PHPExcel介绍
 
PHPExcel是用PHP实现的电子表格文档读写类库，其支持的文档类型包括：Excel(.xls)后缀，Excel 2007(.xlsx后缀），CSV(.csv后缀），LibreOffice Calc(.ods后缀)，PDF和HTML等格式（某些格式只能读）。PHPExcel运行环境为PHP 5.2+，需要开启php_zip、php_xml和php_gd2拓展。
 
细心的读者可能看到PHPOffice有另外一款作品： [PHPSpreadsheet][2] 。PHPSpreadsheet也是一个Excel读写类库，与PHPExcel主要区别是：
 
 
* PHPSpreadsheet是PHPExcel的重构版，基于PHP的新特性进行了重写。PHPSpreadsheet要求PHP 5.6+，使用了名字空间、PSR2编码规范、最新的PHP语言新特性等； 
* 对PHP版本的要求加强。官方的PHP版本支持结束后，PHPSpreadsheet对该版本至多额外支持6个月（意味肯定不支持PHP 5.5及以下版本，PHP5.6的支持也即将终止）。对比之下，PHPExcel依然支持PHP 5.2.0； 
* 开发组已将所有资源转移到PHPSpreadsheet，PHPExcel的维护已经停止。 
 
 
PHPSpreadsheet已经放出1.0.0稳定版，官方不再建议使用PHPExcel。本文以下内容基于PHPExcel，掌握透彻后再转换到PHPSpreadsheet也是很容易的。
 
## PHPExcel架构
 
理解PHPExcel的架构，可以先从理解Excel文件的结构开始。一个Excel文件可以包含多个表单，每个表单包含多个单元；文件、表单和单元都可以单独设置属性。这些概念对应到PHPExcel中的类，关系如下：
 
 
* PHPExcel类<-> Excel文件 
* PHPExcel_Worksheet类<-> 表单 
* PHPExcel_Cell<-> 单元 
* PHPExcel_DocumentProperties<-> 文件属性 
* PHPExcel_Style_*<-> 格式设置类 
 
 
下面开始介绍PHPExcel的常用操作。
 
## PHPExcel操作
 
根据上面介绍的关系，分excel文件、表单、单元、格式设置四个部分分别介绍用法。
 
### excel文件
 
PHPExcel使用reader读取文件，writer将文件写入指定的流。由于支持多种格式，读取和写入文件时需要指定文件格式。为了简化reader和writer的创建，PHPExcel提供了工厂类来生成reader和writer。读写文件的示例代码如下：
 
```php
// 读取文件，自动探测文件格式
$excel = PHPExcel_IOFactory::load("./foo.xlsx");
 
// 新建excel文件，需指定格式
$writer = PHPExcel_IOFactory::createWriter($excel, "Excel2007");
$writer->save("./foo2.xlsx");

```
 
如果知道具体格式，可以使用具体的类操作：
 
```php
// 读文件
$reader = PHPExcel_Reader_Excel2007();
$excel = $reader->load("./foo1.xlsx");
 
// 写文件
$writer = PHPExcel_Writer_Excel2007($excel);
$writer->save("./foo2.xlsx");

```
 
可用的reader和writer类可以参考下图：
 
![][0]
 
建议使用工厂方法读取文件，它能自动探测文件格式并加载。这在读取用户上传不同格式的文件时很有用，避免了格式与后缀名不符可能导致的错误。
 
### 表单操作
 
一个excel文件可以包含多个表单，常用操作包括读取、新建、复制和删除表单。
 
获取表单的方式有多种，如获取当前表单、获取指定顺序表单、根据名字获取表单。以下是示例代码：
 
```php
$sheet = $excel->getActiveSheet();
// 获取第二个表单，编号从0开始
$sheet = $excel->getSheet(1);
$sheet = $excel->getSheetByName("Worksheet 1");

```
 
创建表单分为直接excel文件对象直接创建，也可以先创建表单实例，后续再关联。对应方法为：
 
```php
$excel->createSheet();
 
$sheet = new PHPExcel_Worksheet($excel, "sheet 1");
// 一些其他操作
// 作为第二个表单插入到文档中
$excel->addSheet($sheet, 1);

```
 
PHPExcel也支持复制表单（包括复制其他文件的表单）：
 
```php
// 复制表单
$sheet = clone $excel->getSheet(0);
$sheet->setTitle("new sheet");
$excel->addSheet($sheet, 1);
 
// $excel->addExternalSheet可以添加其他文件的表单

```
 
删除表单的API比较简单，只提供了`removeSheetByIndex`一个方法：
 
```php
// 删除最后一个表单
$index = $excel->getSheetCount() - 1;
$excel->removeSheetByIndex($index);
 
// 删除当前表单
$index = $excel->getIndex($excel->getActiveSheet());
$excel->removeSheetByIndex($index);

```
 
## 单元操作
 
单元是承载内容的主题，其上操作比较复杂，大部分的类和API都与单元相关。常用操作的包括定位、取值/赋值、格式化等。下面是一些代码示例：
 
```php
// 获取单元对象
$cell = $sheet->getCell("B1");
$cell = $sheet->getCellByColumnAndRow(1, 1);
 
// 取值
$value = $cell->getValue();
$value = $cell->getCalculatedValue();   // 获取计算后的值
$style = $cell->getStyle(); // 获取格式化对象
$isMerged = $cell->isMergeRangeValueCell();   //是否是合并单元的主单元（合并单元的左上角单元）
 
// 设置值
$sheet->setCellValue("B1", "TEST");
$sheet->setCellValueByColumnAndRow(1, 1, "TEST");
// 批量赋值
$data = [
[2009, 2010, 2011, 2012],
['Q1',   12,   15,   21],
['Q2',   56,   73,   86],
['Q3',   52,   61,   69],
['Q4',   30,   32,    0],
];
$sheet->fromArray($data);
$cell->setValue("foo");
// 显示赋值
$cell->setValueExplicit("123456788900", PHPExcel_Cell_DataType::TYPE_STRING);
 
// 合并单元
$sheet->mergeCells('A18:E22');
 
// 设置格式
// 设置字体为红色
$cell->getStyle()->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
// 设置边框
$cell->getStyle()->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

```
 
## 文件属性
 
设置excel文件的属性，包括常见的作者、标题、创建时间、描述等。该功能由PHPExcel中类型为DocumentProperties的成员变量负责：
 
```php
$property = $excel->getProperties();
$property->setCreator("tlanyan");
$property->setTitle("demo workbook");
$property->setKeywords("tlanyan, PHPExcel");

```
 
## 其他
 
上述介绍了常见的概念和操作，实际中可能会用到的概念还包括：
 
 
* 缓存和性能 
* 图像、图表、超链接等富文本 
* 日期、货币等格式化和本地化 
* 公式设置 
* 打印属性设置 
* 内容对其、边距设置等 
* 文件密码安全设置 
 
 
搞懂基本概念，理清各个对象的关系后，这些高级功能可以参照API文档完成。
 
#### 参考
 
 
* [https://github.com/PHPOffice/PHPExcel][3]  
 
 


[1]: https://github.com/phpoffice
[2]: https://github.com/PHPOffice/PhpSpreadsheet
[3]: https://github.com/PHPOffice/PHPExcel
[0]: ./img/UVJRNnV.png
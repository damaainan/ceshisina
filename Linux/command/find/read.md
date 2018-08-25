#### **总结**

一、5个查找命令中，find、locate、whereis是用来查找具体的文件，which、type是用来查找命令（which也相当于查找文件，但是只查找命令位置）

二、whereis只查找3种类型的文件：二进制文件、说明文件、源代码文件

三、whereis和locate都是从数据库文件查找，所以效率最高

四、使用优先级：

1、区分命令，使用type

2、查找命令位置，使用which、whereis、type（优先使用`whereis`）

3、通过文件名称查找，优先使用locate，找不到时，`updatedb`

4、其他查找条件、或者因数据库文件未更新，使用whereis、locate查找不到时，使用find


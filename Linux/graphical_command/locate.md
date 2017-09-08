 **locate 命令--> 用于查找符合条件的文档，非实时查找，根据数据库模糊匹配。**

**![][0]**

 **备注:**

 1) find与locate区别：

locate与find是查找文件中应用最频繁的。两者不同: find 是去硬盘找，locate 只在/var/lib/slocate资料库中找。locate的速度比find快，它并不是真的查找，而是查数据库，一般文件数据库在/var/lib/slocate/slocate.db中，所以locate的查找并不是实时的，而是以数据库的更新为准，一般是系统自己维护，也可以手工升级数据库.当我们用whereis和locate无法查找到我们需要的文件时，可以使用find，但是find是在硬盘上遍历查找，因此非常消耗硬盘的资源，而且效率也非常低，因此建议大家优先使用whereis和locate

**2) locat命令示例** **：**

**![][1]**

** **3) find,which,whereis,locate汇总** **：****

** **![][2]****

[0]: ./img/20160925203916613.png
[1]: ./img/20160925204854164.png
[2]: ./img/20160925203408657.png
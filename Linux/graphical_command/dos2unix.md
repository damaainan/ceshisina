 **dos2unix**命令--> Dos to UNIX的缩写，用来将DOS格式的文本文件转换为UNIX格式

![][0]

 **备注:**

 1) 为什么需要用dos2unix进行格式转换

 在Linux系统下经常会出现下列提示:

     /bin/sh^M: bad interpreter: No such file or directory

 Window系统中用/r/n来表示换行，Linux中用/n来表示换行，Windows的文本文件，直接拿到Linux中使用可能会会出错，因为多出了“/r”字符，所以需要通过dos2unix 来将window的文本文件转换来适应linux;在Windows下写的文件，到Linux下会出现每行后面有个字符^M，在一些脚本中，这个字符不会被视为空白字符，于是会出现一些莫名奇怪的错误，比如，在vi配置文件virmc中假如有这种字符，打开vi时会提示：  
Trailing characters: ^M  
用dos2unix就可以解决这个问题，dos2unix file_name , 这样便把文件转为unix格式的，前面的问题也会得到解决

 2) 可以使用cat -v来查看是否转换，或者说转换是否成功

![][1]

3) 模拟下场景，演示dos2unix过程

![][2]

[0]: ./img/20160917105934312.png
[1]: ./img/20160917111325690.png
[2]: ./img/20160917112808896.png
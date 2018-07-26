## PhpStorm代码格式化设置

来源：[https://segmentfault.com/a/1190000015617126](https://segmentfault.com/a/1190000015617126)

PhpStorm格式化的快捷键默认为Ctrl+Alt+F，但是一些格式化效果需要你自己设置，比如你想在格式化的时候php代码可以等号对齐，就需要自己配置。首先打开phpstorm后找到Setting/Editor/Code Style/PHP 如下图：

![][0] 

(Scheme 选择 Default 是怎对 IDE 设置的，如果选择 Project 则是只针对当前项目有效)
 **`1.设置等号对齐`**  选择Wrapping and Braces，勾选 Align consecutive assignments 和 Align Key-Values Pairs，如图所示：

![][1]

 **`2.将array()自动转为[]`**  选择Code Conversion，勾选Force short declaration style和Add a comma ater last element in multiline array，如图所示：

![][2]

[0]: ./img/1460000015617129.png
[1]: ./img/1460000015617130.png
[2]: ./img/1460000015617131.png
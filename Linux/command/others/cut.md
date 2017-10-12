## cut命令使用简介

## 0x0 cut出场

有一个字符串 `var=value`，怎么用shell命令取到value？

这时候cut就派上用场了。

`echo "var=value" | cut -d= -f2`  
就会得到value。

`-d=` 表示 `=`是分隔符，把字符串分割。  
`var=value`会分割成两个`fields`. `-f2` 表示输出第二个field，得到value。

`echo "var=value" | cut -d= -f2`将会得到var。

## 0x1 输出内容的选择

    echo "var=value1=value2"| cut -d= -f1-2
    

`-f1-2` 表示从第一个field到第二个fied，上述命令将会输出var=value1.

如果是`-f1-3`，表示从第一个field到第三个field，实际上会输出整个字符串。

`-f1-`表示从第一个field开始，到后面所有field。实际上也是整个字符串。

`echo "var=value1=value2"| cut -d= -f-2`也会输出var=value1。`-f-2`表示第2个field之前的所有field，包含第2个field。

## 0x2 替换分割符

有人说不想输出分割符，这个也可以。如果你想用逗号分割各个field.

    echo "var=value1=value2"| cut -d= -f1- --output-delimiter=,
    

如果你想用空格分割各个field，传递给`--output-delimiter`的空格要加引号

    echo "var=value1=value2"| cut -d= -f1- --output-delimiter=" "


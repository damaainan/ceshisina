# shell脚本编程进阶：数组

关注 2017.07.06 16:36  字数 305  

## 一、数组(array)

1.变量：存储单个元素的内存空间；  
2.数组：存储多个元素的连续的内存空间，相当于多个变量的集合；  
3.数组名和索引(下标)

    索引：编号从0开始，属于数值索引；
    注意：索引可支持使用自定义的格式，而不仅是数值格式，即为关联索引，bash4.0版本之后开始支持；
    bash --version  查看bash版本
    例如：自定义索引为字母和单词(first)；
    bash的数组支持稀疏格式(索引不连续)；

4.声明数组

    declare -a ARRAY_NAME(数组名)
    declare -A ARRAY_NAME: 关联数组(必须先声明数组)
    注意：两者不可相互转换
    临时生效(exit)；先声明，再使用；

## 二、数组赋值

### 1.数组元素的赋值

(1) 一次只赋值一个元素；

    ARRAY_NAME[INDEX]=VALUE
    weekdays[0]="Sunday"
    weekdays[4]="Thursday"
    echo ${weekdays[0]}  显示数组的某个元素
    echo ${weekdays[4]}
    unset weekdays[0]  删除数组中的某个索引
    unset weekdays  删除数组中的全部索引
    引号作用："a_b"这个形式必须加引号；

(2) 一次赋值全部元素：

    ARRAY_NAME=("VAL1" "VAL2" "VAL3" ...)

![][1]




![][2]




  
(3) 只赋值特定元素：

    ARRAY_NAME=([0]="VAL1" [3]="VAL2" ...)

![][3]




  
(4) 交互式数组值对赋值

    read -a ARRAY

![][4]




  
(5)花样赋值

    alpha=({a..z})
    alpha=({1..10..2})
    filename=(/app/bin/*.sh)
    filename=(f{1..6}.{log,txt})

![][5]




  
(6)显示所有数组： declare -a

### 2.引用数组

(1)引用数组元素：

    ${ARRAY_NAME[INDEX]}
    注意：省略[INDEX]表示引用下标为0的元素
    echo ${ARRAY_NAME}

(2)引用数组所有元素：

    ${ARRAY_NAME[*]}
    ${ARRAY_NAME[@]}

![][6]




  
(3)数组的长度(数组中元素的个数)：

    ${#ARRAY_NAME[*]}
    ${#ARRAY_NAME[@]}

![][7]




(4)删除数组中的某元素：导致稀疏格式

    unset ARRAY[INDEX]

(5)删除整个数组：

    unset ARRAY

![][8]




![][9]




## 三、数组数据的处理

### 1.引用数组中的元素：

    数组切片：${ARRAY[@]:offset:number}
    offset: 要跳过的元素个数
    number: 要取出的元素个数
    取偏移量之后的所有元素${ARRAY[@]:offset}

![][10]




### 2.向数组中追加元素：

    ARRAY[${#ARRAY[*]}]=value
    索引号=数组元素个数

![][11]




### 3.关联数组：

    declare -A ARRAY_NAME 
    ARRAY_NAME=([idx_name1]='val1' [idx_name2]='val2‘...)
    注意：关联数组必须先声明再调用

## 四、字符串

### 1.字符串切片

    ${#var}:返回字符串变量var的长度
    ${var:offset}:返回字符串变量var中从第offset个字符后（不包括第offset个字符）的字符开始，到最后的部分，offset的取值在0 到${#var}-1 之间(bash4.2后，允许为负值)
    ${var:offset:number}：返回字符串变量var中从第offset个字符后（不包括第offset个字符）的字符开始，长度为number的部分
    ${var: -length}：取字符串的最右侧几个字符
    注意：冒号后必须有一个空白字符
    ${var:offset:-length}：从最左侧跳过offset字符，一直向右取到距离最右侧lengh个字符之前的内容
    ${var: -length:-offset}：先从最右侧向左取到length个字符开始，再向右取到距离最右侧offset个字符之间的内容
    注意：-length前空格

![][12]




### 2.字符串处理

#### 基于模式取子串

    (1) ${var#*word}：其中word可以是指定的任意字符
    功能：自左而右，查找var变量所存储的字符串中，第一次出现的word, 删除字符串开头至第一次出现word字符之间的所有字符
    (2) ${var##*word}：同上，贪婪模式，不同的是，删除的是字符串开头至最后一次由word指定的字符之间的所有内容

    示例：
    file="var/log/messages“
    ${file#*/}: log/messages
    ${file##*/}: messages

    (3) ${var%word*}：其中word可以是指定的任意字符；
    功能：自右而左，查找var变量所存储的字符串中，第一次出现的word, 删除字符串最后一个字符向左至第一次出现word字符之间的所有字符；
    file="/var/log/messages"
    ${file%/*}: /var/log
    (4) ${var%%word*}：同上，只不过删除字符串最右侧的字符向左至最后一次出现word字符之间的所有字符；

    示例：
    url=http://www.magedu.com:80
    ${url##*:} 80
    ${url%%:*} http

![][13]




#### 查找替换

    ${var/pattern/substr}：查找var所表示的字符串中，第一次被pattern所匹配到的字符串，以substr替换之
    ${var//pattern/substr}: 查找var所表示的字符串中，所有能被pattern所匹配到的字符串，以substr替换之
    ${var/#pattern/substr}：查找var所表示的字符串中，行首被pattern所匹配到的字符串，以substr替换之
    ${var/%pattern/substr}：查找var所表示的字符串中，行尾被pattern所匹配到的字符串，以substr替换之

![][14]




#### 查找并删除

    ${var/pattern}：删除var所表示的字符串中第一次被pattern所匹配到的字符串
    ${var//pattern}：删除var所表示的字符串中所有被pattern所匹配到的字符串
    ${var/#pattern}：删除var所表示的字符串中所有以pattern为行首所匹配到的字符串
    ${var/%pattern}：删除var所表示的字符串中所有以pattern为行尾所匹配到的字符串

![][15]




    字符大小写转换
    ${var^^}：把var中的所有小写字母转换为大写
    ${var,,}：把var中的所有大写字母转换为小写

![][16]




## 五、变量赋值

    (1) ${var:-value} 或${var-value}：如果var为空或未设置，那么返回value；否则返回var的值

![][17]




    (2) ${var:+value}：如果var非空，则返回value，否则返回空值

![][18]




    (3) ${var:=value}：如果var为空或未设置，那么返回value，并将value赋值给var；否则返回var的值

![][19]




    (4) ${var:?error_info}：如果var为空或未设置，那么在当前终端打印error_info；否则返回var的值

![][20]




    为脚本程序使用配置文件,实现变量赋值
    (1) 定义文本文件，每行定义“name=value”
    单独创建一个文本文件，统一从一个文件中调用变量；
    不同业务，可以定义不同的脚本文件；
    (2) 在脚本中source此文件即可

![][21]




## 六、高级变量用法-有类型变量

    Shell变量一般是无类型的，但是bash Shell提供了declare和typeset两个命令用于指定变量的类型，两个命令是等价的；

    declare [选项] 变量名
    -r 声明或显示只读变量
    -i 将变量定义为整型数
    -a 将变量定义为数组
    -A 将变量定义为关联数组
    -f 显示此脚本前定义过的所有函数名及其内容
    -F 仅显示此脚本前定义过的所有函数名
    -x 声明或显示环境变量和函数
    -l 声明变量为小写字母  declare –l var=UPPER
    -u 声明变量为大写字母  declare –u var=lower

## 七、eval命令

    eval命令将会首先扫描命令行进行所有的置换，然后再执行该命令。
    该命令适用于那些一次扫描无法实现其功能的变量，该命令对变量进行两次扫描；

![][22]




    如果第一个变量的值是第二个变量的名字，从第一个变量引用第二个变量的值就称为间接变量引用；
    
    variable1的值是variable2，而variable2又是变量名，variable2的值为value，间接变量引用是指通过variable1获得变量值value的行为；
    variable1=variable2
    variable2=value
    
    bash Shell  提供了两种格式实现间接变量引用
    eval tempvar=\$$variable1
    tempvar=${!variable1}

![][23]




## 八、创建临时文件

    mktemp命令：创建并显示临时文件，可避免冲突
    
    mktemp [OPTION]... [TEMPLATE]
    TEMPLATE: filename.XXX
    X(大写)至少要出现三个；
    
    OPTION：
    -d: 创建临时目录
    -p DIR或--tmpdir=DIR：指明临时文件所存放目录位置

![][24]




## 九、安装复制文件

    install命令：
    install [OPTION]... [-T] SOURCE DEST 单文件
    install [OPTION]... SOURCE... DIRECTORY
    install [OPTION]... -t DIRECTORY SOURCE...
    install [OPTION]... -d DIRECTORY...创建空目录

    选项：
    -m MODE，默认755
    -o OWNER
    -g GROUP

![][25]


[1]: http://upload-images.jianshu.io/upload_images/6044565-06dfe8ae78b072c1.png
[2]: http://upload-images.jianshu.io/upload_images/6044565-102247b8b764b256.png
[3]: http://upload-images.jianshu.io/upload_images/6044565-4f8e71d28d7f2731.png
[4]: http://upload-images.jianshu.io/upload_images/6044565-b4bc9237f03b6048.png
[5]: http://upload-images.jianshu.io/upload_images/6044565-7ac0c77587b4904f.png
[6]: http://upload-images.jianshu.io/upload_images/6044565-26cc2cd67858d94c.png
[7]: http://upload-images.jianshu.io/upload_images/6044565-3b4f10b305e76bac.png
[8]: http://upload-images.jianshu.io/upload_images/6044565-a6f1b9e0f636d9bb.png
[9]: http://upload-images.jianshu.io/upload_images/6044565-50804930ff392e43.png
[10]: http://upload-images.jianshu.io/upload_images/6044565-5196906ae6af4889.png
[11]: http://upload-images.jianshu.io/upload_images/6044565-e1615c0f49cf4594.png
[12]: http://upload-images.jianshu.io/upload_images/6044565-85e5fe108f6fa2ce.png
[13]: http://upload-images.jianshu.io/upload_images/6044565-b8c2063018845355.png
[14]: http://upload-images.jianshu.io/upload_images/6044565-67fc8cf6d71cb404.png
[15]: http://upload-images.jianshu.io/upload_images/6044565-cdb56fea54a020ab.png
[16]: http://upload-images.jianshu.io/upload_images/6044565-353ab84e25275410.png
[17]: http://upload-images.jianshu.io/upload_images/6044565-b413ee86771f4628.png
[18]: http://upload-images.jianshu.io/upload_images/6044565-3ed9748cd797d416.png
[19]: http://upload-images.jianshu.io/upload_images/6044565-d2afad4f8d79ede8.png
[20]: http://upload-images.jianshu.io/upload_images/6044565-2ab33fb4ffb5ebcc.png
[21]: http://upload-images.jianshu.io/upload_images/6044565-ea98707959ab4cd4.png
[22]: http://upload-images.jianshu.io/upload_images/6044565-128f694ff56c71e2.png
[23]: http://upload-images.jianshu.io/upload_images/6044565-bf9aa952660ca23d.png
[24]: http://upload-images.jianshu.io/upload_images/6044565-6692a05245585d82.png
[25]: http://upload-images.jianshu.io/upload_images/6044565-2d8749c6729e47b3.png
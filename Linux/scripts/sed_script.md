### Sed 直接修改文件

sed最常用的用法莫过于替换文件，然而其默认的模式是直接输出在shell中

    sed 's/Old/New/' My_File.txt

如果我们想要sed直接在文件中更改，只需要在sed后面添加 -i 或 -ig即可

    sed -i 's/Old/New' My_File.txt

在mac中需要添加 -ig才能够执行～ anyway，很简单的，试一下就好了～

----

### sed -i 替换时 正则表达式   { 和 } 需要用  \  转义

---

### sed只打印匹配行

  
**注意参数-n**  
  
(1)显示某一行或某些行  
  
显示1,10行  

    $ sed **-n** '1,10p' postgres.conf  
  
显示第10行  

    $ sed **-n** '10p' postgres.conf  
  
(2)显示某些匹配行  

    $ sed **-n**'/This/p' postgres.conf

---

## awk中使用shell脚本中的变量

```sh
for i in `awk -F'/' '{print $NF}' 2.txt`
do   
        num=`awk -F': ' "/$i/" '{print $1}' 1.txt` # 双引号  单引号分隔开
        echo ${num}
done
```


## awk 字符串切割分组

    ls | grep png | awk '{split($0,a,"amp");print a[2]}'


     ls | grep md | awk -F'# ' '{print $2}' | xargs -I[ mv "# "[ [



## awk system 命令的执行

    awk -F': ' '/bVIu/{print $2}' 1.md | awk -F'/' '{system("aria2c -o "$NF".png "$0)}'

> 此条命令实现了下载图片并重命名

**  空格与双引号  ** 是值得注意的地方

#### 将原有图片地址进行替换

    ls *.png | awk -F'.' '{print $1}' | xargs -I[  sed -i "s/https:\/\/segmentfault.com\/img\/[/..\/img\/[.png/" 1.md

> 替换多个文件

    ls *.png | awk -F'.' '{print $1}' | xargs -I[  sed -i "s/http:\/\/img.blog.csdn.net\/[/.\/img\/[.png/"   *.md

> 双引号中变量可以解析  
> 有些字符需要进行转义

#### 将某个文件内容写入某个文件夹下所有文件

    ls Set\ | awk '{system("cat LinkedList.php > Set/"$0)}'

#### 将`/` 替换为 `\/`  (因为在 `sed`  中 用 s 选项做替换时  `/` 属于分隔符，会报错 )

    # [0]: /xiaoting451292510/article/details/12019771 ## 信息样式
     awk '/\[0\]\: \//{print $2}' *.md | awk '{gsub("/","\\\/");print}'


##### 删除文件名长度固定的文件  

**范围比较不好实现 ，> < 总是出问题，暂时未解决**

    ls *.png | awk '{if(length() ==23)  print $0}' | xargs -I[ rm -rf [


##### 去除问号之后的部分
> sed 替换 改变分隔符

     awk '/upload_images/{print $2}' *.md | awk -F'?' '{print $2}' | xargs -I[ sed -i "s@?[@@" *.md

##### 替换文件名
     ls ../img | awk -F'.' '{system("sed -i \'s/"$1"/"$0"/\' *.md")}'


#### sed 分隔符

     echo sksksksksksk | sed 's@sk@SK@2g' 
     skSKSKSKSKSK
     echo sksksksksksk | sed 's@sk@SK@3g'
     skskSKSKSKSK  
     echo sksksksksksk | sed 's@sk@SK@4g'
     skskskSKSKSK 

#### awk 中变量使用 

    ls | xargs -i[ awk 'NR==1{mm=$2}END{system("mv [ "mm".md")}' [

#### awk 批量修改文件名 

```
# 例子
# 将 1.md 等 改为前面的名字， () 有歧义，需要消除

自动化运维之日志系统ElasticSearch篇(一) 1.md
自动化运维之日志系统上线规范(十) 10.md
自动化运维之日志系统ES+Kibana展示(二) 2.md
自动化运维之日志系统Logstash篇(三) 3.md
自动化运维之日志系统Logstash实践Rsyslog(四) 4.md
自动化运维之日志系统Logstash实践TCP(五) 5.md
自动化运维之日志系统Logstash实践JAVA(六) 6.md
自动化运维之日志系统Logstash实践Nginx(七) 7.md
自动化运维之日志系统Logstash解耦实践(八) 8.md
自动化运维之日志系统Logstash实践ES(九) 9.md
```


    ls | xargs -i[ awk 'NR==1{print $2"***["}' [ | awk '{sub(/\[0\]/,"");sub(/\[/,"");sub(/\]/,"");sub(/\(.\)/,"");print} | awk -F'***' '{system("mv "$2" "$1".m d")}'


**全面替换标记g**

使用后缀 /g 标记会替换每一行中的所有匹配：

     sed 's/book/books/g' file

当需要从第N处匹配开始替换时，可以使用 /Ng：

     echo sksksksksksk | sed 's/sk/SK/2g' 
     skSKSKSKSKSK
     echo sksksksksksk | sed 's/sk/SK/3g'
     skskSKSKSKSK  
     echo sksksksksksk | sed 's/sk/SK/4g'
     skskskSKSKSK 



##### 替换

      awk '/\[\!\[image\]/{print}' 10.md | awk -F'\"image\"' '{sub(/\)\]/,"",$2);print $2}' | xargs -i}  sed -i "s@}$@\![]}@" 10.md



###### 替换例子
    # [![image](./img/341820-20160509233115780-1438241527.png "image")](./img/341820-20160509233115202-1577926534.png)

    sed -i 's@\[\!\[.*\]@@' 10.md #先去除前面部分

    awk '/.\/img/{print $1}' 10.md  | xargs -i} sed  -i "s@}$@\![]}@" 10.md  #再加 ![]

**重点在 sed 替换**

    awk -F'(' '/cnitblog/{sub(")","",$3);print $3}' 04-1.md | xargs -I} sed -i "s@^.*}.*$@!\[\]\(}\)@" 04-1.md

    awk -F'(' '/images.cnitblog/{sub(")","",$2);print $2}' *.md | awk '!a[$0]++' | xargs -i[ aria2c [

**`sed -i "s@^.*}.*$@!\[\]\(}\)@"`**    `^.*}.*$`  开头结尾最重要

    awk -F'(' '/images.cnitblog/{sub(")","",$2);print $2}' *.md | awk '!a[$0]++' |  awk -F'/' '{print $NF}' | xargs -I} sed -i "s@^.*}.*$@!\[\]\(../img/}\)@" *.md


###### sed：使用查找时如何将整行替换，而不仅仅替换匹配到的部分呢

    sed "s/^.*do.*$/bad/" test

##### sed 在匹配行的上一行添加字符串    `i\str` 

     sed  -i '/\?php/i\```php' *.md


打印匹配行

    sed -n '/\[\{0,1\}PHP\]\{0,1\}\[\{0,1\}[0-9]\{0,1\}\]\{0,1\}Unit_Framework_TestCase/p' *.md

替换匹配内容，注意 `\`  需要用 `\\\` 实现

     sed -i 's@\[\{0,1\}PHP\]\{0,1\}\[\{0,1\}[0-9]\{0,1\}\]\{0,1\}Unit_Framework_TestCase@PHPUnit\\\Framework\\\TestCase@' *.md



#### 二进制转换

加 64 ，使得中间空位 0 得以保存

    seq 64 127 | xargs -i[ echo "obase=2;[" | bc

截取除第一位之后的数字 

    seq 64 127 | xargs -i[ echo "obase=2;[" | bc |  awk '{print substr($0,2)}'

拼接下载

    seq 64 127 | xargs -i[ echo "obase=2;[" | bc |  awk '{print substr($0,2)}' | awk '{system("aria2c -o ./img/"$0".jpg http://www.chinazwds.org/chinazw/ASPX/mingli/64gua/zxt"$0".jpg")}'
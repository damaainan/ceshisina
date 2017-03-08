# shell awk sed tr grep 语法汇总

**tr 基本语法**  
```
-c # 用字符串1中字符集的补集替换此字符集，要求字符集为ASCII  
-d # 删除字符串1中所有输入字符  
-s # 删除所有重复出现字符序列，只保留第一个:即将重复出现字符串压缩为一个字符串  
[a-z] # a-z内的字符组成的字符串  
[A-Z] # A-Z内的字符组成的字符串  
[0-9] # 数字串  
\octal # 一个三位的八进制数，对应有效的ASCII字符  
[O*n] # 表示字符O重复出现指定次数n。因此[O*2]匹配OO的字符串 
```
**tr中特定控制字符表达方式**
```
\a Ctrl-G \007 # 铃声  
\b Ctrl-H \010 # 退格符  
\f Ctrl-L \014 # 走行换页  
\n Ctrl-J \012 # 新行  
\r Ctrl-M \015 # 回车  
\t Ctrl-I \011 # tab键  
\v Ctrl-X \030
```

```sh
tr A-Z a-z # 将所有大写转换成小写字母  
tr " " "\n" # 将空格替换为换行  
tr -s "[\012]" < plan.txt # 删除空行  
tr -s ["\n"] < plan.txt # 删除空行  
tr -s "[\015]" "[\n]" < file # 删除文件中的^M，并代之以换行  
tr -s "[\r]" "[\n]" < file # 删除文件中的^M，并代之以换行  
tr -s "[:]" "[\011]" < /etc/passwd # 替换passwd文件中所有冒号，代之以tab键  
tr -s "[:]" "[\t]" < /etc/passwd # 替换passwd文件中所有冒号，代之以tab键  
echo $PATH | tr ":" "\n" # 增加显示路径可读性  
1,$!tr -d '\t' # tr在vi内使用，在tr前加处理行范围和感叹号('$'表示最后一行)  
tr "\r" "\n"<macfile > unixfile # Mac -> UNIX  
tr "\n" "\r"<unixfile > macfile # UNIX -> Mac  
tr -d "\r"<dosfile > unixfile # DOS -> UNIX Microsoft DOS/Windows 约定，文本的每行以回车字符(\r)并后跟换行符(\n)结束  
awk '{ print $0"\r" }'<unixfile > dosfile # UNIX -> DOS：在这种情况下，需要用awk，因为tr不能插入两个字符来替换一个字符
```
---
```sh
#!/bin/sh # 在脚本第一行脚本头 # sh为当前系统默认shell,可指定为bash等shell  
sh -x # 执行过程  
sh -n # 检查语法  
(a=bbk) # 括号创建子shell运行  
basename $0 # 从文件名中去掉路径和扩展名  
dirname # 取路径  
$RANDOM # 随机数  
$$ # 进程号  
source FileName # 在当前bash环境下读取并执行FileName中的命令 # 等同 . FileName  
sleep 5 # 间隔睡眠5秒  
trap # 在接收到信号后将要采取的行动  
trap "" 2 3 # 禁止ctrl+c  
$PWD # 当前目录  
$HOME # 家目录  
$OLDPWD # 之前一个目录的路径  
cd - # 返回上一个目录路径  
yes # 重复打印  
yes |rm -i * # 自动回答y或者其他  
ls -p /home # 查看目录所有文件夹  
ls -d /home/ # 查看匹配完整路径  
echo -n aa;echo bb # 不换行执行下一句话  
echo -e "s\tss\n\n\n" # 使转义生效  
echo $a | cut -c2-6 # 取字符串中字元  
echo {a,b,c}{a,b,c}{a,b,c} # 排列组合(括号内一个元素分别和其他括号内元素组合)  
echo $((2#11010)) # 二进制转10进制  
echo aaa | tee file # 打印同时写入文件 默认覆盖 -a追加  
echo {1..10} # 打印10个字符  
printf '%10s\n'|tr " " a # 打印10个字符  
pwd | awk -F/ '{ print $2 }' # 返回目录名  
tac file |sed 1,3d|tac # 倒置读取文件 # 删除最后3行  
tail -3 file # 取最后3行  
outtmp=/tmp/$$`date +%s%N`.outtmp # 临时文件定义  
:(){ :|:& };: # 著名的 fork炸弹,系统执行海量的进程,直到系统僵死  
echo -e "\033[0;31mL\033[0;32mO\033[0;33mV\033[0;34mE\t\033[0;35mY\033[0;36mO\033[0;32mU\e[m" # 打印颜色
```
---
**seq 语法1**
```sh
# 不指定起始数值，则默认为 1  
-s # 选项主要改变输出的分格符, 预设是 \n  
-w # 等位补全，就是宽度相等，不足的前面补 0  
-f # 格式化输出，就是指定打印的格式  
seq 10 100 # 列出10-100  
seq 1 10 |tac # 倒叙列出  
seq -s '+' 90 100 |bc # 从90加到100  
seq -f 'dir%g' 1 10 | xargs mkdir # 创建dir1-10  
seq -f 'dir%03g' 1 10 | xargs mkdir # 创建dir001-010
```
  
**sed **
```sh
sed 10q # 显示文件中的前10行 (模拟"head")  
sed -n '$=' # 计算行数(模拟 "wc -l")  
sed -n '5,/^no/p' # 打印从第5行到以no开头行之间的所有行  
sed -i "/^$f/d" a # 删除匹配行  
sed -i '/aaa/,$d' # 删除匹配行到末尾  
sed -i "s/=/:/" c # 直接对文本替换  
sed -i "/^pearls/s/$/j/" # 找到pearls开头在行尾加j  
sed '/1/,/3/p' file # 打印1和3之间的行  
sed -n '1p' 文件 # 取出指定行  
sed '5i\aaa' file # 在第5行之前插入行  
sed '5a\aaa' file # 在第5行之后抽入行  
echo a|sed -e '/a/i\b' # 在匹配行前插入一行  
echo a|sed -e '/a/a\b' # 在匹配行后插入一行  
echo a|sed 's/a/&\nb/g' # 在匹配行后插入一行  
seq 10| sed -e{1,3}'s/./a/' # 匹配1和3行替换  
sed -n '/regexp/!p' # 只显示不匹配正则表达式的行  
sed '/regexp/d' # 只显示不匹配正则表达式的行  
sed '$!N;s/\n//' # 将每两行连接成一行  
sed '/baz/s/foo/bar/g' # 只在行中出现字串"baz"的情况下将"foo"替换成"bar"   
sed '/baz/!s/foo/bar/g' # 将"foo"替换成"bar"，并且只在行中未出现字串"baz"的情况下替换  
echo a|sed -e 's/a/#&/g' # 在a前面加#号  
sed 's/foo/bar/4' # 只替换每一行中的第四个字串  
sed 's/\(.*\)foo/\1bar/' # 替换每行最后一个字符串  
sed 's/\(.*\)foo\(.*foo\)/\1bar\2/' # 替换倒数第二个字符串  
sed 's/[0-9][0-9]$/&5' # 在以[0-9][0-9]结尾的行后加5  
sed -n ' /^eth\|em[01][^:]/{n;p;}' # 匹配多个关键字  
sed -n -r ' /eth|em[01][^:]/{n;p;}' # 匹配多个关键字  
echo -e "1\n2"|xargs -i -t sed 's/^/1/' {} # 同时处理多个文件  
sed '/west/,/east/s/$/*VACA*/' # 修改west和east之间的所有行，在结尾处加*VACA*  
sed 's/[^1-9]*\([0-9]\+\).*/\1/' # 取出第一组数字，并且忽略掉开头的0  
sed -n '/regexp/{g;1!p;};h' # 查找字符串并将匹配行的上一行显示出来，但并不显示匹配行  
sed -n ' /regexp/{n;p;}' # 查找字符串并将匹配行的下一行显示出来，但并不显示匹配行  
sed -n 's/\(mar\)got/\1ianne/p' # 保存\(mar\)作为标签1  
sed -n 's/\([0-9]\+\).*\(t\)/\2\1/p' # 保存多个标签  
sed -i -e '1,3d' -e 's/1/2/' # 多重编辑(先删除1-3行，在将1替换成2)  
sed -e ['s/@.*//g'][0] -e '/^$/d' # 删除掉@后面所有字符，和空行  
sed -n -e "{s/文本(正则)/替换的内容/p}" # 替换并打印出替换行  
sed -n -e "{s/^ *[0-9]*//p}" # 打印并删除正则表达式的那部分内容  
echo abcd|sed 'y/bd/BE/' # 匹配字符替换  
sed '/^#/b;y/y/P/' 2 # 非#号开头的行替换字符  
sed '/suan/r 读入文件' # 找到含suan的行，在后面加上读入的文件内容  
sed -n '/no/w 写入文件' # 找到含no的行，写入到指定文件中  
sed '/regex/G' # 在匹配式样行之后插入一空行  
sed '/regex/{x;p;x;G;}' # 在匹配式样行之前和之后各插入一空行  
sed 'n;d' # 删除所有偶数行  
sed 'G;G' # 在每一行后面增加两空行  
sed '/^$/d;G' # 在输出的文本中每一行后面将有且只有一空行  
sed 'n;n;n;n;G;' # 在每5行后增加一空白行  
sed -n '5~5p' # 只打印行号为5的倍数  
seq 1 30|sed '5~5s/.*/a/' # 倍数行执行替换  
sed -n '3,${p;n;n;n;n;n;n;}' # 从第3行开始，每7行显示一次  
sed -n 'h;n;G;p' # 奇偶调换  
seq 1 10|sed '1!G;h;$!d' # 倒叙排列  
ls -l|sed -n '/^.rwx.*/p' # 查找属主权限为7的文件  
sed = filename | sed 'N;s/\n/\t/' # 为文件中的每一行进行编号(简单的左对齐方式)  
sed 's/^[ \t]*//' # 将每一行前导的"空白字符"(空格，制表符)删除,使之左对齐   
sed 's/^[ \t]*//;s/[ \t]*$//' # 将每一行中的前导和拖尾的空白字符删除  
echo abcd\\nabcde |sed 's/\\n/@/g' |tr ['@'][0] '\n' # 将换行符转换为换行  
cat tmp|awk '{print $1}'|sort -n|sed -n '$p' # 取一列最大值  
sed -n '{s/^[^\/]*//;s/\:.*//;p}' /etc/passwd # 取用户家目录(匹配不为/的字符和匹配:到结尾的字符全部删除)  
sed = filename | sed 'N;s/^/ /; s/ *\(.\{6,\}\)\n/\1 /' # 对文件中的所有行编号(行号在左，文字右端对齐)  
/sbin/ifconfig |sed 's/.*inet addr:\(.*\) Bca.*/\1/g' |sed -n '/eth/{n;p}' # 取所有IP

修改keepalive配置剔除后端服务器

sed -i '/real_server.*10.0.1.158.*8888/,+8 s/^/#/' keepalived.conf  
sed -i '/real_server.*10.0.1.158.*8888/,+8 s/^#//' keepalived.conf
```
  
  
**模仿rev功能**
```sh
echo 123 |sed '/\n/!G;s/\(.\)\(.*\n\)/&\2\1/;//D;s/.//;'  
/\n/!G; # 没有\n换行符，要执行G,因为保留空间中为空，所以在模式空间追加一空行  
s/\(.\)\(.*\n\)/&\2\1/; # 标签替换 &\n23\n1$ (关键在于& ,可以让后面//匹配到空行)  
//D; # D 命令会引起循环删除模式空间中的第一部分，如果删除后，模式空间中还有剩余行，则返回 D 之前的命令，重新执行，如果 D 后，模式空间中没有任何内容，则将退出。   
//D 匹配空行执行D,如果上句s没有匹配到,//也无法匹配到空行, "//D;"命令结束  
s/.//; # D结束后,删除开头的 \n
```
**awk判断**
```sh
awk '{print ($1>$2)?"第一排"$1:"第二排"$2}' # 条件判断 括号代表if语句判断 "?"代表then ":"代表else  
awk '{max=($1>$2)? $1 : $2; print max}' # 条件判断 如果$1大于$2,max值为为$1,否则为$2  
awk '{if ( $6 > 50) print $1 " Too high" ;\  
else print "Range is OK"}' file  
awk '{if ( $6 > 50) { count++;print $3 } \  
else { x+5; print $2 } }' file

awk循环  
awk '{i = 1; while ( i <= NF ) { print NF, $i ; i++ } }' file  
awk '{ for ( i = 1; i <= NF; i++ ) print NF,$i }' file  
  
  
awk '/Tom/' file # 打印匹配到得行  
awk '/^Tom/{print $1}' # 匹配Tom开头的行 打印第一个字段  
awk '$1 !~ /ly$/' # 显示所有第一个字段不是以ly结尾的行  
awk '$3 <40' # 如果第三个字段值小于40才打印  
awk '$4==90{print $5}' # 取出第四列等于90的第五列  
awk '/^(no|so)/' test # 打印所有以模式no或so开头的行  
awk '$3 * $4 > 500' # 算术运算(第三个字段和第四个字段乘积大于500则显示)  
awk '{print NR" "$0}' # 加行号  
awk '/tom/,/suz/' # 打印tom到suz之间的行  
awk '{a+=$1}END{print a}' # 列求和  
awk 'sum+=$1{print sum}' # 将$1的值叠加后赋给sum  
awk '{a+=$1}END{print a/NR}' # 列求平均值  
awk -F'[ :\t]' '{print $1,$2}' # 以空格、:、制表符Tab为分隔符  
awk '{print "'"$a"'","'"$b"'"}' # 引用外部变量  
awk '{if(NR==52){print;exit}}' # 显示第52行  
awk '/关键字/{a=NR+2}a==NR {print}' # 取关键字下第几行  
awk 'gsub(/liu/,"aaaa",$1){print $0}' # 只打印匹配替换后的行  
ll | awk -F'[ ]+|[ ][ ]+' '/^$/{print $8}' # 提取时间,空格不固定  
awk '{$1="";$2="";$3="";print}' # 去掉前三列  
echo aada:aba|awk '/d/||/b/{print}' # 匹配两内容之一  
echo aada:abaa|awk -F: '$1~/d/||$2~/b/{print}' # 关键列匹配两内容之一  
echo Ma asdas|awk '$1~/^[a-Z][a-Z]$/{print }' # 第一个域匹配正则  
echo aada:aaba|awk '/d/&&/b/{print}' # 同时匹配两条件  
awk 'length($1)=="4"{print $1}' # 字符串位数  
awk '{if($2>3){system ("touch "$1)}}' # 执行系统命令  
awk '{sub(/Mac/,"Macintosh",$0);print}' # 用Macintosh替换Mac  
awk '{gsub(/Mac/,"MacIntosh",$1); print}' # 第一个域内用Macintosh替换Mac  
awk -F '' '{ for(i=1;i<NF+1;i++)a+=$i ;print a}' # 多位数算出其每位数的总和.比如 1234， 得到 10  
awk '{ i=$1%10;if ( i == 0 ) {print i}}' # 判断$1是否整除(awk中定义变量引用时不能带 $ )  
awk 'BEGIN{a=0}{if ($1>a) a=$1 fi}END{print a}' # 列求最大值 设定一个变量开始为0，遇到比该数大的值，就赋值给该变量，直到结束  
awk 'BEGIN{a=11111}{if ($1<a) a=$1 fi}END{print a}' # 求最小值  
awk '{if(A)print;A=0}/regexp/{A=1}' # 查找字符串并将匹配行的下一行显示出来，但并不显示匹配行  
awk '/regexp/{print A}{A=$0}' # 查找字符串并将匹配行的上一行显示出来，但并不显示匹配行  
awk '{if(!/mysql/)gsub(/1/,"a");print $0}' # 将1替换成a，并且只在行中未出现字串mysql的情况下替换  
awk 'BEGIN{srand();fr=int(100*rand());print fr;}' # 获取随机数  
awk '{if(NR==3)F=1}{if(F){i++;if(i%7==1)print}}' # 从第3行开始，每7行显示一次  
awk '{if(NF<1){print i;i=0} else {i++;print $0}}' # 显示空行分割各段的行数  
echo +null:null |awk -F: '$1!~"^+"&&$2!="null"{print $0}' # 关键列同时匹配  
awk -v RS=@ 'NF{for(i=1;i<=NF;i++)if($i) printf $i;print ""}' # 指定记录分隔符  
awk '{b[$1]=b[$1]$2}END{for(i in b){print i,b[i]}}' # 列叠加  
awk '{ i=($1%100);if ( $i >= 0 ) {print $0,$i}}' # 求余数  
awk '{b=a;a=$1; if(NR>1){print a-b}}' # 当前行减上一行  
awk '{a[NR]=$1}END{for (i=1;i<=NR;i++){print a[i]-a[i-1]}}' # 当前行减上一行  
awk -F: '{name[x++]=$1};END{for(i=0;i<NR;i++)print i,name[i]}' # END只打印最后的结果,END块里面处理数组内容  
awk '{sum2+=$2;count=count+1}END{print sum2,sum2/count}' # $2的总和 $2总和除个数(平均值)  
awk 'BEGIN{ "date" | getline d; split(d,mon) ; print mon[2]}' file # 将date值赋给d，并将d设置为数组mon，打印mon数组中第2个元素  
awk 'BEGIN{info="this is a test2010test!";print substr(info,4,10);}' # 截取字符串(substr使用)  
awk 'BEGIN{info="this is a test2010test!";print index(info,"test")?"ok":"no found";}' # 匹配字符串(index使用)  
awk 'BEGIN{info="this is a test2010test!";print match(info,/[0-9]+/)?"ok":"no found";}' # 正则表达式匹配查找(match使用)  
awk 'BEGIN{info="this is a test";split(info,tA," ");print length(tA);for(k in tA){print k,tA[k];}}' # 字符串分割(split使用)  
awk '{for(i=1;i<=4;i++)printf $i""FS; for(y=10;y<=13;y++) printf $y""FS;print ""}' # 打印前4列和后4列  
awk '{for(i=1;i<=NF;i++) a[i,NR]=$i}END{for(i=1;i<=NF;i++) {for(j=1;j<=NR;j++) printf a[i,j] " ";print ""}}' # 将多行转多列  
awk 'BEGIN{printf "what is your name?";getline name < "/dev/tty" } $1 ~name {print "FOUND" name " on line ", NR "."} END{print "see you," name "."}' file # 两文件匹配  
cat 1.txt|awk -F" # " '{print "insert into user (user,password,email)values(""'\''"$1"'\'\,'""'\''"$2"'\'\,'""'\''"$3"'\'\)\;'"}' >>insert_1.txt # 处理sql语句  
```
**取本机IP**
```sh
/sbin/ifconfig |awk -v RS="Bcast:" '{print $NF}'|awk -F: '/addr/{print $2}'  
/sbin/ifconfig |awk -v RS='inet addr:' '$1!="eth0"&&$1!="127.0.0.1"{print $1}'|awk '{printf"%s|",$0}'  
/sbin/ifconfig |awk '{printf("line %d,%s\n",NR,$0)}' # 指定类型(%d数字,%s字符)  
```
**查看磁盘空间**
```sh
df -h|awk -F"[ ]+|%" '$5>14{print $5}'  
df -h|awk 'NR!=1{if ( NF == 6 ) {print $5} else if ( NF == 5) {print $4} }'   
df -h|awk 'NR!=1 && /%/{sub(/%/,"");print $(NF-1)}'  
df -h|sed '1d;/ /!N;s/\n//;s/ \+/ /;' #将磁盘分区整理成一行 可直接用 df -P
```
**排列打印**
```sh
awk 'END{printf "%-10s%-10s\n%-10s%-10s\n%-10s%-10s\n","server","name","123","12345","234","1234"}' txt  
awk 'BEGIN{printf "|%-10s|%-10s|\n|%-10s|%-10s|\n|%-10s|%-10s|\n","server","name","123","12345","234","1234"}'  
awk 'BEGIN{  
print " *** 开 始 *** ";  
print "+-----------------+";  
printf "|%-5s|%-5s|%-5s|\n","id","name","ip";  
}  
$1!=1 && NF==4{printf "|%-5s|%-5s|%-5s|\n",$1,$2,$3" "$11}  
END{  
print "+-----------------+";  
print " *** 结 束 *** "  
}' txt
```


**老男孩awk经典题**

>分析图片服务日志，把日志（每个图片访问次数*图片大小的总和）排行，也就是计算每个url的总访问大小  
说明：本题生产环境应用：这个功能可以用于IDC网站流量带宽很高，然后通过分析服务器日志哪些元素占用流量过大，进而进行优化或裁剪该图片，压缩js等措施。  
本题需要输出三个指标： 【被访问次数】 【访问次数*单个被访问文件大小】 【文件名（带URL）】  
测试数据  
59.33.26.105 - - [08/Dec/2010:15:43:56 +0800] "GET /static/images/photos/2.jpg HTTP/1.1" 200 11299 

```sh
awk '{array_num[$7]++;array_size[$7]+=$10}END{for(i in array_num) {print array_num[i]" "array_size[i]" "i}}'  
```

**awk练习题**

    wang 4  
    cui 3  
    zhao 4  
    liu 3  
    liu 3  
    chang 5  
    li 2

1 通过第一个域找出字符长度为4的  
2 当第二列值大于3时，创建空白文件，文件名为当前行第一个域$1 (touch $1)  
3 将文档中 liu 字符串替换为 hong  
4 求第二列的和  
5 求第二列的平均值  
6 求第二列中的最大值  
7 将第一列过滤重复后，列出每一项，每一项的出现次数，每一项的大小总和

1、字符串长度  

    awk 'length($1)=="4"{print $1}'

2、执行系统命令  

    awk '{if($2>3){system ("touch "$1)}}'  
3、gsub(/r/,"s",域) 在指定域(默认$0)中用s替代r (sed 's///g')  

    awk '{gsub(/liu/,"hong",$1);print $0}' a.txt  
4、列求和  

    df -h | awk '{a+=$2}END{print a}'  
5、列求平均值  

    df -h | awk '{a+=$2}END{print a/NR}'  
    df -h | awk '{a+=$2;b++}END{print a,a/b}'   
6、列求最大值  

    df -h | awk 'BEGIN{a=0}{if($2>a) a=$2 }END{print a}'  
7、将第一列过滤重复列出每一项，每一项的出现次数，每一项的大小总和  

    awk '{a[$1]++;b[$1]+=$2}END{for(i in a){print i,a[i],b[i]}}'  



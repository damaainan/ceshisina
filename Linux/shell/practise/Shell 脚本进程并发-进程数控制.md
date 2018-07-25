## Shell 脚本进程并发&amp;进程数控制

来源：[http://www.cnblogs.com/hehehe886/p/9150418.html](http://www.cnblogs.com/hehehe886/p/9150418.html)

时间 2018-06-07 14:31:00



Shell 都以 **`串行`** 的方式自上而下执行命令，不适用需要大量作业的场景。

学习此篇shell脚本进程并发，能够大大提高工作效率~

通过wait 和 & 后台符号 可以实现并行，但无法控制进程数。

```
{
    task
}&
done
wait
```


{} 将主执行程序变为一个块，使用&放入后台

wait 函数等待所有后台进程执行程序，否则继续执行后续命令直到整个脚本结束

  
## 通过有名管道控制并发进程数

创建一个fifo文件, 作为进程池, 里面存放一定数目的"令牌".作业运行规则如下: 所有作业排队依次领取令牌; 每个作业运行前从进程池中领取一块令牌, 完成后再归还令牌; 当进程池中没有令牌时, 要运行的作业只能等待. 这样就能保证同时运行的作业数等于令牌数.


管道= = 》有名管道、无名（匿名）管道

有名管道：mkfilo 创建一个管道文件

有名管道： "cat 1.file | grep 'xxx' " "|" ==》创建一个无名管道，直接作为两个进程的数据通道

exec自行定义，绑定文件操作符


系统默认三个文件操作符 0、1、2 = = 》 stdin、stdout、stderr

ls /proc/self/fd

模板

```
#!/bin/bash
trap "exec 6>&-;exec 6<&-;wxit 0" 2
#接受2 程序终止(interrupt)信号 "ctrl c" 后的操作。关闭fd6 
tmp_fifofile=/tmp/$$.fifo        //$$ 进程pid  
mkfifo $tmp_fifofile             //创建为进程pid的管道
                               //我常用$((RANDOM))，大概率的避免与已有文件重复
exec 6<>$tmp_fifofile           //以6为文件描述符fd打开管道 <>代表读写
rm $tmp_fifofile
thread=250                      //定义并发进程数量，上文的令牌数量
#在fd6中放入$thread 个空行作为令牌
for ((i=0; i<=$thread;i++))
do
  echo
done >&6
for i in ``                              //可以省略，直接在{}括号内添加执行命令
do
    read -u6                             //read 读取行，领取令牌              
    {


    echo >& 6                            //归还令牌
}&                                       //{ }&放入后台
done
wait                                     //等待所有后台子进程结束
exec 6>&-                                //关闭fd6
exec 6<&-                                //关闭fd6
```


## 结束

学术不精。欢迎评论一起讨论！~


附上一个自己写的使用并发，检查大批量站点的域名检测脚本

将待检查的脚本放入指定目录就行了~

```
#!/bin/bash

#创建今日目录
if [ ! -d "./$(date +%y-%m-%d)" ];then
    mkdir -p /script/$(date +%y-%m-%d)
fi
dir=/script/$(date +%y-%m-%d)

function global()
{
#第一次curl检测
tmp_fifofile=/tmp/$(($RANDOM%1000)).fifo
mkfifo $tmp_fifofile
exec 6<>$tmp_fifofile
rm $tmp_fifofile
thread=256
for ((i=0; i<=$thread;i++))
do
   echo
done >&6

for ((i=0;i<=$thread;i++))
do
    echo >&6
done

for i  in `cat /script/domain/$url`
do
   read -u6
   {
   code=$(curl -o /dev/null --retry 2 --connect-timeout 10 -s -w %{http_code} $i)
   echo "$code $i" >> $dir/$url-first.log
   echo >& 6
}&

done
wait
exec 6>&-
exec 6<&-
grep -v '200\|301\|302'  $dir/$url-first.log  |tail -n +2  |awk -F' ' '{print $2}' > $dir/$url-second.log
rm -f $dir/$url-first.log  
#第二次wget检测
tmp_fifofile=/tmp/$(($RANDOM%1000)).fifo
mkfifo $tmp_fifofile
exec 6<>$tmp_fifofile
rm $tmp_fifofile
thread=128
for ((i=0; i<=$thread;i++))
do
   echo >&6
done

for i in `cat $dir/$url-second.log`
do
   read -u6
   {
    wget -T 10 --spider -t 2 $i &>/dev/null $i >> /dev/null
    if [ $? = 0 ];then
     echo $i >> /dev/null
    else
     echo $i >> $dir/$url-third.log
    fi
   echo >& 6
}&
done
wait
exec 6>&-
exec 6<&-
rm -f $dir/$url-second.log

#第三次curl检测
tmp_fifofile=/tmp/$(($RANDOM%1000)).fifo
mkfifo $tmp_fifofile
exec 6<>$tmp_fifofile
rm $tmp_fifofile
thread=128
for ((i=0; i<=$thread;i++))
do
   echo >&6
done

for i  in `cat $dir/$url-third.log`
do
   read -u6
   {
   code=$(curl -o /dev/null --retry 2 --connect-timeout 10 -s -w %{http_code} $i)
   echo "$code $i" >> $dir/$url-fourth.log
   echo >& 6
}&

done
wait
exec 6>&-
grep -v '200\|301\|302'  $dir/$url-fourth.log  |tail -n +2   >> $dir/last.log
rm -f $dir/$url-third.log
rm -f $dir/$url-fourth.log

}

function last (){
grep -v '200\|301\|302' $dir/last.log |awk -F' ' '{print $2}' >> $dir/last2.log 
rm -f $dir/last.log
tmp_fifofile=/tmp/last.fifo
mkfifo $tmp_fifofile
exec 6<>$tmp_fifofile
rm $tmp_fifofile
thread=64
for ((i=0; i<=$thread;i++))
do
   echo
done >&6

for ((i=0;i<=$thread;i++))
do
    echo >&6
done


for i  in `cat $dir/last2.log`
do
   read -u6
   {
   code=$(curl -o /dev/null --retry 2 --connect-timeout 10 -s -w %{http_code} $i)
   echo "$code $i" >> $dir/last3.log
   echo >& 6
}&
done
wait
exec 6>&-
exec 6<&-
rm -f $dir/last2.log
echo "请手动复核以下域名：" > $dir/$(date +%H-00)domain.log
grep -v '200\|301\|302' $dir/last3.log >> $dir/$(date +%H-00)domain.log
rm -f $dir/last3.log
}


function main ()
{
tmp_fifofile=/tmp/main.fifo
mkfifo $tmp_fifofile
exec 8<>$tmp_fifofile
rm $tmp_fifofile
thread=2
for ((i=0; i<=$thread;i++))
do
   echo
done >&8

for url in `ls -l /script/domain/ | tail -n +2 | awk -F' ' '{print $9}'`
do
  read -u8
{
  global $url
  echo >& 8
}&

done
wait
exec 8>&-
exec 8<&-

}

main
last
mail -s "检测结果来自xx服务器 :" xxxxxxxx@qq.com < $dir/$(date +%H-00)domain.log
```



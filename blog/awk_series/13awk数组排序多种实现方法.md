[awk数组排序多种实现方法][0]

由于awk数组，是关联数组。for…in循环输出时候，默认打印出来是无序数组。

    [chengmo@localhost ~]$ awk 'BEGIN{info = "this is a test";split(info,tA," ");for(k in tA){print k,tA[k];}}'  
    4 test  
    1 this  
    2 is  
    3 a

如果需要按照顺序输出，通过键值定位方式输出。

    [chengmo@localhost ~]$ awk 'BEGIN{info = "this is a test";slen=split(info,tA," ");for(i=1;i<=slen;i++){print i,tA[i];}}'   
    1 this  
    2 is  
    3 a  
    4 test 

**一、通过内置函数（asort,asorti使用)** awk 3.1以上版本才支持

1. > asort使用说明

> srcarrlen=asort[srcarr,dscarr] 默认返回值是：原数组长度，传入参数dscarr则将排序后数组赋值给dscarr.

```
[chengmo@localhost ~]$ awk 'BEGIN{
    a[100]=100;
    a[2]=224;
    a[3]=34;
    slen=asort(a,tA);
    for(i=1;i<=slen;i++)
    {print i,tA[i];}
}'
1 34
2 100
3 224
```

> asort只对值进行了排序，因此丢掉原先键值。

> 2、asorti 使用说明

```
[chengmo@localhost ~]$ awk 'BEGIN{
a["d"]=100;
a["a"]=224;
a["c"]=34;
slen=asorti(a,tA);
for(i=1;i<=slen;i++)
{print i,tA[i],a[tA[i]];}
}'
1 a 224
2 c 34
3 d 100
```

> asorti对键值 进行排序（字符串类型），将生成新的数组放入：tA中。

**二、通过管道发送到sort排序**

```
[chengmo@localhost ~]$awk 'BEGIN{
a[100]=100;
a[2]=224;
a[3]=34;
for(i in a)
{print i,a[i] | "sort -r -n -k2";}
}'
2 224
100 100
3 34
```

> 通过管道，发送到外部程序“sort”排序，`-r` 从大到小，`-n` 按照数字排序，`-k2` 以第2列排序。通过将数据丢给第3方的sort命令，所有问题变得非常简单。如果以key值排序 `–k2` 变成 `-k1`即可。

```
[chengmo@localhost ~]$ awk 'BEGIN{
a[100]=100;
a[2]=224;
a[3]=34;
for(i in a)
{print i,a[i] | "sort -r -n -k1";}
}'
100 100
3 34
2 224
```

**三、自定义排序函数**

* **awk自定义函数结构：**

```
function funname(p1,p2,p3)

{

    staction;

    return value;

}
```

> 以上是：awk自定义函数表示方式，默认传入参数都是以引用方式传入，return值，只能是字符型或者数值型。 不能返回数组类型。 如果返回数组类型。需要通过形参 方式传入。再获得。

> **awk返回数组类型**

```bash
awk 'function test(ary){
 for(i=0;i<10;i++){
  ary[i]=i;
 }
 return i;
}
BEGIN{
 n=test(array);
 for(i=0;i < n;i++){
  print array[i];
 }
}
'
```

* **排序函数**

> #arr 传入一维数组 

> #key 排序类型 1是按照值排序 2按照键值 

> #datatype 比较类型 1按照数字排序 2按照字符串排序 

> #tarr 排序返回的数组 

> #splitseq 分割字符串 数组中键与值之间分割字符串 

> #return 数组长度 

> #实现思路，将原始数组a[‘a’]=100 排序后变成 a[1]=a分隔符100 ，然后按照下标递归显示内容。 本排序使用冒泡方式进行。 

```
function sortArr(arr,key,datatype,tarr,splitseq)
{

    if(key ~ /[^1-2]/) 
    {return tarr;}
    for(k in arr)
    {
      tarr[++alen]=(k""splitseq""arr[k]);
    }

    for(m=1;m<=alen;m++)
    {
        for(n=1;n<=alen-m-1;n++)
        {
            split(tarr[m],tm,splitseq);
            split(tarr[n+1],tn,splitseq);

                tnum=tarr[m];
            if(datatype==1)
            {
                if(tm[key]+0<tn[key]+0)
                {
                     tarr[m]=tarr[n+1];
                     tarr[n+1]=tnum;
                }
            }
            else
            {
                if((tm[key]"") < (tn[key]""))
                {
                     tarr[m]=tarr[n+1];
                     tarr[n+1]=tnum;
                }
            }
        }
    }
    return alen;
}
```

> **完整代码如下：**

```shell
[chengmo@centos5 ~]$ awk 'BEGIN{
a["a"]=100;
a["b"]=110;
a["c"]=10;
splitseq="%%";
alen=sortArr(a,2,1,tarr,splitseq);
for(m=1;m<=alen;m++)
{
    split(tarr[m],ta,splitseq);
    print m,ta[1],ta[2];
}
}
function sortArr(arr,key,datatype,tarr,splitseq)
{

    if(key ~ /[^1-2]/) 
    {return tarr;}
    for(k in arr)
    {
      tarr[++alen]=(k""splitseq""arr[k]);
    }

    for(m=1;m<=alen;m++)
    {
        for(n=1;n<=alen-m-1;n++)
        {
            split(tarr[m],tm,splitseq);
            split(tarr[n+1],tn,splitseq);

                tnum=tarr[m];
            if(datatype==1)
            {
                if(tm[key]+0<tn[key]+0)
                {
                     tarr[m]=tarr[n+1];
                     tarr[n+1]=tnum;
                }
            }
            else
            {
                if((tm[key]"") < (tn[key]""))
                {
                     tarr[m]=tarr[n+1];
                     tarr[n+1]=tnum;
                }
            }
        }
    }
    return alen;
}
'
```

1 b 110
2 a 100
3 c 10

以上是awk数组排序一些方法。对于少量数据排序，就性能而言，使用自定义函数性能要高，不需要另外再开启进程。对于大量数据，排序第2种方法还是很不错的。

[0]: http://www.cnblogs.com/chengmo/archive/2010/10/09/1846696.html
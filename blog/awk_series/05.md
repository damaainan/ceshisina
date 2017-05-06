[linux awk数组操作详细介绍][0]

用awk进行文本处理，少不了就是它的数组处理。那么awk数组有那些特点，一般常见运算又会怎么样呢。我们先看下下面的一些介绍，结合例子我们会讲解下它的不同之处。在 awk 中数组叫做关联数组(associative arrays)，因为下标记可以是数也可以是串。awk 中的数组不必提前声明，也不必声明大小。数组元素用 0 或空串来初始化，这根据上下文而定。例如：

**一、定义方法**

1：可以用数值作数组索引(下标)

    Tarray[1]=“cheng mo”
    Tarray[2]=“800927”
    
    

2：可以用字符串作数组索引(下标)

    Tarray[“first”]=“cheng ”
    Tarray[“last”]=”mo”
    Tarray[“birth”]=”800927”
    
    

使用中 print Tarray[1] 将得到”cheng mo” 而 print Tarray[2] 和 print[“birth”] 都将得到 ”800927” 。 

**二、数组相关函数**

    [chengmo@localhost ~]$ awk --version  
    GNU Awk 3.1.5 

    使用版本是：3.1以上，不同版本下面函数不一定相同

* 得到数组长度（**length方法使用**）
```
[chengmo@localhost ~]$ awk 'BEGIN{info="it is a test";lens=split(info,tA," ");print length(tA),lens;}'
4 4

length返回字符串以及数组长度，split进行分割字符串为数组，也会返回分割得到数组长度。

 

(asort使用）:

[chengmo@localhost ~]$ awk 'BEGIN{info="it is a test";split(info,tA," ");print asort(tA);}'
4

asort对数组进行排序，返回数组长度。
```
* 输出数组内容(无序，有序输出）：
```
[chengmo@localhost ~]$ awk 'BEGIN{info="it is a test";split(info,tA," ");for(k in tA){print k,tA[k];}}'  
4 test  
1 it  
2 is  
3 a 

for…in 输出，因为数组是关联数组，默认是无序的。所以通过for…in 得到是无序的数组。如果需要得到有序数组，需要通过下标获得。

[chengmo@localhost ~]$ awk 'BEGIN{info="it is a test";tlen=split(info,tA," ");for(k=1;k<=tlen;k++){print k,tA[k];}}'   
1 it  
2 is  
3 a  
4 test 

注意：数组下标是从1开始，与c数组不一样。
```
* 判断键值存在以及删除键值：
**> 一个错误的判断方法**> ：
```
[chengmo@localhost ~]$ awk 'BEGIN{tB["a"]="a1";tB["b"]="b1";if(tB["c"]!="1"){print "no found";};for(k in tB){print k,tB[k];}}'   
no found  
a a1  
b b1  
c 

以上出现奇怪问题，tB[“c”]没有定义，但是循环时候，发现已经存在该键值，它的值为空，这里需要注意，awk数组是关联数组，只要通过数组引用它的key，就会自动创建改序列.
```
**正确判断方法：**
```
[chengmo@localhost ~]$ awk 'BEGIN{tB["a"]="a1";tB["b"]="b1";if( "c" in tB){print "ok";};for(k in tB){print k,tB[k];}}'   
a a1  
b b1 

if(key in array) 通过这种方法判断数组中是否包含”key”键值。
```
**删除键值：**
```
[chengmo@localhost ~]$ awk 'BEGIN{tB["a"]="a1";tB["b"]="b1";delete tB["a"];for(k in tB){print k,tB[k];}}'   
b b1 

delete array[key]可以删除，对应数组key的，序列值。
```
**三、二维数组使用(多维数组使用）**
```
awk的多维数组在本质上是一维数组，更确切一点，awk在存储上并不支持多维数组。awk提供了逻辑上模拟二维数组的访问方式。例 如，array[2,4] = 1这样的访问是允许的。awk使用一个特殊的字符串SUBSEP (\034)作为分割字段，在上面的例子中，关联数组array存储的键值实际上是2\0344。

类似一维数组的成员测试，多维数组可以使用 if ( (i,j) in array)这样的语法，但是下标必须放置在圆括号中。  
类似一维数组的循环访问，多维数组使用 for ( item in array )这样的语法遍历数组。与一维数组不同的是，多维数组必须使用split()函数来访问单独的下标分量。split ( item, subscr, SUBSEP) 

[chengmo@localhost ~]$ awk 'BEGIN{ 

for(i=1;i<=9;i++)
{
  for(j=1;j<=9;j++)  
  {
tarr[i,j]=i*j;
print i,"*",j,"=",tarr[i,j];
  }
}
}' 
1 * 1 = 1  
1 * 2 = 2  
1 * 3 = 3  
1 * 4 = 4  
1 * 5 = 5  
1 * 6 = 6 

…… 

可以通过array[k,k2]引用获得数组内容. 

方法二： 

[chengmo@localhost ~]$ awk 'BEGIN{  
for(i=1;i<=9;i++)
{
  for(j=1;j<=9;j++)  
  {
tarr[i,j]=i*j;
  }
}
for(m in tarr)              
{

split(m,tarr2,SUBSEP);
print tarr2[1],"*",tarr2[2],"=",tarr[m];
}
}'
}'
```

[0]: http://www.cnblogs.com/chengmo/archive/2010/10/08/1846190.html
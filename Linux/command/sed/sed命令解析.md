## sed命令n，N，d，D，p，P，h，H，g，G，x解析

<font face=微软雅黑>

## 1、
sed执行模板=sed '模式{命令1;命令2}'
即逐行读入模式空间，执行命令，最后输出打印出来
## 2、
为方便下面，先说下`p和P`，`p`打印当前模式空间内容，追加到默认输出之后，`P`打印当前模式空间开端至`\n`的内容，并追加到默认输出之前。   

sed并不对每行末尾`\n`进行处理，但是对N命令追加的行间`\n`进行处理，因为此时sed将两行看做一行。
#### 2-1、n命令

n命令简单来说就是提前读取下一行，覆盖模型空间前一行（并没有删除，因此依然打印至标准输出），如果命令未执行成功（并非跳过：前端条件不匹配），则放弃之后的任何命令，并对新读取的内容，重头执行sed。  
例子：   
从aaa文件中取出偶数行  
```
cat aaa 
This is 1    
This is 2    
This is 3    
This is 4    
This is 5    
     
sed -n 'n;p' aaa         //-n表示隐藏默认输出内容    
This is 2    
This is 4
```
> 注释：读取This is 1，执行n命令，此时模式空间为This is 2，执行p，打印模式空间内容This is 2，之后读取 This is 3，执行n命令，此时模式空间为This is 4，执行p，打印模式空间内容This is 4，之后读取This is 5，执行n 命令，因为没有了，所以退出，并放弃p命令。

因此，最终打印出来的就是偶数行。
 
#### 2-2、N命令
N命令简单来说就是追加下一行到模式空间，同时将两行看做一行，但是两行之间依然含有\n换行符，如果命令未执行成功（并非跳过：前端条件不匹配），则放弃之后任何命令，并对新读取的内容，重头执行sed。  
例子：  
从aaa文件中读取奇数行  
```
cat aaa   
This is 1   
This is 2   
This is 3   
This is 4   
This is 5   
                                                     
sed -n '$!N;P' aaa            
This is 1   
This is 3   
This is 5
```
注释中1代表This is 1   2代表This is 2  以此类推
注释：读取1，$!条件满足（不是尾行），执行N命令，得出1\n2，执行P，打印得1，读取3，$!条件满足（不是尾行），执行N命令，得出3\n4，执行P，打印得3，读取5，$!条件不满足，跳过N，执行P，打印得5  
 
#### 2-3、d命令
d命令是删除当前模式空间内容（不在传至标准输出），并放弃之后的命令，并对新读取的内容，重头执行sed。  
d命令例子  
从aaa文件中取出奇数行  
```
cat aaa   
This is 1   
This is 2   
This is 3   
This is 4   
This is 5   
                                                           
sed 'n;d' aaa           
This is 1   
This is 3   
This is 5
```
注释：读取1，执行n，得出2，执行d，删除2，得空，以此类推，读取3，执行n，得出4，执行d，删除4，得空，但是读取5时，因为n无法执行，所以d不执行。因无-n参数，故输出1\n3\n5   

#### 2-4、D命令

D命令是删除当前模式空间开端至\n的内容（不在传至标准输出），放弃之后的命令，但是对剩余模式空间重新执行sed。  
D命令例子  
从aaa文件中读取最后一行   
```
cat aaa   
This is 1   
This is 2   
This is 3   
This is 4   
This is 5   
                                                
sed 'N;D' aaa           
This is 5
```
注释：读取1，执行N，得出1\n2，执行D，得出2，执行N，得出2\n3，执行D，得出3，依此类推，得出5，执行N，条件失败退出，因无-n参数，故输出5  
 
#### 2-5、y命令
y命令的作用在于字符转换  
y命令例子：  
将aaa文件内容大写  
```
sed 'y/his/HIS/' aaa  
THIS IS 1  
THIS IS 2  
THIS IS 3  
THIS IS 4  
THIS IS 5
```

此外，如果需要对某个字符串进行大小写转换，则可使用如下方法  
```
cat ddd   
This is a and a is 1   
This is b and b is 2   
This is c and c is 3   
This is d and d is 4   
This is e and e is 5   
    
sed 's/\b[a-z]\b/\u&/g' ddd   
This is A and A is 1   
This is B and B is 2   
This is C and C is 3   
This is D and D is 4   
This is E and E is 5
```

#### 2-6、h命令，H命令，g命令，G命令
h命令是将当前模式空间中内容覆盖至保持空间，H命令是将当前模式空间中的内容追加至保持空间  
g命令是将当前保持空间中内容覆盖至模式空间，G命令是将当前保持空间中的内容追加至模式空间  
命令例子：  
将ddd文件中数字和字母互换，并将字母大写  
```
cat ddd.sed
h  
{  
s/.*is \(.*\) and .*/\1/  
y/abcde/ABCDE/
G  
s/\(.*\)\n\(.*is \).*\(and \).*\(is \)\(.*\)/\2\5 \3\5 \4\1/  
}  
                                           
sed -f ddd.sed ddd  
This is 1 and 1 is A  
This is 2 and 2 is B  
This is 3 and 3 is C  
This is 4 and 4 is D  
This is 5 and 5 is E
```
注释：读取1，执行h，复制到保持空间，执行s，模式空间得到匹配到的字母a，然后执行y，将a转成A，执行G，追加保持空间内容到模式空间，得  
A\nThis is a and a is 1；执行s，重新排列，得出This is 1 and 1 is A；以此类推，得出结果。  
这里需要注意的是匹配的内容中，空格一定要处理好，空格处理不对，会造成第二次s匹配错误，无法执行重新排列或排列错误  

#### 2-7、x命令
x命令是将当前保持空间和模式空间内容互换   

</font>
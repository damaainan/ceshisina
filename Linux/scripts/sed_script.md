### Sed 直接修改文件

sed最常用的用法莫过于替换文件，然而其默认的模式是直接输出在shell中

    sed 's/Old/New/' My_File.txt

如果我们想要sed直接在文件中更改，只需要在sed后面添加 -i 或 -ig即可

    sed -i 's/Old/New' My_File.txt

在mac中需要添加 -ig才能够执行～ anyway，很简单的，试一下就好了～

----


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


     
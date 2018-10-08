

awk '!array[$1]++' file.txt 第一列去重


输出成对符号中间的多行内容

按目录新建文件

    awk '{print $0}' 00.md | awk -F'*' '{if(NR<10){print "0"NR""$1}else{print NR""$1}}' | xargs -I[ touch "[.md"

添加标题号

    ls | grep "第" | xargs -i[ awk -F'*' 'NR==1{system("sed -i \"s/"$1"/### "$1"/\" \"[\"")}' [ 

添加引用

     ls | grep "第" | xargs -I[ sed -i '16s/^/> /' [

批量用文件内容重命名

    ls *.md | awk -F'[b.]' '{print $2}' | xargs -I[ awk 'NR==1{system("mv github[.md ["$2".md")}' "github"[.md

    ls *b1.md | awk -F'[b.]' '{print $2}' | xargs -I[ awk 'NR==1{if(length($2) ==1){system("mv github[.md 0["$2".md")}else{system("mv github[.md ["$2".md")}}' "github"[.md

重命名带空格的文件  `注意双引号的位置`

    ls *.md | xargs -I[ awk -F'## ' 'NR==1{system("mv [ \""$2".md\"")}' [

    ls *.md | xargs -I[ awk -F'## ' 'NR==1{print "["$2}' [ | awk -F'.md' '{system("mv "$1".md \""$1$2".md\"")}'

#### 批量修改文件名及语言标识

```sh
sed -i 's@~~~@```@' *.md 

# 加序号

awk -F"[" '{print $2}' toc.md | awk -F']' '{print FNR"**"$1}' | awk -F'**' '{system("mv \""$2".md\" \""$1""$2".md\"")}'
```


```sh 
#!/bin/bash
# echo $IFS

# 改变奇数行的语言标志

MY_SAVEIFS=$IFS  # 改变分隔符
# IFS=$(echo -en "\n\b")  
IFS=$'\n'  
# echo $IFS
for i in `ls *.md`
do
    # echo $i
    name=$i
    echo $name
    awk '/```/{i++;if(i%2==1)print NR}' "${name}" | xargs -I[ sed -i '[s@```@```c@' "${name}"
done

IFS=$MY_SAVEIFS  
# echo $IFS
```



    awk -F': ' '/\.\/img/{print $2}' cnblogs*.md | awk -F'-' '{system("sed -i \"s@"$0"@"$1"_"$NF"@\" cnblogs*.md")}'

#### 补全文件名 并下载

    awk -F': ' '/upload/{print $2}' 51*.md | awk -F'[-?]' '{if(index($3,"png"))print $3;else print $3".png"}'

    awk -F': ' '/upload/{print $2}' 51*.md | awk -F'[-?]' '{if(index($3,"png"))system("aria2c -o "$3" "$0);else system("aria2c -o "$3".png "$0)}'
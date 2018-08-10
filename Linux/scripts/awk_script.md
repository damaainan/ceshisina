

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
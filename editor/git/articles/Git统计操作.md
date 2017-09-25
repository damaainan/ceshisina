# Git统计操作

 时间 2017-05-16 09:26:11  

原文[http://yelog.org/2017/05/16/Git统计操作/][1]

```shell
    # 统计当前作者今天（从凌晨1点开始）提交次数
    $ git log --author="$(git config --get user.name)" --no-merges --since=1am --stat
    
    # 按提交作者统计，按提交次数排序
    $ git shortlog -sn
    $ git shortlog --numbered --summary
    
    # 只看某作者提交的commit数
    $ git log --author="faker" --oneline --shortstat
    
    # 按提交作者统计，提交数量排名前5（看全部，去掉head管道即可）
    $ git log --pretty='%aN' | sort | uniq -c | sort -k1 -n -r | head -n 5
    
    # 按提交者邮箱统计，提交数量排名前5
    $ git log --pretty=format:%ae | gawk -- '{ ++c[$0]; } END { for(cc in c) printf "%5d %s\n",c[cc],cc; }' | sort -u -n -r | head -n 5
    
    # 统计贡献者数量
    $ git log --pretty='%aN' | sort -u | wc -l
    
    # 统计提交数量
    $ git log --oneline | wc -l
```

## 按代码行数统计 
```shell
    # 统计指定作者增删行数
    $ git log --author="faker" --pretty=tformat: --numstat | awk '{ add += $1; subs += $2; loc += $1 - $2 } END { printf "added lines: %s, removed lines: %s, total lines: %s\n", add, subs, loc }' -
    
    # 统计当前作者增删行数
    $ git log --author="$(git config --get user.name)" --pretty=tformat: --numstat | gawk '{ add += $1 ; subs += $2 ; loc += $1 - $2 } END { printf "added lines: %s removed lines : %s total lines: %s\n",add,subs,loc }' -
    
    # 统计所有邮箱前缀的增删行数 -英文版
    $ git log --shortstat --pretty="%cE" | sed 's/\(.*\)@.*/\1/' | grep -v "^$" | awk 'BEGIN { line=""; } !/^ / { if (line=="" || !match(line, $0)) {line = $0 "," line }} /^ / { print line " # " $0; line=""}' | sort | sed -E 's/# //;s/ files? changed,//;s/([0-9]+) ([0-9]+ deletion)/\1 0 insertions\(+\), \2/;s/\(\+\)$/\(\+\), 0 deletions\(-\)/;s/insertions?\(\+\), //;s/ deletions?\(-\)//' | awk 'BEGIN {name=""; files=0; insertions=0; deletions=0;} {if ($1 != name && name != "") { print name ": " files " files changed, " insertions " insertions(+), " deletions " deletions(-), " insertions-deletions " net"; files=0; insertions=0; deletions=0; name=$1; } name=$1; files+=$2; insertions+=$3; deletions+=$4} END {print name ": " files " files changed, " insertions " insertions(+), " deletions " deletions(-), " insertions-deletions " net";}'
    
    # 统计所有邮箱前缀的增删行数 -中文版
    $ git log --shortstat --pretty="%cE" | sed 's/\(.*\)@.*/\1/' | grep -v "^$" | awk 'BEGIN { line=""; } !/^ / { if (line=="" || !match(line, $0)) {line = $0 "," line }} /^ / { print line " # " $0; line=""}' | sort | sed -E 's/# //;s/ files? changed,//;s/([0-9]+) ([0-9]+ deletion)/\1 0 insertions\(+\), \2/;s/\(\+\)$/\(\+\), 0 deletions\(-\)/;s/insertions?\(\+\), //;s/ deletions?\(-\)//' | awk 'BEGIN {name=""; files=0; insertions=0; deletions=0;} {if ($1 != name && name != "") { print name ": " files " 个文件被改变, " insertions " 行被插入(+), " deletions " 行被删除(-), " insertions-deletions " 行剩余"; files=0; insertions=0; deletions=0; name=$1; } name=$1; files+=$2; insertions+=$3; deletions+=$4} END {print name ": " files " 个文件被改变, " insertions " 行被插入(+), " deletions " 行被删除(-), " insertions-deletions " 行剩余";}'
    
    # 统计所有作者增删行数 --英文版
    $ git log --format='%aN' | sort -u | while read name; do echo -en "$name\t"; git log --author="$name" --pretty=tformat: --numstat | awk '{ add += $1; subs += $2; loc += $1 - $2 } END { printf "added lines: %s, removed lines: %s, total lines: %s\n", add, subs, loc }' -; done
    
    # 统计所有作者增删行数 --中文版
    $ git log --format='%aN' | sort -u | while read name; do echo -en "$name\t"; git log --author="$name" --pretty=tformat: --numstat | awk '{ add += $1; subs += $2; loc += $1 - $2 } END { printf "添加行数: %s, 删除行数: %s, 总行数: %s\n", add, subs, loc }' -; done
```

## git log 说明
git log 参数说明：

-- author 指定作者 

-- stat 显示每次更新的文件修改统计信息，会列出具体文件列表 

-- shortstat 统计每个commit 的文件修改行数，包括增加，删除，但不列出文件列表： 

-- numstat 统计每个commit 的文件修改行数，包括增加，删除，并列出文件列表： 

-p 选项展开显示每次提交的内容差异，用 -2 则仅显示最近的两次更新

例如：git log -p -2

-- name-only 仅在提交信息后显示已修改的文件清单 

-- name-status 显示新增、修改、删除的文件清单 

-- abbrev-commit 仅显示 SHA-1 的前几个字符，而非所有的 40 个字符 

-- relative-date 使用较短的相对时间显示（比如，“2 weeks ago”） 

-- graph 显示 ASCII 图形表示的分支合并历史 

-- pretty 使用其他格式显示历史提交信息。可用的选项包括 oneline，short，full，fuller 和 format（后跟指定格式） 

例如： 

`git log --pretty=oneline ;`  

`git log --pretty=short ;` 

`git log --pretty=full ;` 

git log –pretty=fuller

-- pretty=tformat: 可以定制要显示的记录格式，这样的输出便于后期编程提取分析 

例如： 

`git log --pretty=format:""%h - %an, %ar : %s""`  
下面列出了常用的格式占位符写法及其代表的意义。

选项 说明

%H 提交对象（commit）的完整哈希字串

%h 提交对象的简短哈希字串

%T 树对象（tree）的完整哈希字串

%t 树对象的简短哈希字串

%P 父对象（parent）的完整哈希字串

%p 父对象的简短哈希字串

%an 作者（author）的名字

%ae 作者的电子邮件地址

%ad 作者修订日期（可以用 -date= 选项定制格式）

%ar 作者修订日期，按多久以前的方式显示

%cn 提交者(committer)的名字

%ce 提交者的电子邮件地址

%cd 提交日期

%cr 提交日期，按多久以前的方式显示

%s 提交说明

-- since 限制显示输出的范围， 

例如： `git log --since=2.weeks` 显示最近两周的提交 

选项 说明

-(n) 仅显示最近的 n 条提交

-- since, -- after 仅显示指定时间之后的提交。 

-- until, -- before 仅显示指定时间之前的提交。 

-- author 仅显示指定作者相关的提交。 

-- committer 仅显示指定提交者相关的提交。 

一些例子： 

`git log --until=1.minute.ago` // 一分钟之前的所有 log 

`git log --since=1.day.ago` //一天之内的log 

`git log --since=1.hour.ago` //一个小时之内的 log 

`git log --since=1.month.ago --until=2.weeks.ago` //一个月之前到半个月之前的log 

`git log --since ==2013-08.01 --until=2013-09-07` //某个时间段的 log 

git blame 看看某一个文件的相关历史记录 

例如： `git blame index.html --date short`


[1]: http://yelog.org/2017/05/16/Git统计操作/

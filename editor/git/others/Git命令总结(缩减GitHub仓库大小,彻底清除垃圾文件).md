## Git命令总结(缩减GitHub仓库大小,彻底清除垃圾文件)

2018.02.23 22:51*

来源：[https://www.jianshu.com/p/6217949e87a3](https://www.jianshu.com/p/6217949e87a3)


          
## 1.初始化

```
git init 初始化仓库,新建一个Git仓库(新建了一个隐藏目录.git)
　　
把远程仓库克隆到本地
git clone git@github.com:lifegh/lifegh.github.io.git
git clone git@git.coding.net:lifec/lifec.git

把本地仓库关联到远程仓库
git remote add github git@github.com:lifegh/lifegh.github.io.git
git remote add coding git@git.coding.net:lifec/lifec.git

远程仓库别名
如果git clone一个远程仓库, Git会自动添加url,别名为origin
git remote      列出远程仓库别名    
git remote -v   远程仓库别名对应的实际url
git remote add [alias] [url]   添加一个新远程仓库
git remote rm [alias]          删除远程仓库别名
git remote rename [old-alias] [new-alias]   重命名
git remote set-url [alias] [url]   更改url,可以加上—push和fetch参数,为同一个别名set不同地址

```
## 2.常用

```
git status
git add .
git commit -m "注释..."
git push -u origin master
git fetch origin master
git pull origin master

git pull = git fetch + git merge

特殊符号:
~<n>相当于连续的<n>个^
^代表父提交,当一个提交有多个父提交时,可以通过在^后面跟上一个数字,表示第几个父提交 ^相当于^1

```
## 3.日志/回滚重置

```
git log --name-only --oneline fileName
git log --oneline --number  每条log只显示一行,显示number条
git log --oneline --graph   图形化显示分支合并历史
git log branchname          显示特定分支
git log --decorate
git log --author=[author name] 指定作者的提交历史.
git log --since --before --until --after  根据提交时间筛选
git log --grep 根据commit信息过滤
git log --stat 改动信息     
    
git reflog
    reflog记录分支变化或者HEAD引用变化, 当git reflog不指定引用时, 默认列出HEAD的reflog,
    HEAD@{0}代表HEAD当前的值, HEAD@{3}代表HEAD在3次变化之前的值,
    git会将变化记录到HEAD对应的reflog文件中, 其路径为.git/logs/HEAD, 分支reflog文件都放在.git/logs/refs的子目录

git show commitID
git diff
    不加参数: show diff of unstaged changes.

    git diff --cached 命令
        已经暂存的文件和上次提交之间的差异
        
    git diff HEAD
        show diff of all staged or unstated changes.
        
git checkout commitID fileName
git revert
git reset --hard

git分为三个区域: 
    1.工作区(working directry)
    2.暂缓区(stage index)   
    3.历史记录区(history)
    
git reset --mixed id  history变了(提交记录变了),但staged 和 working没变  (默认方式)
git reset --soft id   history变了(提交记录变了)和staged区都变了,但working没变
git reset --hard id   全都变了

变化范围:
soft (history) < mixed (history + stage) < hard (history + stage + working)

```
## 4.分支

```
git branch -v  每一个分支的最后一次提交.
git branch     列出本地所有分支,当前分支会被星号标示出 
git branch mybranch        创建分支
git branch -D mybranch     删除分支 

git checkout mybranch      切换分支
git checkout -b mybranch   创建并切换分支
git rebase master          把master分支更新到当前分支
git merge mybranch         分支合并

git push [remote-name] :branch-name 删除远程分支

```
## 5.彻底清除垃圾文件,缩减git仓库

```
参考GitHub官网: https://help.github.com/articles/removing-sensitive-data-from-a-repository

例如, 我的jekyll博客代码库提交了大量mp3文件, 使用下面命令清除后代码库由233M缩小为1.3M, 提交到GitHub部署博客轻快飞速
# 清除垃圾文件(大量无用的mp3文件)
git filter-branch --force --index-filter 'git rm --cached --ignore-unmatch *.mp3' --prune-empty --tag-name-filter cat -- --all

# 提交到远程仓库(如GitHub, 我再次从git clone GitHub代码库会变小为1.3M)
git push origin --force --all

# 必须回收垃圾,本地仓库才变小
git for-each-ref --format='delete %(refname)' refs/original | git update-ref --stdin    
git reflog expire --expire=now --all
git gc --prune=now

rm -rf .git/refs/original
git reflog expire --expire=now --all
git gc --prune=now
git gc --aggressive --prune=now

```

简书: [http://www.jianshu.com/p/6217949e87a3][0]

CSDN博客: [http://blog.csdn.net/qq_32115439/article/details/79357615][1]

GitHub博客: [http://lioil.win/2018/02/23/Git-Cmd.html][2]

Coding博客: [http://c.lioil.win/2018/02/23/Git-Cmd.html][3]


[0]: https://www.jianshu.com/p/6217949e87a3
[1]: https://link.jianshu.com?t=http%3A%2F%2Fblog.csdn.net%2Fqq_32115439%2Farticle%2Fdetails%2F79357615
[2]: https://link.jianshu.com?t=http%3A%2F%2Flioil.win%2F2018%2F02%2F23%2FGit-Cmd.html
[3]: https://link.jianshu.com?t=http%3A%2F%2Fc.lioil.win%2F2018%2F02%2F23%2FGit-Cmd.html
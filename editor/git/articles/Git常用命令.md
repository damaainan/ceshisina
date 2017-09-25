# Git常用命令


创建时间:2016-10-02 17:15

 字数:2,410  阅读: 123   

 原文: [常用Git 命令清单- 阮一峰的网络日志][2]

1. 新建版本仓库
1. 配置
1. 增加/删除文件
1. 代码提交
1. 分支
1. 标签
1. 查看信息/搜索
1. 远程同步
1. 撤销
1. 其他

经常用到Git，但是很多命令记不住，将其整理于此。（大量摘自网络）

一般来说，日常使用只要记住下图6个命令，就可以了。但是熟练使用，恐怕要要记住60~100个命令。

![git常用命令][13]

 git常用命令

  
  
下面整理的 Git 命令清单。几个专业名词的译名如下。

    Workspace：工作区
    Index / Stage：暂存区
    Repository：仓库区（本地仓库）
    Remote：远程仓库
    

## 新建版本仓库

```shell
    # 在当前目录新建一个Git代码库
    $ git init
    
    # 新建一个目录，将其初始化为Git代码库
    $ git init [project-name]
    
    # 下载一个项目和它的整个代码历史, -o 给远程仓库起名:faker,默认origin
    $ git clone [-o faker] [url]
```

## 配置

Git的设置文件为`.gitconfig`，它可以在用户主目录下（全局配置），也可以在项目目录下（项目配置）。

```shell
    # 显示当前的Git配置
    $ git config --list
    
    # 编辑Git配置文件
    $ git config -e [--global]
    
    # 设置提交代码时的用户信息
    $ git config [--global] user.name "[name]"
    $ git config [--global] user.email "[email address]"
    
    # 设置大小写敏感（windows不区分大小写的解决办法）
    $ git config core.ignorecase  false
```

## 增加/删除文件

```shell
    # 添加指定文件到暂存区
    $ git add [file1] [file2] ...
    
    # 添加指定目录到暂存区，包括子目录
    $ git add [dir]
    
    # 添加当前目录的所有文件到暂存区
    $ git add .
    
    # 添加每个变化前，都会要求确认
    # 对于同一个文件的多处变化，可以实现分次提交
    $ git add -p
    
    # 删除工作区文件，并且将这次删除放入暂存区
    $ git rm [file1] [file2] ...
    
    # 停止追踪指定文件，但该文件会保留在工作区
    $ git rm --cached [file]
    
    # 改名文件，并且将这个改名放入暂存区
    $ git mv [file-original] [file-renamed]
```

## 代码提交

```shell
    # 提交暂存区到仓库区
    $ git commit -m [message]
    
    # 提交暂存区的指定文件到仓库区
    $ git commit [file1] [file2] ... -m [message]
    
    # 提交工作区自上次commit之后的变化，直接到仓库区
    $ git commit -a
    
    # 提交时显示所有diff信息
    $ git commit -v
    
    # 使用一次新的commit，替代上一次提交
    # 如果代码没有任何新变化，则用来改写上一次commit的提交信息
    $ git commit --amend -m [message]
    
    # 重做上一次commit，并包括指定文件的新变化
    $ git commit --amend [file1] [file2] ...
```

## 分支

```shell
    # 列出所有本地分支
    $ git branch
    
    # 列出所有远程分支
    $ git branch -r
    
    # 列出所有本地分支和远程分支
    $ git branch -a
    
    # 列出所有本地分支，并展示没有分支最后一次提交的信息
    $ git branch -v
    
    # 列出所有本地分支，并展示没有分支最后一次提交的信息和远程分支的追踪情况
    $ git branch -vv
    
    # 列出所有已经合并到当前分支的分支
    $ git branch --merged
    
    # 列出所有还没有合并到当前分支的分支
    $ git branch --no-merged
    
    # 新建一个分支，但依然停留在当前分支
    $ git branch [branch-name]
    
    # 新建一个分支，并切换到该分支
    $ git checkout -b [branch]
    
    # 新建一个与远程分支同名的分支，并切换到该分支
    $ git checkout --track [branch-name]
    
    # 新建一个分支，指向指定commit
    $ git branch [branch] [commit]
    
    # 新建一个分支，与指定的远程分支建立追踪关系
    $ git branch --track [branch] [remote-branch]
    
    # 切换到指定分支，并更新工作区
    $ git checkout [branch-name]
    
    # 切换到上一个分支
    $ git checkout -
    
    # 建立追踪关系，在现有分支与指定的远程分支之间
    $ git branch --set-upstream-to=[remote-branch]
    $ git branch --set-upstream [branch] [remote-branch] # 已被弃用
    
    # 合并指定分支到当前分支
    $ git merge [branch]
    
    # 中断此次合并（你可能不想处理冲突）
    $ git merge --abort
    
    # 选择一个commit，合并进当前分支
    $ git cherry-pick [commit]
    
    # 删除分支
    $ git branch -d [branch-name]
    
    #新增远程分支 远程分支需先在本地创建,再进行推送
    $ git push origin [branch-name]
    
    # 删除远程分支
    $ git push origin --delete [branch-name]
    $ git branch -dr [remote/branch]
```

## 标签

```shell
    # 列出所有tag
    $ git tag
    
    # 新建一个tag在当前commit
    $ git tag [tag]
    
    # 新建一个tag在指定commit
    $ git tag [tag] [commit]
    
    # 删除本地tag
    $ git tag -d [tag]
    
    # 删除远程tag
    $ git push origin :refs/tags/[tagName]
    
    # 查看tag信息
    $ git show [tag]
    
    # 提交指定tag
    $ git push [remote] [tag]
    
    # 提交所有tag
    $ git push [remote] --tags
    
    # 新建一个分支，指向某个tag
    $ git checkout -b [branch] [tag]
```

## 查看信息/搜索

```shell
    # 显示有变更的文件
    $ git status [-sb] #s:short,给一个短格式的展示，b:展示当前分支
    
    # 显示当前分支的版本历史
    $ git log
    
    # 显示commit历史，以及每次commit发生变更的文件
    $ git log --stat
    
    # 搜索提交历史，根据关键词
    $ git log -S [keyword]
    
    # 显示某个commit之后的所有变动，每个commit占据一行
    $ git log [tag] HEAD --pretty=format:%s
    
    # 显示某个commit之后的所有变动，其"提交说明"必须符合搜索条件
    $ git log [tag] HEAD --grep feature
    
    # 显示某个文件的版本历史，包括文件改名
    $ git log --follow [file]
    $ git whatchanged [file]
    
    # 显示指定文件相关的每一次diff
    $ git log -p [file]
    
    # 显示过去5次提交
    $ git log -5 --pretty --oneline
    
    # 显示在分支2而不在分支1中的提交
    $ git log [分支1]..[分支2]
    $ git log ^[分支1] [分支2]
    $ git log [分支2] --not [分支1]
    
    # 显示两个分支不同时包含的提交
    $ git log [分支1]...[分支2]
    
    # 显示所有提交过的用户，按提交次数排序
    $ git shortlog -sn
    
    # 显示指定文件是什么人在什么时间修改过
    $ git blame [file]
    
    # 显示暂存区和工作区的差异
    $ git diff
    
    # 显示暂存区和上一个commit的差异
    $ git diff --cached [file]
    
    # 显示工作区与当前分支最新commit之间的差异
    $ git diff HEAD
    
    # 显示两次提交之间的差异
    $ git diff [first-branch]...[second-branch]
    
    # 显示今天你写了多少行代码
    $ git diff --shortstat "@{0 day ago}"
    
    # 显示某次提交的元数据和内容变化
    $ git show [commit]
    
    # 显示某次提交发生变化的文件
    $ git show --name-only [commit]
    
    # 显示某次提交时，某个文件的内容
    $ git show [commit]:[filename]
    
    # 显示当前分支的最近几次提交
    $ git reflog
    
    # 搜索你工作目录的文件，输出匹配行号
    $ git grep -n [关键字]
    
    # 搜索你工作目录的文件，输出每个文件包含多少个匹配
    $ git grep --count [关键字]
    
    # 优化阅读
    $ git grep --break --heading [关键字]
    
    # 查询iCheck这个字符串那次提交的
    $ git log -SiCheck --oneline
    
    # 查询git_deflate_bound函数每一次的变更
    $ git log -L :git_deflate_bound:zlib.c
```

## 远程同步

```shell
    # 下载远程仓库的所有变动 [shortname] 为远程仓库的shortname, 如origin,为空时:默认origin
    $ git fetch [shortname]
    
    # 显示所有远程仓库
    $ git remote -v
    
    #显式地获得远程引用的完整列表 [shortname] 为远程仓库的shortname, 如origin,为空时:默认origin
    $ git ls-remote [shortname]
    
    # 显示某个远程仓库的信息 [remote] 为远程仓库的shortname, 如origin
    $ git remote show [shortname]
    
    # 增加一个新的远程仓库，并命名
    $ git remote add [shortname] [url]
    
    # 重命名一个远程仓库（shortname）
    $ git remote rename [旧仓库名] [新仓库名]
    
    # 删除一个远程链接
    $ git remote rm [shortname] [url]
    $ git remote remove [shortname] [url]
    
    # 修改远程仓库地址
    $ git remote set-url [shortname] [url]
    
    # 取回远程仓库的变化，并与本地分支合并
    $ git pull [remote] [branch]
    
    # 上传本地当前分支到远程仓库
    git push [remote]
    
    # 上传本地指定分支到远程仓库
    $ git push [remote] [branch]
    
    # 上传本地所有分支到远程仓库
    $ git push -all [remote]
    
    # 强行推送当前分支到远程仓库，即使有冲突
    $ git push [remote] --force
    
    # 推送所有分支到远程仓库
    $ git push [remote] --all
```

## 撤销

```shell
    # 恢复暂存区的指定文件到工作区
    $ git checkout [file]
    
    # 恢复某个commit的指定文件到暂存区和工作区
    $ git checkout [commit] [file]
    
    # 恢复暂存区的所有文件到工作区
    $ git checkout .
    
    #只会保留源码（工作区），回退commit(本地仓库)与index（暂存区）到某个版本
    $ git reset <commit_id>   #默认为 --mixed模式
    $ git reset --mixed <commit_id>
    
    #保留源码（工作区）和index（暂存区），只回退commit（本地仓库）到某个版本
    $ git reset --soft <commit_id>
    
    #源码（工作区）、commit（本地仓库）与index（暂存区）都回退到某个版本
    $ git reset --hard <commit_id>
    
    # 恢复到最后一次提交的状态
    $ git reset --hard HEAD
    
    # 新建一个commit，用来撤销指定commit
    # 后者的所有变化都将被前者抵消，并且应用到当前分支
    $ git revert [commit]
    
    # 将工作区和暂存区的代码全都存储起来了
    $ git stash [save]
    
    # 只保存工作区，不存储暂存区
    $ git stash --keep-index
    
    # 存储工作区、暂存区和未跟踪文件
    $ git stash -u
    $ git stash --include-untracked
    
    # 不存储所有改动的东西，但会交互式的提示那些改动想要被储藏、哪些改动需要保存在工作目录中
    $ git stash --patch
    
    # 不指定名字，Git认为指定最近的储藏，将存储的代码（工作区和暂存区）都应用到工作区
    $ git stash apply [stash@{2}]
    
    # 存储的工作区和暂存区的代码应用到工作区和暂存区
    $ git stash apply [stash@{2}] --index
    
    # 将存储的代码（工作区和暂存区）都应用到工作区，并从栈上扔掉他
    $ git stash pop
    
    # 删除stash@{2}的存储
    $ git stash drop [stash@{2}]
    
    # 获取储藏的列表
    $ git stash list
    
    # 移除工作目录中所有未跟踪的文件及口口那个的子目录，不会移除.gitiignore忽略的文件
    $ git clean -f -d
```

## 其他

```shell
    # 生成一个可供发布的压缩包
    $ git archive
```

[2]: http://www.ruanyifeng.com/blog/2015/12/git-cheat-sheet.html

[13]: ../img/FlWMWzIX9WE7PW-7eyeq8uaEJ_3p.png
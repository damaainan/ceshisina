# [Git操作之高手过招](http://yelog.org/2016/12/23/git-master/)

创建时间:2016-12-23 10:59

 字数:842  阅读: 32  

1. 最后一次commit信息写错了
1. 最后一次commit少添加一个文件
1. 最后一次commit多添加一个文件
1. 移除add过的文件
1. 回退本地commit（还未push）
1. 回退本地commit（已经push）
1. 回退单个文件的历史版本
1. 修改提交历史中的author和email
1. 忽略已提交的文件（.iml）

在使用git的过程中，总有一天你会遇到下面的问题：）  
这些也是在开发过程中很常见的问题，以下也是作者的经验之谈，有不对的地方还请指出。

### 最后一次commit信息写错了

如果只是提交信息写错了信息，可以通过以下命令单独修改提交信息

```shell
    $ git commit --amend
```

> **注意：** 通过这样的过程修改提交信息后，相当于删除原来的提交，重新提交了一次。所有如果你在修改前已经将错误的那次提交push到服务端，那在修改后就需要通过 git pull 来合并代码（类似于两个分支了）。  
> 通过 `git log --graph --oneline` 查看就会发现两个分支合并的痕迹

### 最后一次commit少添加一个文件

```shell
    $ git add file1
    $ git commit --amend
```
    

### 最后一次commit多添加一个文件

```shell
    $ git rm --cached file1
    $ git commit --amend
```

### 移除add过的文件

    #方法一
    $ git rm --cache [文件名]
    
    #方法二
    $ git reset head [文件/文件夹]
    

### 回退本地commit（还未push）

这种情况发生在你的本地仓库，可能你add，commit以后发现代码有点问题，打算取消提交，用到下面命令

```shell
    #只会保留源码（工作区），回退commit(本地仓库)与index（暂存区）到某个版本
    $ git reset <commit_id>   #默认为 --mixed模式
    $ git reset --mixed <commit_id>
    
    #保留源码（工作区）和index（暂存区），只回退commit（本地仓库）到某个版本
    $ git reset --soft <commit_id>
    
    #源码（工作区）、commit（本地仓库）与index（暂存区）都回退到某个版本
    $ git reset --hard <commit_id>
```

当然有人在push代码以后，也是用reset –hard回退代码到某个版本之前，但是这样会有一个问题，你线上的代码没有变化。

> !!!可以通过 git push –force 将本地的回退推送到服务端,但是除非你很清楚在这么做, 不推荐.

所以，这种情况你要使用下面的方式了。

### 回退本地commit（已经push）

对于已经把代码push到线上仓库,你回退本地代码其实也想同时回退线上代码,回滚到某个指定的版本,线上,线下代码保持一致.你要用到下面的命令

```shell
    $ git revert <commit_id>
```

**注意：**

1. git revert 用于反转提交，执行命令时要求工作树必须是干净的。
1. git revert 用一个新的提交来消除一个历时提交所做出的修改

### 回退单个文件的历史版本

    #查看历史版本
    git log 1.txt
    
    #回退该文件到指定版本
    git reset [commit_id] 1.txt
    git checkout 1.txt
    
    #提交
    git commit -m "回退1.txt的历史版本"
    

### 修改提交历史中的author和email

旧的：author:Old-Author email:old@mail.com  
新的：author:New-Author email:new@mail.com  
1.在git仓库内创建下面的脚本，如change.sh

```shell
    # !/bin/sh
    
    git filter-branch --env-filter '
    an="$GIT_AUTHOR_NAME"
    am="$GIT_AUTHOR_EMAIL"
    cn="$GIT_COMMITTER_NAME"
    cm="$GIT_COMMITTER_EMAIL"
    
    if [ "$GIT_COMMITTER_EMAIL" = "old@mail.com" ]
    then
        cn="New-Author"
        cm="new@mail.com"
    fi
    if [ "$GIT_AUTHOR_EMAIL" = "old@mail.com" ]
    then
        an="New-Author"
        am="new@mail.com"
    fi
    
    export GIT_AUTHOR_NAME="$an"
    export GIT_AUTHOR_EMAIL="$am"
    export GIT_COMMITTER_NAME="$cn"
    export GIT_COMMITTER_EMAIL="$cm"
    '
```

2.运行脚本

    $ sh change.sh
    

### 忽略已提交的文件（.iml）

1. 删除已提交的文件

```
    # 删除项目中所有的.iml后缀的文件
    $ find . -name "*.iml" | xargs rm -f
```

1. 添加.gitignore文件

```
    *.iml
    /**/*.iml
```


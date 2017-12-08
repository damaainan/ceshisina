## 常用命令 

    git clone 
    git pull
    git status

#### 保存现场

    git stash
    git stash list
    git stash pop

#### 1.删除无用的分支

    $ git branch -d <branch_name>

#### 2.删除无用的tag
    
    $ git tag -d <tag_name>

#### 3.**`清理本地版本库`**  合并 `.git` 目录对象

    $ git gc --prune=now

本地分支和远程分支没有建立联系--解决执行 git pull 报错

    git branch --set-upstream-to=origin/远程分支的名字 本地分支的名字

### 拉取远程分支并创建本地分支
#### 方法一
使用如下命令：

    git checkout -b 本地分支名x origin/远程分支名x

    git checkout -b jia_test origin/integration_apply_join  # 示例 
  避免从本地新建 
#### 方式二
使用如下命令：

    git fetch origin 远程分支名x:本地分支名x

### 远端 

    git fetch origin remotebranch[:localbranch]# 从远端拉去分支[到本地指定分支]
    
    git merge origin/branch#合并远端上指定分支
    
    git pull origin remotebranch:localbranch# 拉去远端分支到本地分支
    
    git push origin branch#将当前分支，推送到远端上指定分支
    git push origin localbranch:remotebranch#推送本地指定分支，到远端上指定分支
    git push origin :remotebranch # 删除远端指定分支
    git push origin remotebranch --delete # 删除远程分支
    git branch -dr branch # 删除本地和远程分支
    git checkout -b [--track] test origin/dev # 基于远端dev分支，新建本地test分支[同时设置跟踪]


## git status 显示中文乱码

    git config --global core.quotepath false
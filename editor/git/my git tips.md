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


**`git merge <branch>`** | **合并指定分支到当前分支**


###   git pull --ff-only 报错  `fatal: Not possible to fast-forward, aborting.`

解决办法 [https://adamcod.es/2014/12/10/git-pull-correct-workflow.html](https://adamcod.es/2014/12/10/git-pull-correct-workflow.html)
    
    git rebase --preserve-merges origin/master

#### git拉取远程分支到本地分支或者创建本地新分支

    git fetch origin branchname:branchname
    git fetch origin integration_apply_join:jia_apply_join_test_fix # 示例 将远程 集成分支 拉取到本地 其他分支    需要切换到 master 再执行，即可成功

可以把远程某各分支拉去到本地的branchname下，如果没有branchname，则会在本地新建branchname

    git checkout --track origin/remoteName -b localName

获取远程分支remoteName 到本地新分支localName，并跳到localName分支，这里加了--track，让创建的本地分支localName跟中远程的origin/remoteName分支。


### 拉取远程分支并创建本地分支
#### 方法一
使用如下命令：

    git checkout -b 本地分支名x origin/远程分支名x

    git checkout -b jia_test origin/integration_apply_join  # 示例 
    git checkout -b jia_query_orders_with_new_sheetcode origin/master
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

## 删除错误的 commit

假设你有3个commit如下：

    commit 3
    commit 2
    commit 1
    

其中最后一次提交commit 3是错误的，那么可以执行：

    git reset --hard HEAD~1
    

你会发现，HEAD is now at commit 2。

然后再使用`git push --force origi master` 将本次变更强行推送至服务器。这样在服务器上的最后一次错误提交也彻底消失了。

> 值得注意的是，这类操作比较比较危险，例如：在你的 commit 3 之后别人又提交了新的 commit 4 ，那在你强制推送之后，那位仁兄的 commit 4 也跟着一起消失了。

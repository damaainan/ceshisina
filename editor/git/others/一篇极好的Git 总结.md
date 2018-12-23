## 一篇极好的Git 总结

来源：[http://www.cnblogs.com/qcloud1001/p/10006556.html](http://www.cnblogs.com/qcloud1001/p/10006556.html)

时间 2018-11-23 11:37:00

 
欢迎大家前往[腾讯云+社区][7] ，获取更多腾讯海量技术实践干货哦~
 
本文由[腾讯工蜂][8] 发表于[云+社区专栏][9]
 
## 常用命令 
 
简单的，常用的命令也就几个。但是想非常熟练使用，怕是要记住几十个。
 
![][0]

 
* Workspace：工作区（clone或者原始内容） 
* Index/Stage：暂存区（有增删改查后add到临时区） 
* Repository：本地仓库（保存了本地的增删改查记录） 
* Remote：远程仓库（git.code.oa.com，本地的记录提交到远端，供团队所有人查看使用） 
 
 
## 有意思的事 
 
## 代码更新之Fetch vs Pull 
 
二者都是从远程拉取代码到本地

 
* fetch：只是拉取到本地 
* pull：不仅拉取到本地，还merge到本地分支中 
 
 
## 代码合流之Merge vs Rebase 

 
* rebase：用于把一个分支的修改合并到当前分支 
 
 
![][1]
 
假设远程分支上有2个提交，然后基于远程develop，再创建一个分支feature。
 
然后分别在两个分支上做两次提交。
 
![][2]
 
git merge
 
这时候，你可以用`pull`命令把`develop`分支上的修改拉下来并且和你的修改合并；结果看起来就像一个新的`和并提交`![][3]

```
git rebase


```

```
$ git checkout feature
$ git rebase
 develop
```

 
* 这些命令会把你的feature分支里的每个提交(commit)取消掉，并且把它们临时保存为补丁(patch)(这些补丁放到".git/rebase"目录中) 
* 然后把feature分支更新为最新的develop分支 
* 最后把保存的这些补丁应用到feature分支上 
* 当feature分支更新后，会指向最新的commit，临时存放的就会被删除掉 
 
 
![][4]
 
## 代码回滚之Reset、Revert、 Checkout 
 
![][5]

 
* Reset 
 
 
将一个分支的末端指向另一个提交，可以用来移除当前分支的一些提交。
 
文件层面上，将缓存区的文件同步到指定的那个提交。

```
$ git checkout develop
$ git reset HEAD~2

# 将当前的README.md从缓存区中移除出去
$ git reset HEAD README.md
```
 
![][6]
 
develop分支末端的两个提交就变成了悬挂提交
 
如果提交还没有push，git reset 是撤销commit的简单方法
 
除了在当前分支上操作，还可以通过其他参数来修改stage或者workspace

 
* --soft：stage和workspace都不会被改变 
* --mixed（默认）：stage和你指定的提交同步，但workspace不受影响 
* --hard：stage和workspace都同步到你指定的提交 
 

```
# 将当前的改动从stage中移除，但这些改动还保留在workspace中
$ git reset --mixed HEAD

# 完全舍弃没有提交的改动
$ git reset --hard HEAD
```

 
* Checkout 
 
 
提交层面上的checkout，可以切换分支，同一分支，可以切换当前HEAD。
 
文件层面上，不会移动HEAD指针，也不会切换到其他分支上，只是更改workspace，而不是stage。

```
# 将HEAD移到新的分支，然后更新工作目录
$ git checkout develop

# 将HEAD移动到当前commit的前两个commit上，同时更新workspace
$ git checkout HEAD~2

# 将workspace中的README.md同步到最新的提交
$ git checkout HEAD README.md
```

 
* Revert 
 
 
撤销一个提交的同时会创建一个新的提交。

```
# 产生一个新的commit用于撤销倒数第二个commit
$ git checkout develop
$ git revert HEAD~2
```

 
* 不会改变提交历史 
* revert可以用在公共分支，reset应该用在私有分支上 
* 如果提交已经push，想到达到撤销的目的，应该使用revert
 
| 命令 | 作用域 | 常用情景 |
| - | - | - | 
| git reset | 提交层面 | 在私有分支上舍弃一些没有提交的更改 | 
| git reset | 文件层面 | 将文件从缓存区中移除 | 
| git checkout | 提交层面 | 切换分支或查看旧版本 | 
| git checkout | 文件层面 | 舍弃工作目录中的更改 | 
| git revert | 提交层面 | 在公共分支上回滚更改 | 
| git revert | 文件层面 | （然而并没有） | 
 
 
## 代码暂存之Stash 
 `git stash`会把所有未提交的修改（包括暂存和未暂存的）都保存起来，用于日后恢复当前工作目录

 
* 保存一个不必要但日后又想查看的提交 
* 切换分支前先暂存，处理分支的其他事情 
 

```
$ git status
On branch develop
Changes to be committed:

new file:   README.md

Changes not staged for commit:

modified:   index.html

$ git stash
Saved working directory and index state WIP on master: 5002d47 ...

$ git status
On branch master
nothing to commit, working tree clean
```
 
stage是本地的，不会上传到git server
 
实际应用中，推荐给每个stash加一个message，使用`git stash save`取代`git stash`

```
$ git stash save "test stash"
Saved working directory and index state On autoswitch: test stash
HEAD 现在位于 296e8d4 remove unnecessary postion reset in onResume function
$ git stash list
stash@{0}: On autoswitch: test stash
```

 
* 可以使用`git stash list`命令，查看stash列表  
 

```
$ git stash list
stash@{0}: WIP on master: 049d078 stash_0
stash@{1}: WIP on master: c264051 stash_1
stash@{2}: WIP on master: 21d80a5 stash_2
```
 
\2. 使用`git stash apply`命令可以通过名字指定那个stash，默认指定最近的（stash@{0}）
 
\3. 使用`git stash pop`将stash中第一个stash删除，并将对应修改应用到当前的工作目录中
 
\4. 使用`git stash drop`，后面加上stash名，可以移除相应的stash；或者使用`git stash clear`清空所有stash
 
默认情况下，`git stash`会缓存：

 
* 添加到暂存区的修改（staged changes ） 
* Git跟踪但并未添加到暂存区的修改（unstaged changes） 
 
 
但不会缓存：

 
* 在工作目录中新的文件（untracked files） 
* 被忽略的文件（ignored files） 
 
 
此时，使用`-u`或者`--include-untracked`可以stash untracked 文件；使用`-a`或者`--all`可以stash当前目录下的所有修改（ **`慎用`**  ）
 
## 基础命令 
 
## 初始化 

```
# 在当前目录新建一个Git代码库
$ git init

# 新建一个目录，将其初始化为Git代码库
$ git init git_test

# 下载一个项目和它的整个代码历史
$ git clone http://git.code.oa.com/jaelintu/git_test
```
 
## 增加/删除文件 

```
# 添加指定文件到暂存区
$ git add file1 file2...

# 添加指定目录到暂存区，包括子目录
$ git add dir

# 添加当前目录的所有文件到暂存区
$ git add .

# 添加每个变化前，都会要求确认
# 对于同一个文件的多处变化，可以实现分次提交
$ git add -p

# 删除工作区文件，并且将这次删除放入暂存区
$ git rm file1 file2 ...
```
 
## 代码提交 

```
# 提交暂存区到仓库区
$ git commit -m "message"

# 提交暂存区的指定文件到仓库区
$ git commit file1 file2 ... -m "message"

# 提交工作区自上次commit之后的变化，直接到仓库区
$ git commit -a

# 提交时显示所有diff信息
$ git commit -v

# 使用一次新的commit，替代上一次提交
# 如果代码没有任何新变化，则用来改写上一次commit的提交信息
$ git commit --amend -m "message"

# 重做上一次commit，并包括指定文件的新变化
$ git commit --amend file1 file2 ...
```
 
## 分支 

```
# 列出所有本地分支
$ git branch

# 列出所有远程分支
$ git branch -r

# 列出所有本地分支和远程分支
$ git branch -a

# 新建一个分支，但依然停留在当前分支
$ git branch name

# 新建一个分支，并切换到该分支
$ git checkout -b branch

# 新建一个分支，指向指定commit
$ git branch name commit_SHA

# 新建一个分支，与指定的远程分支建立追踪关系
$ git branch --track name orgin/name

# 切换到指定分支，并更新工作区
$ git checkout name

# 切换到上一个分支
$ git checkout -

# 建立追踪关系，在现有分支与指定的远程分支之间
$ git branch --set-upstream name origin/name

# 合并指定分支到当前分支
$ git merge branch-name

# 选择一个commit，合并进当前分支
$ git cherry-pick commit_SHA

# 删除分支
$ git branch -d branch-name

# 删除远程分支
$ git push origin --delete branch-name
$ git branch -dr remote/branch
```
 
## tags 

```
# 列出所有tag
$ git tag

# 新建一个tag在当前commit
$ git tag tag-name

# 新建一个tag在指定commit
$ git tag tag-name commit-SHA

# 删除本地tag
$ git tag -d tag-name

# 删除远程tag
$ git push origin :refs/tags/tag-Name

# 查看tag信息
$ git show tag-name

# 提交指定tag
$ git push origin tag-name

# 提交所有tag
$ git push origin --tags

# 新建一个分支，指向某个tag
$ git checkout -b branch-name tag-name
```
 
## 查看信息 

```
# 显示有变更的文件
$ git status

# 显示当前分支的版本历史
$ git log

# 显示commit历史，以及每次commit发生变更的文件
$ git log --stat

# 搜索提交历史，根据关键词
$ git log -S [keyword]

# 显示某个commit之后的所有变动
$ git log (tag-name||commit-SHA) HEAD

# 显示某个文件的版本历史，包括文件改名
$ git log --follow file
$ git whatchanged file

# 显示指定文件相关的每一次diff
$ git log -p file

# 显示过去5次提交
$ git log -5 --pretty --oneline

# 显示所有提交过的用户，按提交次数排序
$ git shortlog -sn

# 显示指定文件是什么人在什么时间修改过
$ git blame file

# 显示暂存区和工作区的代码差异
$ git diff

# 显示暂存区和上一个commit的差异
$ git diff --cached file

# 显示工作区与当前分支最新commit之间的差异
$ git diff HEAD

# 显示两次提交之间的差异
$ git diff [first-branch]...[second-branch]

# 显示今天你写了多少行代码
$ git diff --shortstat "@{0 day ago}"

# 显示某次提交的元数据和内容变化
$ git show commit-SHA

# 显示某次提交发生变化的文件
$ git show --name-only commit-SHA

# 显示某次提交时，某个文件的内容
$ git show commit-SHA:filename

# 显示当前分支的最近几次提交
$ git reflog

# 从本地master拉取代码更新当前分支：branch 一般为master
$ git rebase
 branch-name
```
 
## 远程同步 

```
# 下载远程仓库的所有变动
$ git fetch origin

# 显示所有远程仓库
$ git remote -v

# 显示某个远程仓库的信息
$ git remote show origin

# 增加一个新的远程仓库，并命名
$ git remote add shortname url

# 取回远程仓库的变化，并与本地分支合并
$ git pull origin branch-name

# 上传本地指定分支到远程仓库
$ git push origin branch-name

# 强行推送当前分支到远程仓库，即使有冲突
$ git push origin --force

# 推送所有分支到远程仓库
$ git push origin --all
```
 
## 撤销 

```
# 恢复暂存区的指定文件到工作区
$ git checkout file

# 恢复某个commit的指定文件到暂存区和工作区
$ git checkout commit-SHA file

# 恢复暂存区的所有文件到工作区
$ git checkout .

# 重置暂存区的指定文件，与上一次commit保持一致，但工作区不变
$ git reset file

# 重置暂存区与工作区，与上一次commit保持一致
$ git reset --hard

# 重置当前分支的指针为指定commit，同时重置暂存区，但工作区不变
$ git reset commit-SHA

# 重置当前分支的HEAD为指定commit，同时重置暂存区和工作区，与指定commit一致
$ git reset --hard commit-SHA

# 重置当前HEAD为指定commit，但保持暂存区和工作区不变
$ git reset --keep commit-SHA

# 新建一个commit，用来撤销指定commit
# 后者的所有变化都将被前者抵消，并且应用到当前分支
$ git revert commit-SHA

# 暂时将未提交的变化移除，稍后再移入
$ git stash
$ git stash pop
```
 
## 冲突解决 
 
rebase过程中，也许会出现冲突（conflict）

```
git add
git rebase
 --continue
git rebase
 --abort

```

```
$ git rebase
 develop
CONFLICT (content): Rebase conflict in readme.txt
Automatic rebase failed; fix conflicts and then commit the result.

$ git status
On branch feature

You have unmerged paths.
  (fix conflicts and run "git rebase
 --continue")
  (use "git merge --abort" to abort the merge)

Unmerged paths:
  (use "git add <file>..." to mark resolution)

    both modified:   readme.txt

no changes added to commit (use "git add" and/or "git commit -a")
```
 
查看readme.md 内容

```
Git tracks changes of files.
<<<<<<< HEAD
Creating a new branch is quick & simple.
=======
Creating a new branch is quick AND simple.
>>>>>>> feature
```
 
选择保留`HEAD`或者`feature`的版本

```
Git tracks changes of files.
Creating a new branch is quick AND simple.
```
 
在提交：

```
$ git add readme.md
$ git rebase
 --contine
```
 
## 推荐的Git GUI工具 
 
\1. Source Tree（号称最好用）：特色支持git flow，一键创建工作流

 
* 免费功能 
* 强大：无论你是新手还是重度用户，SourceTree 都会让你觉得很顺手。对于非常重度用户，Source Tree还支持自定义脚本的执行 
* 同时支持 Windows 和 Mac 操作系统 
* 同时支持 Git 和 Mercurial 两种 VCS 
* 内置GitHub, BitBucket 和 Stash 的支持：直接绑定帐号即可操作远程repo 
 
 
\2. Tortoise git：文件的右键菜单很容易上手

 
* 免费 
* 只支持Windows：与文件管理器良好集成 
* 中文界面 
* 与Tortoise SVN相同的体验 
 
 
  
相关阅读
[【每日课程推荐】机器学习实战！快速入门在线广告业务及CTR相应知识][10] 
 
 
此文已由作者授权腾讯云+社区发布，更多原文请[点击][11]
 

[7]: https://cloud.tencent.com/developer/?fromSource=waitui
[8]: https://cloud.tencent.com/developer/user/3246935
[9]: https://cloud.tencent.com/developer/column/6041?fromSource=waitui
[10]: https://cloud.tencent.com/developer/edu/course-1128?fromSource=waitui
[11]: https://cloud.tencent.com/developer/article/1365571?fromSource=waitui
[0]: ../img/famuMnY.png 
[1]: ../img/A3miqiB.png 
[2]: ../img/viqiUv3.png 
[3]: ../img/umUVbyJ.png 
[4]: ../img/BVvu63Z.png 
[5]: ../img/z6VRR3b.png 
[6]: ../img/Iraye2v.png 
Command line instructions

# Git global setup
```
git config --global user.name "杨剑"
git config --global user.email "yangjian@miyabaobei.com"
```

# Create a new repository

```
git clone git@dev45.gitlab.miyabaobei.com:open/open.git
cd open
touch README.md
git add README.md
git commit -m "add README"
git push -u origin master
```

# Existing folder or Git repository

```
cd existing_folder
git init
git remote add origin git@dev45.gitlab.miyabaobei.com:open/open.git
git add .
git commit
git push -u origin master
```

# 如何创建一个基于历史提交的分支
```
git checkout -b branch_name a2b43f83
```

# 如何删除本地分支
```
git branch -d branch_name
```

# 如何删除远端分支
```
git push origin :branch_name
```

# 远端分支已经删除，本地还出现这个分支
```
git fetch origin -p
```

# reset 说明
模式名称 | HEAD的位置 | 索引 | 工作树
---- | ---- | ---- | ----
soft | 修改 | 不修改 | 不修改
mixed | 修改 | 修改 | 不修改
hard | 修改 | 修改 | 修改

* 主要使用的场合：
 * 复原修改过的索引的状态(mixed)
 * 彻底取消最近的提交(hard)
 * 只取消提交(soft)


# 如何撤销上一次提交 [高风险操作]
```
git reset ^1 --hard
```

# 如何清理工作区 [高风险操作]
```
git reset --hard HEAD
git clean -df
```

# 如何生成 git patch

> 要注意的是，如果master与Fix分支中间有多次提交，它会针对每次提交生成一个patch。

```
git format-patch hashofcommit
0001-.patch
```

# 如何使用 git patch
```
git checkout yangjian-fix-bug
git am 0001-.patch
gitk &
```

# 管理员使用命令

## 如何删除，已经合并到主干的远程分支 [高风险操作]
```
$ git checkout master
$ git branch -r --merged | grep -v master | sed  "s/origin\///" | xargs -i echo git push origin :{} | sh
```

## Remove local tags that are no longer on the remote repository

git < 2.0
```bash
$ git fetch <remote> --prune --tags
```

```bash
$ git --version
git version 2.1.3

$ git fetch --prune origin +refs/tags/*:refs/tags/*
From ssh://xxx
 x [deleted]         (none)     -> rel_test
```

## 按照提交者来统计,统计远端分支个数

```
git br -r | awk -F_ '{print $1}' | sort | uniq -c | sort -r
```

# Git常用命令

> 作者：王奥(OX)

> 链接：https://www.zhihu.com/question/22932048/answer/96992496

> 来源：知乎

> 著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。


**符号约定**

- `<xxx>` 自定义内容
- `[xxx]` 可选内容
- `[<xxx>]`自定义可选内容

``` bash
#初始设置
git config --global user.name "<用户名>" #设置用户名
git config --global user.email "<电子邮件>" #设置电子邮件

#本地操作
git add [-i] #保存更新，-i为逐个确认。
git status #检查更新。
git commit [-a] -m "<更新说明>" #提交更新，-a为包含内容修改和增删，-m为说明信息，也可以使用 -am。

#远端操作
git clone <git地址> #克隆到本地。
git fetch #远端抓取。
git merge #与本地当前分支合并。
git pull [<远端别名>] [<远端branch>] #抓取并合并,相当于第2、3步
git push [-f] [<远端别名>] [<远端branch>] #推送到远端，-f为强制覆盖
git remote add <别名> <git地址> #设置远端别名
git remote [-v] #列出远端，-v为详细信息
git remote show <远端别名> #查看远端信息
git remote rename <远端别名> <新远端别名> #重命名远端
git remote rm <远端别名> #删除远端
git remote update [<远端别名>] #更新分支列表

#分支相关
git branch [-r] [-a] #列出分支，-r远端 ,-a全部
git branch <分支名> #新建分支
git branch -b <分支名> #新建并切换分支
git branch -d <分支名> #删除分支
git checkout <分支名> #切换到分支
git checkout -b <本地branch> [-t <远端别名>/<远端分支>] #-b新建本地分支并切换到分支, -t绑定远端分支
git merge <分支名> #合并某分支到当前分支
```

# [Git常用命令](http://gityuan.com/2015/06/27/git-notes/)

## 一、概述
先用一幅图，从总体上描述主要git命令的工作流程

![image](/uploads/34253898aab53ce91f4512f9ba8ba84b/image.png)

* `workspace`: 本地的工作目录。（记作A）
* `index`：缓存区域，临时保存本地改动。（记作B）
* `local repository`: 本地仓库，只想最后一次提交HEAD。（记作C）
* `remote repository`：远程仓库。（记作D）

### 二、命令笔记
以下所有的命令的功能说明，都采用上述的标记的A、B、C、D的方式来阐述。

```bash
#初始化
git init # 创建
git clone /path/to/repository # 检出
git config --global user.email "you@example.com" # 配置email
git config --global user.name "Name" # 配置用户名
#操作
git add <file> # 文件添加，A → B
git add . # 所有文件添加，A → B

git commit -m "代码提交信息" # 文件提交，B → C
git commit --amend # 与上次commit合并, *B → C

git push origin master # 推送至master分支, C → D
git pull # 更新本地仓库至最新改动， D → A
git fetch # 抓取远程仓库更新， D → C

git log # 查看提交记录
git status # 查看修改状态
git diff # 查看详细修改内容
git show # 显示某次提交的内容
#撤销操作
git reset <file> # 某个文件索引会回滚到最后一次提交， C → B
git reset # 索引会回滚到最后一次提交， C → B
git reset --hard # 索引会回滚到最后一次提交， C → B → A

git checkout # 从index复制到workspace， B → A
git checkout -- files # 文件从index复制到workspace， B → A
git checkout HEAD -- files # 文件从local repository复制到workspace， C → A
#分支相关
git checkout -b branch_name # 创建名叫“branch_name”的分支，并切换过去
git checkout master # 切换回主分支
git branch -d branch_name # 删除名叫“branch_name”的分支
git push origin branch_name # 推送分支到远端仓库
git merge branch_name # 合并分支branch_name到当前分支(如master)
git rebase # 衍合，线性化的自动， D → A
#冲突处理
git diff # 对比workspace与index
git diff HEAD # 对于workspace与最后一次commit
git diff <source_branch> <target_branch> # 对比差异
git add <filename> # 修改完冲突，需要add以标记合并成功
#其他
gitk # 开灯图形化git
git config color.ui true # 彩色的 git 输出
git config format.pretty oneline # 显示历史记录时，每个提交的信息只显示一行
git add -i # 交互式添加文件到暂存区
```
## 常用命令 

    git clone 

    git pull

    git status


#### 1.删除无用的分支
    
    $ git branch -d <branch_name>

#### 2.删除无用的tag
    
    $ git tag -d <tag_name>

#### 3.**`清理本地版本库`**  合并 `.git` 目录对象
    
    $ git gc --prune=now


一些常用的git命令行使用小技巧

### 撤销上一次提交

撤销的前提是没有推送到远程

* 撤销上一次，不保留修改或者添加的文件：
```
    git reset —hard HEAD~
```
* 撤销上一次，保留修改或者添加到的文件：
```
    git reset —soft HEAD~
```
### 删除远程分支

* 删除本地对于远程分支的关联分支：
```
    git branch -r -d origin/branch_name
```
* 真正删除远程分支：
```
    git push origin :branch_name
```
### 清空工作目录文件

* 清除有版本跟踪且未提交暂存区，有修改的文件
```
    git checkout  - - .  //. 是指清除所有，如果是对应的文件名，则清除对应的文件
```
* 清除无版本跟踪的工作区文件：
```
    git clean -f      // 清除所有无版本跟踪文件，不包含文件夹
    git clean -fd     // 清除所有无版本跟踪的文件和文件夹
```
### 修改本地分支名称
```
    git branch -m <old_branch_name> <new_branch_name>
```
### 修改上次提交的日志

前提条件是提交未推送到远程仓库

    git commit --amend  // 直接编辑出来的文件即可，vim语法

### 如何提交空白目录

在要提交到git版本控制中的目录里面，添加一个文件：`.gitkeep` 即可：

    emptyFolder
        .gitkeep

### 设置命令行别名

很多同学也许遇到过，在操作git的过程中，很多命令使用很多次，但是命令本身很长，如status，branch等等，输入多了，就烦了；那么有没有只输入这些单词的缩写就可以了呢？答案是可以。  
我们有两种方案可以解决这个问题：

* 编辑仓库的`config`文件进行设置：  
编辑 `.git/config` 文件，在文件开头添加
```
      [alias]
         st = status
         br = branch
```
保存退出到仓库主目录，试试 git st 或者 git br看看效果吧。

* 使用命令行设置全局别名或者局部别名  
语法：`git config [--global] alias.别名 = '命令'`
如果不加上--global参数，则只设置当前仓库中的命令别名。  
还是上面的例子:
```
      git config --global alias.st = 'status'
    
      git config --global alias.br = 'branch'
```
设置完即可生效，试试 `git st` 或者 `git br`看看效果吧。


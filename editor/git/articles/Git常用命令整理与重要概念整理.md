## Git常用命令整理与重要概念整理

来源：[http://os.51cto.com/art/201805/574880.htm](http://os.51cto.com/art/201805/574880.htm)

时间 2018-05-29 15:53:01

 
#### Git重要概念
 
#### master head
 
每次提交，Git都把它们串成一条时间线，这条时间线就是一个分支。在Git里，有个分支叫主分支，即master分支。HEAD严格来说不是指向提交，而是指向master，master才是指向提交的，所以，HEAD指向的就是当前分支。
 
一开始的时候，master分支是一条线，Git用master指向最新的提交，再用HEAD指向master，就能确定当前分支，以及当前分支的提交点。
 
每次提交，master分支都会向前移动一步，这样，随着你不断提交，master分支的线也越来越长：
 
当我们创建新的分支，例如dev时，Git新建了一个指针叫dev，指向master相同的提交，再把HEAD指向dev，就表示当前分支在dev上。
 
从现在开始，对工作区的修改和提交就是针对dev分支了，比如新提交一次后，dev指针往前移动一步，而master指针不变。
 
假如我们在dev上的工作完成了，就可以把dev合并到master上。Git怎么合并呢？最简单的方法，就是直接把master指向dev的当前提交，就完成了合并。
 
合并完分支后，甚至可以删除dev分支。删除dev分支就是把dev指针给删掉，删掉后，我们就剩下了一条master分支。
 
#### 工作区，暂存区
 
 
* 工作区Workspace：就是你在电脑里能看到的目录，即你代码放的那个文件夹。即时性强，对文件的所有更改都会立刻提现在这里。 
* 版本库：工作区有一个隐藏目录.git，这个不算工作区，而是Git的版本库。 
* 暂存区 Index / Stage：git add以后，当前对文件的更改会保存到这个区 
* 本地仓库Repository：git commit以后，当前暂存区里对文件的更改会提交到本地仓库 
* 远程仓库Remote：远程仓库名一般叫origin。git push以后，本地仓库里优先于远程仓库的commit会被push到远程仓库 
 
 
![][0]
 
#### 下载安装
 
git官网下载
 
#### 初始化
 
#### 初始化参数
 
```
$ git config --global user.name "你的名字"  
$ git config --global user.email "你的邮箱地址"  


```
 
因为Git是分布式版本控制系统，所以，每个机器都必须自报家门：你的名字和Email地址。
 
注意git config命令的--global参数，用了这个参数，表示你这台机器上所有的Git仓库都会使用这个配置，当然也可以对某个仓库指定不同的用户名和Email地址。
 
初始化本地仓库
 
```
$ git init 


```
 
SSH key生成
 
```
$ ssh-keygen -t rsa -C "你的邮箱地址" 


```
 
clone代码
 
```
// 克隆master分支  
$ git clone <版本库的网址>  
// 指定克隆的分支名  
$ git clone -b <分支名> <版本库的网址>  


```
 
.gitignore生效办法
 
```
// 先把本地缓存删除（改变成未track状态）  
$ git rm -r --cached .  
// 然后再提交  
$ git add .  
$ git commit -m 'update .gitignore'  


```
 
查看各种状态
 
```
// 查看当前状态（分支名，有哪些改动，有哪些冲突，工作区暂存区中的内容，几个commit等等）  
$ git status  
// 查看本地仓库的提交历史  
$ git log  
// 查看本地仓库的提交历史，简洁版  
$ git log --pretty=oneline  
// 查看命令历史  
$ git reflog  


```
 
分支
 
```
// 查看分支： 
$ git branch -a  
// 创建本地分支：  
$ git branch <分支名>  
// 切换本地分支：  
$ git checkout <分支名>  
// 创建+切换本地分支：  
$ git checkout -b <name>  
// 合并某分支到当前分支： 
$ git merge <要合并的分支>
// 将本地分支推送到远程
$ git push origin <要推送的本地分支名>
// 以远程分支为基础，建一个本地分支
$ git checkout -b <本地分支名> origin/<远程分支名>
// 删除本地分支：
$ git branch -d <本地分支名>
// 删除远程分支。将本地空分支推送到远程分支,相当于删除远程分支
$ git push origin  :<要删除的远程分支名>


```
 
更新和提交代码
 
一个新的文件,或改动.刚开始只存在你的工作区。当你使用git add的时候，Git就会缓存这个改动并且跟踪。当你使用git commit的时候就会把你的改动提交到仓库里。
 
```
// 缓存所有改动  
$ git add --all  
// 缓存单个文件的改动  
$ git add <该文件的文件名，包含路径>  
// 提交至本地仓库  
$ git commit -m <提交备注>  
// 更新本地代码  
$ git pull origin <分支名>  
// 将本地commit推送至远端  
$ git push orign <分支名> 


```
 
撤销
 
```
// 撤销工作区某个文件的更改  
$ git checkout [file]  
// 撤销工作区所有文件的更改  
$ git checkout .  
// 重置暂存区的指定文件，与上一次commit保持一致。但更改并没有消失，而是更改打回工作区  
$ git reset [file]  
// 重置暂存区与工作区，与上一次commit保持一致。  
$ git reset --hard <当前分支名>  
// 重置当前分支的指针为指定commit，同时重置暂存区。但更改并没有消失，而是更改打回工作区  
$ git reset [commit]    
// 重置当前分支的HEAD为指定commit，同时重置暂存区和工作区，与指定commit一致。  
$ git reset --hard [commit]  
// 重置当前HEAD为指定commit，但保持暂存区和工作区不变  
$ git reset --keep [commit]  
// 暂时将未提交的变化存入stash，稍后再弹出 
$ git stash  
$ git stash pop  
git review  


```
 
代码评审使用gerrit系统，git中使用git review <分支名>(默认是master) 命令执行review操作。
 
规则
 
 
* 提交reivew之前pull远程代码，保证提交以前是最新代码，有冲突需要本地合并处理。 
* 一个单一的功能的变更放入一个commit中，提交一次reivew。 
 
 
特殊情况
 
 
* review没有通过怎么办？ 
 
 
先回到要修改的那个commit
 
```
$ git reset --soft  <要修改的那个commit的id> 


```
 
继续修改你要改的文件。修改后add缓存文件，并执行
 
```
$ git commit --amend 


```
 
将刚生产的变更归并到上一次变更里，继续执行git review
 
 
* 已经做了多个提交commits怎么办？ 
 
 
如果多个提交是相关联的，请合并这个提交为一个提交
 
```
// 查询最先提交的commit, 记住id.  
$ git log   
// 进行变基操作  
$ git rebase -i  <上一步查到的id>  
// 弹出的界面上罗列了最先提交的commit到现在的所有提交记录  
//将每列开头的 'pick' 改成 's', 只保留第一列的 'pick'。  
//保存修改后系统会自动把这些commits合并成一个commit.  
// 如果遇到冲突需要手动解决。合并冲突后，继续变基， 直到所有commits都合并为止.  
$ git rebase --continue  


```
 
如果review中提交了多个commits，其中一个commit没review过怎么办(包括以前某个commit中没有生成change id)？一次commit对应生成一个review, 前一个review没通过的话，后面的review 通过了也提交不了。 必须把前面一个review 弄通过，后面的review才能提交。
 
```
// 查询未通过的review对应的commit id(gerrit里有记录)  
// 回到这个commit的前一个节点，注意有个^  
$ 执行 git rebase -i  <未通过的review对应的commit id>^   
// 修改并缓存要提交的文件后  
$ git commit --amend 
 
// 返回head处  
$ git rebase --continue   
// 提交对老review的更新  
$ git review   

```
 


[0]: https://img2.tuicool.com/7nEvIjv.png 
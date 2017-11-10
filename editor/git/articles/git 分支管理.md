# [git 分支管理](http://www.jianshu.com/p/402131fe615c)

卡巴拉的树 2017.02.05 19:36  字数 1798  

  
观察过很多使用git的人，只会用add, commit,push,pull这几个命令，包括恢复版本之类的，多半也会暴力的删除整个项目，再重新clone干净的代码。虽说也能工作了，但是无疑没有领会到git的精华。  
这篇文章主要说明如何使用分支使我们的开发工作更加顺滑，如何让分支成为你日常工作流不可缺失的一部分。

## git branch 用法

    git branch   //列出所有的分支
    git branch <branch> //创建名为<branch>的分支，但是不会切换过去
    git branch -d <branch>  //删除指定分支，这是一个“安全”操作，git会阻止你删除包含未合并更改的分支。
    git branch -D <branch>  //强制删除分支
    git branch -m <branch> //重新命名当前分支

在日常开发中，无论是修复一个bug或者添加一个功能，我们都应该新建一个分支来封装我们的修改。这样可以保证我们不稳定的代码永远不会提交到主代码分支上。

下面我们具体来看在执行分支有关的操作，分支的变化：

## 创建分支

分支只是指向提交的指针，理解这一点很重要，当你创建新分支，实际上只是创建了一个新的指针，仓库本身不会受到影响，一开始你的仓库只有一条分支：

![master][3]

  
然后你执行下面的命令创建一个分支，用于加一个新feature：

    git branch new-feature

![new-feature][4]

  
当然执行后，你只是创建了这个分支，还需要执行git chekcout new-feature切换到new-feature分支，然后再使用git add,git commit。

## 删除分支

假如你已经开发完了new-feature，并且已经commit代码了， 你就可以自由删除这个分支了。

    git branch -d new-feature

如果分支还没有合入master，会报下面的错误：

    error: The branch 'new feature' is not fully merged.
    If you are sure you want to delete it, run 'git branch -D crazy-experiment'.

这时候你可以合并分支（下面会说如何合并分支），如果你**真的**确定了要删除分支，可以用-D执行强制删除：

    git branch -D new-feature

## 切换分支（git checkout）

git checkout 命令允许你切换到用git branch创建的分支。切换分支会更新当前工作目录中的文件，还可以用git log查看当前分支的历史。  
**用法**

    git checkout <existing-branch> //切换到一个已有分支上
    git checkout -b <new-branch> // -b 标记 可以方便让你先创建一个新的new-branch,再直接切换过去
    git checkout -b <new-branch> <existing-branch> //在已有的分支上创建分支，原来的分支使新分支的基

git branch和git chekout 是一对好基友，你可以使用git checkout在不同的功能分支或者bug分支之间切换，而不产生相互影响。

**关于分离的HEAD（detached HEAD）**  
一般我们有时候需要恢复到以前commit的版本上查看原来的一些文件时，我们会git checkout commit的hash码或者tag, 这时候会提醒我们进入了detached HEAD。

![detached HEAD][5]

  
拿上图举例，我们当前的HEAD在4，然后假设 git checkout 2，我们的代码回到2的状态了，通常git会显示下面的warning:

    You are in 'detached HEAD' state. You can look around, make experimental
    changes and commit them, and you can discard any commits you make in this
    state without impacting any branches by performing another checkout.
    
    If you want to create a new branch to retain commits you create, you may
    do so (now or later) by using -b with the checkout command again. Example:
    
      git checkout -b new_branch_name
    
    HEAD is now at 2

如果在这种状态下开发，然后又add,commit，没有分支可以让你回到之前的状态。当你不可避免的需要checkout到另外一个分支，想再回来就是不可能的了，因为像图中的那个X状态，根本就不在分支上，你将再也不能引用你之前添加的代码了。

**重点：永远记得在开发分支上开发，不要在分离的HEAD上开发，这可以确保你可以引用到你的新提交，如果只是checkout到以前看看无所谓，如果真的需要在以前的版本上添加什么代码，记得上面warning 中的git checkout -b new_branch_name，是自己处于一个确切的分支中。**

远离detached HEAD,我们来看实际上git分支流程是什么样子的：

    git branch new-feature
    git checkout new-feature

接下来我们做一些代码改动，提交：

    git add <some file>
    git commit -m "A new feature"
    ##你接着改代码，接着提交很多次

当你git log会显示每一次的commit, 和master分支完全独立，当你checkout到master分支去，再git log,会发现new-feature分支的提交都不在，这就是不影响master分支。  
这时候，你可以考虑合并new-feature或者在master分支上开始别的工作。

## 合并(git merge)

合并是git将被fork的历史放回到一起的方式。 git merge 命令允许你将 git branch 创建的多条分支合并成一个。  
**用法**

    git merge <branch>  //将指定分支并入当前分支
    git merge --no-ff <branch>  //将指定分支并入当前分支，但 总是 生成一个合并提交（即使是快速向前合并）。这可以用来记录仓库中发生的所有合并。

一旦在新分支上完成开发，我们需要把新分支的提交合并到主分支，git会根据目前分支之间的结构信息，选择不同的算法来完成合并：

* 快速向前合并
* 三路合并

**快速向前合并**  
当new-feature的分支与原有的master分支呈现线性关系时，执行快速向前合并，git将当前的HEAD指针快速移到目标分支的顶端，master分支也就具有了new-feature分支的历史了，如图：

![快速向前合并][6]

来看一个快速向前合并的实例：

    # 开始新功能
    git checkout -b new-feature master
    
    # 编辑文件
    git add <file>
    git commit -m "开始新功能"
    
    # 编辑文件
    git add <file>
    git commit -m "完成功能"
    
    # 合并new-feature分支
    git checkout master
    git merge new-feature
    git branch -d new-feature

对于合作开发的人少的项目，这是一种主要的工作流，合作开发的人多的话，主master分支经常都会有新提交，如果你的new-feature耗时比较久，再提交时，master分支可能已经过去几个版本了，这时候就需要下面的三路合并了。

**三路合并**  
但是如果master分支在new-feature分离后，又有了新的提交，即开始分叉了，git只能执行三路合并，三路合并使用一个专门的提交来合并两个分支的历史。

![已经分叉的branch][7]

  
所谓的三路也就是：两个分支的顶端以及他们共同的祖先。 在执行三路合并后：

![三路合并后][8]

  
使用三路合并产生的合并提交作为两个分支的连接标志。

**解决冲突**  
如果两个分支对同一个文件的同一部分均有修改时，git将无法判断应该使用哪个，这时候合并提交会停止，需要你手动解决这些冲突。你可以使用git status来查看哪里存在冲突，很多时候我都会在目录下执行grep -rn HEAD来查看哪些文件里有这个标记，有这个标记的地方都是有冲突的。

当修改完所有的冲突后，git add所有的冲突文件，运行git commit生成一个合并提交，这和提交一个普通快照的流程相同。提交冲突只会存在在三路合并中，快速向前合并中不可能出现针对同一文件同一部分的不一样修改。

下面实例来看看三路合并是怎么产生的：

    # 开始新功能
    git checkout -b new-feature master
    
    # 编辑文件
    git add <file>
    git commit -m "开始新功能"
    
    # 编辑文件
    git add <file>
    git commit -m "完成功能"
    
    # 在master分支上开发
    git checkout master
    
    # 编辑文件
    git add <file>
    git commit -m "在master上添加了一些极其稳定的功能"
    
    # 合并new-feature分支
    git merge new-feature
    git branch -d new-feature

这时候，merge会停止，因为无法将 master 直接移动到 new-feature。所以需要你手动合并冲突后再提交。

git merge会产生合并提交，有的人会选择使用git rebase来合并以确保一个干净的提交历史。关于这两个的区别，我会另写一篇介绍。git 代码合并



[3]: ../img/b965174b6cab013a.PNG
[4]: ../img/ce3367855cc86818.PNG
[5]: ../img/2dc6a15789d5e06e.PNG
[6]: ../img/6d2a2fdb236db48d.PNG
[7]: ../img/9f23d7fc6aff8a45.PNG
[8]: ../img/eca0c55e78808883.PNG
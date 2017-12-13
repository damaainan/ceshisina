# Git提交历史的修改删除合并等实践

 时间 2017-12-13 13:56:53  惊鸿三世的博客

原文[http://blog.codingplayboy.com/2017/12/13/git-commit-operate/][2]


今天主要针对在项目版本控制器Git的使用中遇到的一些和提交历史操作相关的常见问题，进行实践总结。在项目开发中经常会需要修改提交commit信息，合并多个提交commit，甚至放弃当前修改回退至某一历史提交的需求，那我们到底该如何操作呢，本篇一一阐述。

索引

##  前言 
假如，当前我们处在需求分支 feature-test ，进行了多次提及， git log 查看commit信息如下： 

![][4]

每一个提交的commit都是独立的，但是最近三个commit都是相关的，都是添加 read.txt 文件内容，在本文中以此为实例依次介绍如何修改，合并，回退commit的。 

##  查看git历史 
如果要查看git历史可以使用 git log 或 git reflog 指令： 

1. git log ：查看当前分支的存在提交历史记录，不包括诸如删除的或被合并的提交；
1. git reflog ：查看当前分支所有操作历史，诸如历史提交记录，撤销，合并提交等详细历史记录；

##  git rebase -i 
需要用到的指令是 git rebase -i commitHash ， commitHash 是commitID，是需要合并的commit的前一个commit节点的ID，对于本文实例中而言，是最近第四个提交 df11bf944 ，所以执行如下指令： 

    git rebase -i df11bf944

命令行终端会输出如下内容：

![][5]

由远及近列出了我们期望处理的三个提交，前面 pick 代表的默认使用该提交commit，我们现在可以按 i 进入编辑模式，修改该字段值，值可以如图中描述，经常使用的如下： 

1. pick ：简写 p ，启用该commit；
1. reword ：简写 r ，使用该commit，但是修改提交信息，修改后可以继续编辑后面的提交信息；
1. edit ：简写 e ，使用commit，停止合并该commit；
1. squash ：简写 s ，使用该commit，并将该commit并入前一commit；
1. drop ：简写 d ，移除该commit；

##  修改提交信息 
我们现在尝试修改最近一次的提交commit信息，将其前面 pick 修改成 reword ： 

![][6]

编辑后，按 esc 键退出编辑模式，然后输入 :wq ，保存当前编辑，会输出如下内容： 

![][7]

我们可以开始编辑我们需要修改的commit信息了，按 i 键进入编辑模式，修改提交信息为： 

    feature(read.txt) 添加read.txt第三行（reword修改commit message）

保存退出后会有修改成功提示：

![][8]

##  合并历史提交 
前面修改commit成功，如果期望将多个提交合并成一个提交，使得整个提交历史更干净，如何处理呢？

执行如下指令， df11bf944 是需要合并的提交的前一个提交节点的commitID: 

    git rebase -i df11bf944

然后修改 pick 值为 squash ： 

![][9]

保存退出，会进入最终合并提交commit信息编辑状态，在这里会列出合并commit的所有message，我们可以操作：

![][10]

我们可以同时保留三次的提交信息，也可以任意修改，此处我们只保留第一个提交的信息，然后保存退出，当我们再次使用 git log 查看历史提交信息时，就会发现只剩下合并后的一个提交及之前未操作的提交： 

![][11]

###  git rebase -i head~{num} 
前面提到的 git rebase -i commitHash 指令可以合并提交历史，其实还可以换成一种快捷方式，如当需要合并最近两个提交时，执行： 

    git rebase -i head～2

效果一样：

![][12]

后续修改，合并，回退操作均一致。

##  撤销提交 
当需求发现变更，我们发现不需要某一历史提交时，怎么办呢，怎么放弃该修改提交？这也分两种情况：

1. 历史提交中间某提交的撤销；
1. 最近提交的撤销；

###  撤销中间提交 
当需要放弃的提交被合并后，我们想放弃该提交，需要先查看该提交的信息使用，执行指令：

    git reflog

该指令输出详细的操作历史，包括提交，操作，修改等：

![][13]

我们找到需要撤销的提交，如最近第二个提交,提交commitId为 dcbdde2 ，索引为 HEAD@{19} ： 

    dcbdde2 HEAD@{19}: commit: feature(read.txt): 添加read.txt第二行

 git revert

执行以下指令撤销该commit：

    git revert head@{19}

![][14]

上面 head@{19} 指令也等效于： 

    git revert dcbdde2

git revert 撤销一个提交的同时会创建一个新的提交。这是一个安全的方法，因为它不会重写提交历史。它会创建一个新的提交来撤销指定更改，然后把新提交加入至项目中。 

撤销提交时若多个提交修改了同一文件可能会出现冲突，需要处理冲突后，暂存：

    git add .

然后继续执行revert操作：

    git revert --continue

![][15]

然后查看提交历史，发现多了一个记录：

![][16]

此时已经撤销了之前最近第二次提交的内容（即撤销了read.txt文件第二行）。

###  撤销最近提交 
如果期望撤销的提交是最近独立存在的，并没有发生合并，以撤销上一节 git revert 新生成的提交为例： 

    5a7b985 Revert "feature(read.txt): 添加read.txt第二行"
    df11bf9 commit: "feat(RN-publish-up): React Native发布，热更新原理介绍"

 git reset

只需要使用 git reset 指令： 

    git reset commitHash / head~{num}

commitHash是期望撤销提交的上一次提交的commitID，等效于指定期望撤销最近几次提交，num值等于期望撤销提交数。

具体提交的commitID可以使用 git log 或 git reflog 指令查找，删除执行指令： 

    git reset head~1

等效于， df11bf9 是需要撤销提交commit的上一次提交commitID： 

    git reset df11bf9

最后会发现提交内容变成未提交，使用 git checkout. 指令撤销变更就行： 

![][17]


[2]: http://blog.codingplayboy.com/2017/12/13/git-commit-operate/

[4]: ../img/rqIZvay.png
[5]: ../img/QvIj2iR.png
[6]: ../img/UBZBjmq.png
[7]: ../img/v2IVr2m.png
[8]: ../img/vm2I7rI.png
[9]: ../img/VbmeQzV.png
[10]: ../img/JbyIBvf.png
[11]: ../img/63yMryN.png
[12]: ../img/jUFNzue.png
[13]: ../img/VnAz22A.png
[14]: ../img/zQVrYrz.png
[15]: ../img/63iIRbj.png
[16]: ../img/nMz2Qbu.png
[17]: ../img/qYfU7b3.png
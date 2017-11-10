# [git 代码合并](http://www.jianshu.com/p/b15574f50939)

卡巴拉的树  2017.02.12 14  字数 1082  

上一篇传送门：[git 分支管理][1]  
在Git中，git merge和git rebase都是用来将一个分支的修改并入另一个分支，只不过方式不同。

在日常工作中基本都会有一个工作主分支，一般我们会新建一个新的分支开始我们的工作，以免影响主分支。我们假设以下的情景来说明代码合并。

小李需要开发FeatureA，因此他在项目主分支的基础上新建了一个FeatureA的分支开始了他的工作，在他工作的同时，同事修复了两个bug，并且都合入了主分支，于是代码分支变成 了下面这种情况：

![分支状况][2]
  
这时候小李想基于修复好bug的版本继续之前的开发，那么就需要将自己的代码和已经分叉的主分支进行合并，采取的方法可以是git merge,也可以是git rebase.

**git merge**  
一般来说，最简单的操作就是执行下面的步骤：

    git checkout featureA
    git merge master

或者直接：

    git merge master featureA

这样就会产生一个三路合并：

![git merge][3]
  
git merge的优点就是简单，并且产生一个合并的历史，但是如果master分支的历史很活跃，你又想始终保持与master一致，那么多多少少那么多的合并历史会影响到你分支历史的整洁性。这时候git rebase就可以登场了。

关于更多git merge的详细述说可以看我上一篇文章：[git 分支管理][1]

**git rebase**  
作为merge的替代，我们可以这样执行rebase：

    git checkout featureA
    git rebase master

它会把整个featureA的分支直接移到现在master分支的后面：

![git rebase][4]
  
git rebase最大的特点就是会使你的项目历史非常干净，呈现出一条线性提交，因为它不会引入合并提交，这让你更容易使用git log 、git bisect和gitk 来查看项目历史。

不过说git rebase也是git中的黑魔法，如果不遵守使用git rebase的黄金法则，也会给你的项目历史带来灾难性的影响，rebase使你的feature分支没有合并提交历史，所以你也看不出feature合并进了上游的哪些修改。

**交互式的rebase**  
这里假设小A在他的featureA分支已经有三个提交了，这时候如果直接rebase就会把 这三个提交历史都接到master分支后面。

![带有三次提交历史的feature分支][5]
  
而交互式的rebase则可以对featureA的分支历史进行清理。  
加上-i,我们执行交互式的rebase:

    git checkout featureA
    git rebase -i master

它会打开你git的文本编辑器:

    pick 33d5b7a Message for commit #1
    pick 9480b3d Message for commit #2
    pick 5c67e61 Message for commit #3

这个列表显示了你fetureA的历史，你可以通过更改顺序，更改pick或者使用fixup来合并你的历史。比如如果#2和#3包含了修复性的更改，你想合并他们，那么你可以加上一个fixup:

    pick 33d5b7a Message for commit #1
    pick 9480b3d Message for commit #2
    fixup 5c67e61 Message for commit #3

![fixup][6]
  
如图所示，#2和#3被合并为一次提交，这样在合并的过程中还能简化项目历史，这是git merge办不到的。

**Rebase黄金法则**  
当你使用Rebase更加方便地处理你的分支时，你也必须懂得有哪些潜在的风险。使用rebase的一条黄金法则就是，绝对不要在公共分支上使用它。假设你使用了，那么便会发生下面的情况：

![在主分支上rebase][7]
  
如上图，你在主分支上的git rebase将master分支移到了featureA分支的后面，而其他开发者还是基于原有的master分支进行开发，这时候你的master分支就和别人的分叉了，如果需要同步，则需要额外的一次git merge,以让你目前的master和别人的master合并，你的项目历史更加令人迷惑。

所以，在进行git rebase时，务必确认有没有别人在此分支上工作，如果有的话，那么你就得考虑一种无害的方式来进行你的提交，比如git revert这样的操作。


[1]: http://www.jianshu.com/p/402131fe615c
[2]: ../img/5ef9d3f78af7f147.PNG
[3]: ../img/ea50ef194043781e.PNG
[4]: ../img/b9a5be802b44a680.PNG
[5]: ../img/5aacac5832fe0eb8.PNG
[6]: ../img/617c9fc30ff46635.PNG
[7]: ../img/34b6f720c460f2a0.PNG
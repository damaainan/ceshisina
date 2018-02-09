# [Git学习 <day6>－Fast-forward][0]


 2016-07-30 22:40 

1. [Fast-forward方式合并][8]
    1. [git merge ff][9]
    1. [git merge no-ff][10]
    1. [git merge ff-only][11]
    1. [用Fast-forward方式好还是不用好呢][12]

## Fast-forward方式合并

git merge默认使用的是Fast-forward的方式，git merge可以选择使用或者不使用快进方式合并。–ff是Fast-forward的简写，git merge --ff 是默认的方式，只更新指针的指向，没有**`针对merge的commit提交`**。–no-ff 强制针对本次merge产生一个commit提交，即使本次merge经git解析可以使用快进方式，也会产生额外的针对此次merge的commit。–ff-only 只能进行快进方式的合并，否则git拒绝进行merge。

#### **git merge –ff**

我在新checkout出来的mobile分支上修改了env.properties文件，master分支未更改env.properties文件，欲将test分支合并到master，我先用默认的方式：

    ➜  erp git:(master) git merge --ff -m "Merge mobile to master with -ff" mobile
    Updating c1c36d7..101317b
    Fast-forward (no commit created; -m option ignored)
     env.properties | 1 +
     1 file changed, 1 insertion(+)

提示信息说，本次merge是快进方式，因此针对commit的说明，即-m 后面跟的comment被忽视掉了，因为快进方式没有commit提交，仅仅是指针的移动。

    ➜ erp git:(master) git log --graph --pretty=oneline --abbrev-commit    1317b modify file on mobile branch
        *   c1c36d7 merge test to master
        |\  
        | 11210 modify env file on test
        * |   cea1dc3 Merge branch 'test'

--graph 表示以图的形式显示log信息，--pretty=oneline 表示每条日志信息显示一行，--abbrev-commit 表示以缩略的形式显示提交信息。从分支树中很直观地看到，master分支的指针向后移动了一个位置，指向了mobile分支的最新提交。整个快进式合并没有产生新的commit提交。

    ➜ erp git:(master) git reflog    101317b HEAD@{0}: merge mobile: Fast-forward (no commit created; -m option ignored)
        c1c36d7 HEAD@{1}: checkout: moving from mobile to master
        101317b HEAD@{2}: commit: modify file on mobile branch
        c1c36d7 HEAD@{3}: checkout: moving from master to mobile

可以看到针对默认方式没有产生commit提交。

#### **git merge –no-ff**

同样的方式，修改mobile分支上的文件，master分支上的对应文件未改动，按之前的经验来看，git会自动将这种合并解析为快进方式，但是呢，我现在强制不使用快进方式：

    ➜  erp git:(master) git merge --no-ff -m "Merge mobile to master with --no-ff" mobile
    Merge made by the 'recursive' strategy.
     env.properties | 1 +
     1 file changed, 1 insertion(+)

提示信息显示使用recursive递归的方式进行合并。

    ➜ erp git:(master) git log --graph --pretty=oneline --abbrev-commit    *   4ecfc99 Merge mobile to master with --no-ff
        |\  
        | * a351eb2 modify env file on mobile branch
        |/  
        1317b modify file on mobile branch

从上面的分支树很清晰地看到，这次合并并不是仅仅将master的指针移动到mobile分支的最新提交那么简单，而是真对此次merge专门产生了一个commit。通过查看操作日志也能很清晰地看到这点：

    ➜ erp git:(master) git reflog    4ecfc99 HEAD@{0}: merge mobile: Merge made by the 'recursive' strategy.
        101317b HEAD@{1}: checkout: moving from mobile to master
        a351eb2 HEAD@{2}: commit: modify env file on mobile branch
        101317b HEAD@{3}: checkout: moving from master to mobile

#### **git merge –ff-only**

我分别在mobile和master分支修改了env.properties文件的同一个地方，切换到master分支上做merge：

    ➜  erp git:(master) git merge --ff-only mobile
    fatal: Not possible to fast-forward, aborting.

--ff-only 表示只支持快进方式合并，如果git 发现此次合并无法解析为快进方式，那么什么都不做。

#### **用Fast-forward方式好还是不用好呢？**

Fast-forward看起来很简单，当不需要程序员手动merge代码的时候，用快进方式只移动指针即可，简单又粗暴。然而快进方式也会带来一些问题：_记录丢失_  
切换到mobile分支上，在env.properties中添加属性server=jboss，切换到master分支上，将mobile分支merge到master。

    ➜ erp git:(master) git log --graph --pretty=oneline --abbrev-commit    d42c83 add server property
        *   c1c36d7 merge test to master
        |\  
        | 11210 modify env file on test
        * |   cea1dc3 Merge branch 'test'
        |\ \  
        | |/  
        | * d4cdf09 modify env file on test
        |/  
        88129 modify env properties file

仅从上图中我们无法判断8d42c83 add server property 是哪个分支所做的操作，也看不出来曾经做过合并。

[0]: /chi_wawa/article/details/52075642
[8]: #t0
[9]: #t1
[10]: #t2
[11]: #t3
[12]: #t4
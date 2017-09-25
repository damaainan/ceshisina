# 使用 git rebase 提高 PR 质量

 时间 2017-09-24 22:53:37  

原文[http://ewind.us/2017/git-rebased-better-pr/][2]



在 Github 上以提交 PR 的方式参与开源项目是十分简单的。不过由于 Git 本身自由度较高，有些随意提出的 PR 实际上是会影响项目历史记录的【脏】PR。下文介绍何时会发生这种情况，以及如何通过 rebase 工作流改进它。

## 什么是脏 PR 

我们知道，如果你想为某个开源项目贡献代码，通用的流程是：

1. fork 项目到自己的仓库。
1. 在新开的分支上提交。
1. 提出 PR 请求维护者将你的新分支合并至原项目。

在最后一步中，你所提交的 PR 会包括新分支上全部的历史记录。这时候，如果出现下面的几种情况之一，在这里我们就认为这个 PR 属于【脏】PR：

1. PR 分支和原仓库的目标分支 **存在冲突** 。
1. PR 包含了许多 **琐碎的 commit 记录** ，如 `fix bug / add dev` 等缺乏实际意义的提交信息。
1. PR 包含了多个 **不必要的 Merge 记录** 。一般来说，fork 出的仓库和原仓库保持同步的最简单方式，是 fetch 原仓库后将 HEAD merge 到当前分支。这个操作每执行一次，就会在当前分支留下一个类似 Merge xxx 的 commit 记录。
1. PR 包含了 **与主题不符的改动** ，如留下冗余的日志文件、在其它模块中添加了额外的调试用代码等。

## 如何处理脏 PR 

### 内部项目 

上述的几种情况，在开发托管在 Stash 或 Gitlab 上的内部项目时，其实都不是问题，都有着非常简单的解决方案：

1. 冲突了？直接拉主分支拉下来改啊，反正大家都是管理员:v:
1. `commit` 怎么写有问题吗？本来不就是每天下班准时 `commit` 一次吗？
1. 看看我们的 `git log --graph` ，多么壮观！大家都很努力的好吗！
1. 能按时提测就行，不要在意这些细节��。

并不是说这么处理有什么问题，尤其在中国特色天天赶进度的业务项目中，这么做也基本上是最佳实践了。下面，我们重点讨论的是在较为正式地向外提交 PR 时，提升 PR 质量的方法。

### merge –squash 

Github 在很早之前就支持了强制 squash 功能。通过这种方式，原仓库的维护者可以在将 PR 提交的分支所更改的内容，squash 到主仓库的一次提交中。这样，不管提出 PR 的分支有多【脏】，都可以在并入时得到净化了。这大致相当于命令行下这样的操作：

    git merge forked_lib/new_branch --squash
    git commmit -m 'something from new_branch'

这是得到 Github 官方支持的实践，但这么处理有什么局限性呢？主要是这两点：

1. 需要原仓库维护者解决冲突并整理历史，而不是 PR 提出者。
1. 只能将多个 `commit` 整理为一个，而不是若干个。

这个方式最棘手的问题实际上在于：它把编辑提交历史的责任丢给了原仓库的维护者，PR 提交者并不能在提交 PR 前清理历史记录。是否有更好的方案呢？

### rebase 

通过 git rebase 命令，我们能够获得对 git 提交历史更大的掌控。不过，这也是一个存在风险的命令，因此在实际使用前建议稍加了解其原理。

首先假设项目主干分支是 master，你在 fork 而来的仓库下新增了 dev 分支。你从 master 的 m1 提交开始，在 dev 提交了 d1、d2 和 d3 三次提交。这时，master 也更新了 m2 和 m3 两次提交。这时候版本树大致长这样：

    m0 -- m1 -- m2 -- m3
     |
     d1 -- d2 -- d3

这时你的目标是将三次 dev 上的 commit 合并为一个新的 d ，让 dev 的历史变成这样： 

    m0 -- m1 -- m2 -- m3 -- d

为了实现这一点，你可以在 dev 上 rebase 到 master：

    git checkout dev
    git rebase -i master
    

rebase 的原理是：

1. 首先找到两个分支（dev 和 master）的最近共同祖先 m1。
1. 对比当前 dev 分支相比 m1 的历次提交，提取修改，保存为临时文件。
1. 将分支指向 master 最新的 m3。
1. 依次应用修改。

在【依次应用修改】的这一步中，你可以进一步选择如何对待 d1、d2 和 d3 的 commit message。在以 -i 参数启动了交互式的 rebase 后，会进入 vim 界面，由你选择如何操作 dev 上的提交记录，形如这样： 

    pick 91398f93 d1
    pick 65efc762 d2
    pick b82e050d d3
    
    # Rebase 4652f96d..b82e050d onto 4652f96d (3 commands)
    #
    # Commands:
    # p, pick = use commit
    # r, reword = use commit, but edit the commit message
    # e, edit = use commit, but stop for amending
    # s, squash = use commit, but meld into previous commit
    # f, fixup = like "squash", but discard this commit's log message
    # x, exec = run command (the rest of the line) using shell
    # d, drop = remove commit
    #
    # These lines can be re-ordered; they are executed from top to bottom.
    #
    # If you remove a line here THAT COMMIT WILL BE LOST.
    #
    # However, if you remove everything, the rebase will be aborted.
    #
    # Note that empty commits are commented out

你可以编辑对 dev 上这几个 commit 的处理，如输入 pick 为保留，输入 squash 则将该 commit 内容并入上一个 commit 等。在完成操作选择后（这里我们可以选择 fixup d1 和 d2，并 reword d3），输入 :wq 保存退出，会进入一个新的 vim 窗口，在此你可以进一步编辑新的 commit message，保存后 rebase 即可生效。 _注意，你至少需要选择一个需要 use 的 commit，否则会报错。_

rebase 生效后再查阅分支历史记录，是不是清净多了呢？在这个状态下提交更清爽的 PR 吧:wink:

在此额外提醒一点，对于已经被 fork 出多份的仓库，rebase 原仓库的主干是危险操作。除此之外，使用 rebase 修改私有分支的历史记录是很安全的。

回头看看脏 PR 的几个问题，如何通过 rebase 解决呢？

1. 遇到和远程主库的冲突时，可以先将远程仓库 fetch 下来，而后将自己的 dev 分支 rebase 到新的 HEAD 上。
1. 冗余的 commit 记录可以直接 rebase 合并。
1. 和 1 类似地，通过将自己的 dev 分支 rebase 到新的远程库 HEAD 的方式，不会留下冗余的 Merge 记录。
1. 提交一个新 commit 修复问题，而后 rebase 即可。

到此，对 rebase 的介绍大体上就结束了。希望对大家更好地参与开源项目有所帮助。

参考：

* [git-rebase][4]


[2]: http://ewind.us/2017/git-rebased-better-pr/

[4]: https://git-scm.com/docs/git-rebase
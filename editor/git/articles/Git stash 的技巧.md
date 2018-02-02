# 你可能不知道的关于 Git stash 的技巧

[KenChoi][0]

6 小时前

> 简评：如果你用过一段时间的 Git，你可能会用过 Git stash 命令。它是 Git 最有用的功能之一。

以下是一些我在上周学的关于 Git stash 的技巧。

1. Git stash save
1. Git stash list
1. Git stash apply
1. Git stash pop
1. Git stash show
1. Git stash branch <name\>
1. Git stash clear
1. Git stash drop

## Git stash save

这个命令类似于 Git stash。但这个命令可以有一些选项。我会在这里讨论一些重要的选项。

**带消息的 Git stash**

    git stash save “Your stash message”
    

以上命令会将消息存放起来。我们将会看到这很有用。

**存储没有追踪的文件**

你也可以存储没有追踪的文件。

    git stash save -u
    or
    git stash save --include-untracked
    

## Git stash list

在讨论这个命令之前，让我来告诉你 git stash 的工作原理。

当你输入 Git stash 或 Git stash save，Git 会创建一个带名字的 Git 提交对象，然后保存到你的仓库。

这意味着你可以随时查看你的存储列表。

    git stash list
    

效果是这样的：

![][1]

你可以查看 stash 创建的列表。最近的 stash 会放在最上面。

你可以看到最上面的那条有一条自定义消息（通过 Git stash sava “message” 命令生成的）。

## Git stash apply

这条命令会将工作栈中最上面的 stash 应用到仓库中。本例中是 **stash@{0}。**

你也可以通过 stash id 将某个 stash 应用到仓库中：

    git stash apply stash@{1}
    

## Git stash pop

这个命令和 stash apply 非常相似，但它会在应用到仓库后删除这个 stash。

例如：

![][2]

你可以看到最上面的 stash 被删除了，**stash@{0}** 变成了之前的 stash。

同样地，你也可以通过特定的 stash id 来 pop 某个 stash。

    git stash pop stash@{1}
    

## Git stash show

这个命令会显示 stash 差异总结。这条命令只考虑和最近的 stash 比较。

![][3]

如果你想看完整的差异，可以使用：

    git stash show -p
    

和其他的命令相似，你可以通过 stash id 来查看某个 stash 的差异总结：

    git stash show stash@{1}
    

## Git stash branch <name>

这条命令会根据最近的 stash 创建一个新的分支，然后删除最近的 stash（和 stash pop 一样）。

如果你需要某个 stash，你可以指明 stash id。

    git stash branch <name> stash@{1}
    

当你将 stash 运用到最新版本的分支后发生了冲突时，这条命令会很有用。

## Git stash clear

这条命令会删除仓库中创建的所有的 stash。有可能不能恢复。

## Git stash drop

这条命令会删除工作栈中最近的 stash。但是要谨慎地使用，有可能很难恢复。

你可以声明 stash id。

    git stash drop stash@{1}
    

希望对大家有帮助。：）

[0]: https://www.zhihu.com/people/cai-yao-guan
[1]: ../img/v2-aef7f93c09b65f83c459edccd8baff55_hd.jpg
[2]: ../img/v2-cc00b7a4fc10abb95b2bbd8585100bd9_hd.jpg
[3]: ../img/v2-e57b98dc2475a2117bf80a734a56c4e7_hd.jpg
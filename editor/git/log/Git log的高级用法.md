## Git:log的高级用法

来源：[http://www.jianshu.com/p/73f13d2725a8](http://www.jianshu.com/p/73f13d2725a8)

时间 2016-10-20 11:32:10

 
任何版本控制器都是用来记录代码的变动历史。这能帮助你在项目中找到谁提交了什么代码，bug在哪次提交被引入的，并且能帮助你回滚有问题的改动。但是，只是存储这些信息而不知道怎么去引导和分类，也是没有用的。这就是git log这条命令被引入的原因。
 
你应该已经知道可以使用git log来展示提交历史。但是你可能不知道，通过传入不同的参数，能够改变git log的输出。
 
git log的高级特性可以分为两种：1.控制输出的格式；2.控制输出的内容。合理使用这些高级特性能让你找到项目中需要的信息。
 
## 输出格式化
 
我们先来看看如何通过传入一些参数，将git log的输出格式化。
 
如果你不喜欢git log默认的格式，可以用git config创建一个git log的别名， [The git config Command][1] 
 
### Oneline
 
--oneline参数将每次提交记录汇总成一行，默认情况下，会展示commit的id和commit信息的第一行。

```sh
git log --oneline
```
 
一个事例输出如下

```sh
0e25143 Merge branch 'feature'
ad8621a Fix a bug in the feature
16b36c6 Add a new feature
23ad9ad Add the initial code base
```
 
这对了解工程的大概情况很有帮助。
 
### Decorating
 
很多时候需要了解每次commit的关联的分支或者是标签。--decorate参数会展示每次commit对象的相关信息。
 
这个参数和可以和其他参数配合使用，比如，使用

```sh
git log --oneline --decorate
```
 
这个命令会对输出进行如下格式化

```sh
0e25143 (HEAD, master) Merge branch 'feature'
ad8621a (feature) Fix a bug in the feature
16b36c6 Add a new feature
23ad9ad (tag: v0.9) Add the initial code base
```
 
从上面的输出可以看出，第一个commit是master分支的最后一条提交(HEAD)。第二条commit有一个叫feature的分支也指向了它。第4条commit被打上了v0.9的标签。
 
分支，标签，HEAD，commit历史几乎就是你git版本库里的信息，这个命令能让你了解项目完整的逻辑结构。
 
## Diffs
 
git log命令提供了很多参数用于展示每个commit的更改的信息。最常用的有--stat和-p
 
--stat参数将会显示每次提交中，每个文件加入和删除的行数（修改一行等同于加入一行和删除一行）。如果想要简单了解每个commit大致的改动，这个参数是很有用的。举个例子，下面这个commit中，hello.py这个文件添加了67行，删除了38行。

```sh
commit f2a238924e89ca1d4947662928218a06d39068c3
Author: John <john@example.com>
Date: Fri Jun 25 17:30:28 2014 -0500 
Add a new feature hello.py | 105 ++++++++++++++++++++++++----------------- 
1 file changed, 67 insertion(+), 38 deletions(-)
```
 
“+”和“-”数量显示的是添加和删除行数的占比。
 
如果想要知道改动的详细信息，可以使用

```sh
git log -p
```
 
这个命令会将补丁的信息完整输出

```sh
commit 16b36c697eb2d24302f89aa22d9170dfe609855b
Author: Mary <mary@example.com>
Date: Fri Jun 25 17:31:57 2014 -0500 
Fix a bug in the feature
diff --git a/hello.py b/hello.py
index 18ca709..c673b40 100644
--- a/hello.py
+++ b/hello.py
@@ -13,14 +13,14 @@ B
-print("Hello, World!")
+print("Hello, Git!")
```
 
如果提交中包含了大量改动，输出信息可能会很长并且很复杂。通常，你会在完整的补丁信息中寻找特定的改动，这种情况下，可以使用pickaxe参数。
 
### Shortlog
 
git shortlog是git log的一个特别版本，用于生成发布的通告。它会将每个开发者提交的信息汇总，并且展示出来。使用这个命令可以可以很快看到各自做的事情。
 
举个例子，如果两个开发者提交了5次commit，git shortlog会输出这样的信息

```sh
Mary (2): 
    Fix a bug in the feature 
    Fix a serious security hole in our framework
John (3): 
    Add the initial code base 
    Add a new feature 
    Merge branch 'feature'
```
 
git shortlog默认会按提交者名字排序，也可以传入-n参数按每个人的提交次数排序
 
### Graphs
 
--graph 参数会根据分枝提交历史绘出图像。这个命令通常和--oneline，--decorate一起使用

```sh
git log --graph --oneline --decorate
```
 
如果版本库中有两个分支，会有如下输出

```sh
* 0e25143 (HEAD, master) Merge branch 'feature'
|\ 
| * 16b36c6 Fix a bug in the new feature
| * 23ad9ad Start a new feature
* | ad8621a Fix a critical security issue
|/ 
* 400e4b7 Fix typos in the documentation
* 160e224 Add the initial code base
```
 
*号的意思是commit在哪个分支上，所以上面的图像告诉我们23ad9ad和16b36c6者两个commit提交在了topic分支上，其他的commit提交在master分支上。
 
如果代码库分支少，这个命令还是不错的，不过如果分支很多的话，最好还是使用gitk和 [SourceTree][2] 这样的工具。 
 
### 自定义格式化
 
可以使用--pretty=format:"<string>"来自定义输出的格式。输出格式有点像printf中的占位符。
 
举个例子，下面的命令中，%cn,%h和%cd会被提交者姓名，commit的hash缩写，提交的日期占据

```sh
git log --pretty=format:"%cn committed %h on %cd"
```
 
这会产生如下的输出

```sh
John committed 400e4b7 on Fri Jun 24 12:30:04 2014 -0500
John committed 89ab2cf on Thu Jun 23 17:09:42 2014 -0500
Mary committed 180e223 on Wed Jun 22 17:21:19 2014 -0500
John committed f12ca28 on Wed Jun 22 13:50:31 2014 -0500
```
 
占位符的说明可以在 [Pretty Formats][3] 找到。 
 
需要将git log信息重定向作为其他命令的输出时，这个命令尤其有用。
 
## 过滤提交提交历史
 
格式化输出只是git log强大功能的一部分。git log还能够根据需求筛选commit。下面就来看看这部分的功能，上面提交的格式化功能也可以配合这部分使用。
 
### 根据数量过滤
 
最基本的过滤就是限制输出的commit个数。如果你只对最近的几次commit感兴趣，就不用讲所有commit历史输出。
 
使用-<n>参数可以做到这一点。举个例子，下面的命令只输出最近3条提交

```sh
git log -3
```
 
### 根据日期过滤
 
如果想找特定时间段的提交记录，可以使用--after或者是--before参数。这两个参数接受很多种日期格式，举个例子，下面的命令只显示2014年7月1号后的提交

```sh
git log --after="2014-7-1"
```
 
也可以传入相对的时间概念，像"1 week ago"，或者是"yesterday"

```sh
git log --after="yesterday"
```
 
如果想找某个时间区间的提交记录，可以同时传入--before和--after参数。
 
举个例子，可以用下面的命令找到2014年7月1号到4号之间的提交。

```sh
git log --after="2014-7-1" --before="2014-7-4"
```
 
--since，--until和--after，--before是同义的
 
### 按照提交者过滤
 
如果想找某个开发者提交的commit，可以使用--auther参数，传入一个正则表达式，返回所有符合表达式的开发者提交的commit。如果知道想找的人是谁，直接传入字符也可以

```sh
git log --author="John"
```
 
上面的命令会筛选出所有名字里包含"John"的作者提交的commit。
 
也可以用正则来满足更复杂的需求，比如筛选出名字里包含了John和Mary的开发者的提交。

```sh
git log --author="John\|Mary"
```
 
### 根据commit信息过滤
 
使用--grep可以根据commit提交的信息过滤。这个和上面的--author差不多，只不过匹配的是commit信息，比如说可以这样

```sh
git log --grep="JRA-224:"
```
 
也可以使用-i参数忽略大小写。
 
### 根据文件过滤
 
有很多时候，你只对某个文件的改动感兴趣。传入文件路径，就能找到所有和这个文件相关的提交记录，比如，下面的命令会筛出foo.py 和bar.py相关的提交

```sh
git log -- foo.py bar.py
```
 
-- 参数是告诉git log，后面传入的参数是文件路径，而不是分支的名字。如果传入的文件路径不可能是分支名的话，可以省略掉它。
 
### 根据改动过滤
 
根据代码中加入或者移除的某一行代码，也能筛选出相应的commit。这个叫做  **pickaxe**  ，它接受形如-S"<string>"的参数。如果你想知道  **Hello, World!**  这行代码是何时加入到文件中的，可以使用下面的命令 

```sh
git log -S"Hello, World!"
```
 
如果想查找匹配某个正则表达式的代码，可以传入这样子的参数  **-G"<regex>"**  。 
 
这个功能在debug的时候是很有用的，因为它能够筛选出所有影响某一行代码的提交。它甚至能告诉你这一行代码是什么时候移到另外一个文件中的
 
### 根据提交范围过滤
 
可以传入提交的范围来筛选出范围内的commit。范围的格式如下，其中<since>和<until>是指向某个commit

```
git log <since>..<until>
```
 
当传入分支是，这个命令尤其有用。比如展示两个分支的不同，命令如下

```sh
git log master..feature
```
 
**master..feature**  范围中包含了feature分支中所有不在master分支上的commit。换句话说，是feature分支从master分支上切出来后的进度。如图所示。 
 

![][0]
 
注意如果交换顺序(即feature..master)，会得到所有不在feature上的master上的commit。
 
### 过滤Merge信息
 
git log输出包含merge信息。但是，如果开发组总是把上游分支里的更新mege到feature分支，而不是将feature分支rebase到上游分支，就会在代码库中看到非常多的merge信息。
 
可以使用--no-merges来过滤掉这个merge信息

```sh
git log --no-merges
```
 
另一方面，如果只想看到merge信息，可以使用--merges

```sh
git log --merges
```
 
## 总结
 
现在你应该能够使用git log的高级特性，来筛选commit，并且格式化输出了。
 
这些技巧在使用git的时候是非常有用的，但是记住，git log经常和其他的git命令一起使用。一旦你找到了想要的commit，就可以使用git checkout，git revert，或者其他的命令来控制项目的历史了。所以掌握其他git的高级特性也是必要的。
 


[1]: https://www.atlassian.com/git/tutorials/setting-up-a-repository/config
[2]: https://www.atlassian.com/software/sourcetree/overview
[3]: https://www.kernel.org/pub/software/scm/git/docs/git-log.html#_pretty_formats
[0]: ../img/ZRFvEza.png
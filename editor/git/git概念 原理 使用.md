# [git概念 原理 使用][0]

<font face=楷体>

 标签： [git][1][linux][2]

 2016-01-19 16:50  7773人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [git和Github][8]
    1. [概念][9]
    1. [git和Github的关系][10]
    1. [Github类似产品][11]
    1. [git和CVSSVN的区别][12]

1. [git的工作原理][13]
    1. [架构图][14]
    1. [功能特性][15]
    1. [git中的一些概念和原理][16]
        1. [工作区][17]
        1. [版本库][18]
        1. [暂存区][19]
        1. [工作区版本库暂存区之间的关系][20]
        1. [分支][21]
        1. [分支内部原理][22]
        1. [分支策略][23]

1. [git安装][24]
    1. [linux上的安装][25]
    1. [windows上的安装][26]

1. [git命令][27]
    1. [单机操作命令单仓库命令][28]
        1. [创建版本库repository][29]
        1. [把文件添加到代码库][30]
        1. [查看代码仓库状态][31]
        1. [查看修改内容][32]
        1. [查看两个版本之间的不同][33]
        1. [查看commit历史][34]
        1. [查看命令历史][35]
        1. [版本回退][36]
        1. [撤销修改][37]
        1. [删除文件误删除还原][38]
    
    1. [远程仓库命令][39]
        1. [关联远程仓库][40]
        1. [查看远程仓库][41]
        1. [克隆远程仓库][42]
        1. [跟踪远程分支][43]
        1. [推送内容][44]
        1. [抓取内容][45]
        1. [删除远程分支][46]
        1. [删除远程仓库本地关联][47]
        1. [删除远程分支本地关联][48]
    
    1. [分支管理命令][49]
        1. [创建切换查看分支][50]
        1. [合并分支][51]
        1. [删除分支][52]
        1. [bug分支][53]
    
    1. [自定义git命令][54]
        1. [忽略特殊文件][55]
        1. [配置git命令][56]
        1. [搭建自己的git服务器][57]

1. [Github使用][58]
    1. [账号设置][59]
    1. [把本地库推送到github远程库][60]
    1. [从github远程库克隆到本地库][61]
    1. [github原理应用][62]

1. [其它][63]

 **提示：查看本文的方式，不懂的先略过，看了后面的篇幅可能就懂了。**

# git和Github

## 概念   
[Git][64] --- [版本控制][64]**工具（命令）**。

工具介绍官方网站：[http://git-scm.com][65]

工具下载地址：[http://git-scm.com/download/][66]

**[git][64]是一个开源的分布式版本控制系统**，用以有效、高速的处理从很小到非常大的项目版本管理。**git是个工具，在[Linux][67]里面也就类似gcc这样的工具一样，是一个shell命令_。**git是Linus Torvalds为了帮助管理[linux][67]内核开发而开发的一个开放源码的版本控制软件。Torvalds开始着手开发git是为了作为一种过渡方案来替代BitKeeper，后者之前一直是Linux内核开发人员在全球使用的主要源代码工具。开放源码社区中的有些人觉得BitKeeper的许可证并不适合开放源码社区的工作，因此 Torvalds决定着手研究许可证更为灵活的版本控制系统。尽管最初git的开发是为了辅助Linux内核开发的过程，但是我们已经发现在很多其他自由软件项目中也使用了git。例如：很多Freedesktop的项目也迁移到了git上。

Github --- 一个**平台（网站）**。

Github官方网站：[https://github.com/][68]

**提供给用户创建git仓储空间**，**保存（托管）用户的一些数据文档或者代码等。**

Github目前拥有140多万开发者用户。随着越来越多的应用程序转移到了云上，Github已经成为了管理软件开发以及发现已有代码的首选方法。GitHub可以托管各种git库，并提供一个web界面，但与其它像SourceForge或Google Code这样的平台不同，GitHub的独特卖点在于从另外一个项目进行分支的简易性。为一个项目贡献代码非常简单：首先点击项目站点的“fork”的按钮，然后将代码检出并将修改加入到刚才分出的代码库中，最后通过内建的“pull request”机制向项目负责人申请代码合并。已经有人将GitHub称为代码玩家的MySpace。

**Github公有仓库免费，私有仓库要收费的！**

## git和Github的关系   
指定了remote链接和用户信息（git靠用户名+邮箱识别用户）之后，git可以帮你将提交过到你本地分支的代码push到**远程的git仓库**（任意提供了git托管服务的服务器上都可以，包括**你自己建一个服务器** 或者**GitHub/BitBucket等网站提供的服务器**）或者将远程仓库的代码 fetch 到本地。

**Github只是一个提供存储空间的服务器，用来存储git仓库_。**当然现在Github已经由一个存放git仓库的网站空间发展为了一个开源社区（**不只具有存储git仓库的功能了**），你可以参与别人的开源项目，也可以让别人参与你的开源项目。

## Github类似产品 

有很多Github类似的平台，用于提供git仓库的存储。

BitBucket

公有、私有仓库都免费。

网址：[https://bitbucket.org/][69]

GitCafe

网址：[https://gitcafe.com/][70]

GitLab

GitLab可以下载软件GitLab CE 或者 直接在在线平台上操作。

网址：[http://www.gitlab.cc/][71]

Git@OSC 

Git@OSC是开源中国社区团队基于开源项目GitLab开发的在线代码托管平台。  
网址：[http://git.oschina.net/][72]  
  
CSDN代码托管平台  
CSDN CODE系统搭建于阿里云IaaS平台之上，向个人开发者、IT团队及企业提供代码托管、在线协作、社交编程、项目管理等服务。  
网址：[https://code.csdn.net][73]

## git和CVS、SVN的区别 

git是分布式版本控制系统，代码提交是在本地的（如此速度就快），**当然生成补丁（patch）然后push到远程服务器上是需要联网的**。

CVS、SVN是集中式版本控制系统，代码提交都是提交到远程服务器上，是需要一直联网的（如此速度就慢）（ **这里的一直联网不是说你写代码的时候要联网，而是说你提交代码的时候必须联网；** 但是git不同，git提交代码是本地的不需要联网，生成patch后push patch才需要联网，相当于svn的远程的集中服务器对于git来说，这个集中的远程服务器就在本地）。这个地方比较难理解。

CVS、SVN这样的集中式版本控制系统，它的完整代码仓库（ **代码仓库不仅仅只包含了代码，还包含各个历史版本的信息等** ）在中心服务器上，一旦这个中心服务器挂了，也就是完整的代码仓库挂了，虽然你本地可能之前有从中心服务器上取得过代码，但是那些历史版本信息都没有了，而且你也无法再提交代码。

git不同，git没有中心服务器的概念，每一个git客户端（git节点）都含有一个完整的代码仓库（前提是你之前从远程git仓库fetch过代码），所以那些历史版本信息都在你本机上，假如哪一个git节点挂掉了，随意从其他git节点clone一个代码仓库过来就ok了， 那些原来的代码、版本信息之类的都还是完整的（**当然如果你在这个挂掉的git节点上新增的代码是没有掉了的**）。

综上， **git的每一个节点（第一次从远程git仓库取得代码后，该git节点就是一个完整的代码仓库）相当于SVN的中心服务器，都包含完整的代码仓库** 。

# git的工作原理

## 架构图 

![][74]

## 功能特性  
git的功能特性：  
从 **一般开发者**的角度来看，git有以下功能：  
1、从远程服务器上克隆clone完整的git仓库（包括代码和版本信息）到自己的机器（单机）上。  
2、在自己的机器上根据不同的开发目的，创建分支，修改代码。  
3、在单机上自己创建的分支上提交代码。  
4、在单机上合并分支。  
5、把远程服务器上最新版的代码fetch下来，然后跟自己的主分支合并。  
6、生成补丁（patch），把补丁发送给主开发者。  
7、看主开发者的反馈，如果主开发者发现两个一般开发者之间有冲突（他们之间可以合作解决的冲突），就会要求他们先解决冲突，然后再由其中一个人提交。如果主开发者可以自己解决，或者没有冲突，就通过。  
8、一般开发者之间解决冲突的方法，开发者之间可以使用pull命令解决冲突，解决完冲突之后再向主开发者提交补丁。

从 **主开发者** 的角度看，git有以下功能：  
1、查看邮件或者通过其它方式查看一般开发者的提交状态。  
2、打上补丁，解决冲突（可以自己解决，也可以要求开发者之间解决以后再重新提交，如果是开源项目，还要决定哪些补丁有用，哪些不用）。  
3、向远程服务器（公共的）提交结果，然后通知所有开发人员。

  
优点：  
1、适合分布式开发，强调个体。  
2、远程服务器（公共的）压力和数据量都不会太大。  
3、速度快、灵活。  
4、任意两个开发者之间可以很容易的解决冲突。  
5、离线工作。（**当然提交远程服务器或者从远程服务器fetch代码是要联网的**）。

## git中的一些概念和原理 

![][75]

### 工作区 

Working Directory

电脑上能看到的目录即工作目录，比如：/e/git_repo/

![][76]

### 版本库 

Repository

工作区有一个隐藏目录.git，这个不算工作区，而是git的版本库。  
git的版本库里存了很多东西，其中最重要的就是称为stage（或者叫index）的暂存区，还有git为我们自动创建的第一个分支master，以及指向master的一个指针叫HEAD。构造参见上面的结构图。

![][77]

### 暂存区   
Stage

暂存区就是版本库中的一个区域，具体参见上面的结构图。

### 工作区、版本库、暂存区之间的关系 

git工作的流程就是：  
第1步，使用`git add`把文件从工作区添加到版本库中的暂存区，`git add`命令可以多次用；  
第2步，使用`git commit`提交代码，就是把暂存区的所有内容提交到当前分支。  
综上，需要提交的文件修改通通放到暂存区（可能有多次的`git add`），然后，一次性提交暂存区的所有修改到当前分支（`git commit`）。

### 分支 

分支（`branch`）有什么用呢？假设你准备开发一个新功能，但是需要两周才能完成，第一周你写了50%的代码，如果立刻提交，由于代码还没写完，不完整的代码库会导致别人不能干活了。如果等代码全部写完再一次提交，又存在丢失每天进度的巨大风险。  
现在有了分支，就不用怕了。你创建了一个属于你自己的分支，别人看不到，还继续在原来的分支上正常工作，而你在自己的分支上干活，想提交就提交，直到开发完毕后，再一次性合并到原来的分支上，这样，既安全，又不影响别人工作。

### 分支内部原理 

1、如下图所示，版本的每一次提交（`commit`），git都将它们根据提交的时间点串联成一条线。刚开始是只有一条时间线，即`master`分支，`HEAD`指向的是当前分支的当前版本。

![][78]

2、当创建了新分支，比如`dev分支`（通过命令`git branch dev`完成），git新建一个指针`dev`，`dev=master`，dev指向master指向的版本，然后切换到dev分支（通过命令`git checkout dev`完成），把`HEAD`指针指向`dev`，如下图。

![][79]

3、在dev分支上编码开发时，都是在dev上进行指针移动，比如在dev分支上commit一次，dev指针往前移动一步，但是master指针没有变，如下：

![][80]

4、当我们完成了dev分支上的工作，要进行分支合并，把dev分支的内容合并到master分支上（通过首先切换到master分支，`git branch master`，然后合并`git merge dev`命令完成）。其内部的原理，其实就是先把HEAD指针指向master，再把master指针指向现在的dev指针指向的内容。如下图。

![][81]

5、当合并分支的时候出现冲突（`confict`），比如在dev分支上`commit`了一个文件file1，同时在master分支上也提交了该文件file1，修改的地方不同（比如都修改了同一个语句），那么合并的时候就有可能出现冲突，如下图所示。

![][82]

这时候执行`git merge dev`命令，git会默认执行合并，但是要手动解决下冲突，然后在master上git add并且git commit，现在git分支的结构如下图。

![][83]

可以使用如下命令查看分支合并情况。

    git log --graph --pretty=oneline --abbrev-commit

6、合并完成后，就可以删除掉dev分支（通过`git branch -d dev`命令完成）。

![][84]

如此，就是分支开发的原理。其好处也是显而易见的。

### 分支策略 

如何合适地使用分支？

在实际开发中，我们应该按照几个基本原则进行分支管理：  
1、master分支应该是非常稳定的，也就是仅用来发布新版本，平时不要在master分支上编码开发。 **master分支应该与远程仓库保持同步** ；  
2、平常编码开发都在dev分支上，也就是说，dev分支是不稳定的，到某个时候，比如1.0版本发布时，再把dev分支合并到master上，在master分支发布1.0版本； **dev分支也应该与远程保持同步；（git push/git pull也要解决冲突）**   
3、你和团队成员每个人都在本地的dev分支上干活，每个人都有自己的分支，时不时地往远程dev分支上push/pull就可以了。（ **push/pull的时候是要解决冲突的.** ）

![][85]

上面这个图是大致示意图，其实上面这个图是省略了`git push/git pull`操作的，比如bob在本地dev分支上，新建了一个feature1分支干完活，在本地的dev分支上合并了feature1分支，然后要把dev分支`push`到公共服务器上，这样michael才能`pull`下来bob完成的内容。更详细一点的图如下：

![][86]

**PS** ：git本没有公共服务器的概念，git的每个节点都是一个完整的git库，但是公共服务器是方便了git节点之间的代码互相`push/pull`（要不然每个git节点都需要互相连接，每增加一个git节点就要连接其他的git节点）。如下图所示：

![][87]

![][88]

有公共服务器的结构 无公共服务器的结构

有公共服务器的，增加tom节点时候，只需要tom和公共服务器相连接，tom就可以取得（pull）michael提交（`push`，此处不是`commit`，`commit`是 **本地提交**，没有 **推送到公共服务器**）的代码，也可以取得bob提交（push）的代码。

如果没有公共服务器，tom要想取得（pull）michael的代码，则必须在tom和michael之间建立一个网络连接； 要想取得（pull）bob的代码，必须在tom和bob之间建立一个网络连接。

# git安装

## linux上的安装 

    yum install git

## windows上的安装 

[http://git-scm.com/download/win][89]

下载安装即可。

安装完成后，**还需要最后一步设置**，在命令行输入：

    git config --global user.name "Your Name"
    git config --global user.email "email@example.com"

  
因为git是分布式版本控制系统，所以，每个机器都必须自报家门：你的名字和Email地址。   
**注意`git config`命令的`--global`参数，用了这个参数，表示你这台机器上所有的git仓库都会使用这个配置，当然也可以对某个仓库指定不同的用户名和Email地址**。然后在本机会生成一个`.gitconfig`文件，里面包含了`user.name`和`user.email`的信息。

**WINDOWS下需要再添加一个配置**，如下：


    git config --global core.autocrlf false

如果没有加这个配置，在后续git操作的时候可能会报warning，如下： 

![][90]

这是因为在windows中的换行符为`CRLF（\r\n）`， 而在linux下的换行符为：`LF（\n）`。  
使用git来生成一个工程后，文件中的换行符为`LF`，在windows中，执行git add file操作时，系统提示： **LF将被转换成CRLF**。  
`CRLF -- Carriage-Return Line-Feed` 回车换行，就是回车(CR,ASCII 13,\r) 换行(LF,ASCII 10,\n)。

# git命令

git目录最好都不要包含中文名。

## 单机操作命令/单仓库命令

### **创建版本库repository** 

    mkdir git_repo
    cd git_repo
    git init

![][91]

会在目录中生成`.git`文件夹，该文件夹就是git仓库的管理文件，不要随意改动里面的内容。该文件夹默认是隐藏，ls命令看不到，用ls -ah命令可以看到。

![][92]

新建一个file1.txt文件，文件内容如下（随意写的）：


    This is file1.txt.
    for test~~~
    

  
### **把文件添加到代码库**


    git add file1 file2...
    git commit -m "comment"

![][93]

![][94]

**git add是把想要提交的文件先提交到commit缓存中；**

**git commit才是真正的文件提交。**

**通过git add命令，就等于是把文件加入到git管理中，会有各种git信息跟踪，比如代码版本号，修改了哪里等等。**

### **查看代码仓库状态** 

    git status

假设我修改了file1.txt文件内容如下： 

    hello everybody!
    This is an file1.txt.
    
    end file.

然后用`git status`命令看一下： 

![][95]

上面的提示告诉我们file1.txt被修改过了，**但是还没有add到commit缓存中，即还没有准备提交（commit）**。

  
### 查看修改内容 

虽然git status告诉我们file1.txt被修改了，但是没告诉我们哪里被修改，为了具体查看文件什么地方被修改，就使用如下命令：


    git diff file

**git diff命令要在git add命令之前使用，否则一旦添加到commit缓存后，git diff命令就失效了**。 

![][96]

![][97]

**通过git add后，再看git status状态，就变成了准备提交（commit）的状态了**。

随后再`git commit`一下，就提交成功。

![][98]

commit后，我们再看看git status，如下：

![][99]

上图黄色框中说明当前目录多了个less文件是没有被git跟踪管理的，这个是我不小心加入的文件，在此无用，可以删去。如果是你需要的文件，那么就通过`git add`把它加入git管理。

删去了less文件后，看git status，如下所示：

![][100]

### 查看两个版本之间的不同 


    git diff 版本1 版本2 [文件]

文件是可选的参数，不带[文件]参数的是比较所有的不同修改： 

![][101]

带[文件]参数的，你可以只查看某个文件的不同：

![][102]

### 查看commit历史   

    git log

![][103]

### 查看命令历史 

    git reflog

![][104]

### **版本回退** 

    git reset --hard HEAD^
    git reset --hard HEAD@{4}

先用`git log`或者`git reflog`看下历史版本，然后用`git reset` 命令回退版本，如下： 

![][105]

**git是用HEAD来表示 当前分支 中的 当前版本**，`HEAD^`表示上一个版本，`HEAD^^`表示上上一个版本，以此类推，如果要回退很早的版本就用`HEAD@{版本号}`，版本号用`git reflog`查看。

### 撤销修改 

分3种情况

场景1：当你改乱了工作区某个文件的内容，想直接丢弃工作区的修改时，用命令`git checkout -- file` **或者** 手动修改。  
场景2：当你不但改乱了工作区某个文件的内容，并且还添加到了暂存区（即已经git add了）时，想丢弃修改，分两步，第一步用命令`git reset HEAD file`，就回到了场景1，第二步按场景1操作。  
场景3：已经提交了不合适的修改到版本库时，想要撤销本次提交，可以用版本回退（参考 **版本回退**一节）， **不过前提是没有推送到远程库** 。

下面举例说明：

**场景1**

如果文件还是在工作区中，还没有git add到暂存区，那么撤销修改有2种办法:，如下：

第1种办法：手动复原，把不想要的修改地方复原。（该方法可以只修改文件的一部分）

![][106]

第2种办法：使用命令`git checkout -- file`。（ **这里的 -- 很重要，后续的版本分支也是是用git checkout命令，它没有 --** ）

（该方法只能全部复原文件， **因为git checkout -- file其实就是把版本库中的file替换现在工作区的file** ）

![][107]

**场景2**

当你不但改乱了工作区某个文件的内容，还添加到了暂存区时，想丢弃修改，分两步骤。  
第1步：用命令`git reset HEAD file`，就回到了场景1；  
第2步：按场景1操作。 

![][108]

![][109]

`git reset HEAD file`命令是把缓存区中的file文件删去，对工作区后续做的修改并没有影响，比如上面的例子，git add后又修改了文件的内容everybody->chenj_freedom，`git reset HEAD file`后，file的内容还是chenj_freedom。

**场景3**

已经提交了不合适的修改到版本库时，想要撤销本次提交，参考版本回退一节， **不过前提是没有推送到远程库** 。

### 删除文件/误删除还原 

从版本库中删除文件

    git rm file 或者 git add file
    git commit

误删除复原

    git checkout -- file

`git checkout -- file`其实就是用版本库中的file文件替换工作区的文件，所以无论工作区的file文件是被修改了还是被删除了，用这个命令都可以一键还原。

![][110]

## 远程仓库命令

### 关联远程仓库 

    git remote add 远程仓库名 [url]

举例：

    git remote add origin git@github.com:chenj-freedom/learngit.git

如上，`origin`是远程仓库的名字（注意：是 **仓库** 的名字，仓库中含有 **分支** 等信息）。 

### 查看远程仓库 

    git remote //查看远程仓库
    git remote -v //查看远程仓库，更详细信息

如下图： 

![][111]

`fetch`说明本地有提取远程仓库的权限，`push`说明本地有推送代码到远程仓库的权限。

### 克隆远程仓库 

    git clone [url]

举例：  
![][112]

git clone操作会 **自动为你将远程仓库命名为origin** ，并抓取远程仓库中的所有数据，建立一个指向它的master指针，在本地命名为`orgin/master`，然后，git自动建立一个属于你自己的本地master分支，始于origin上master分支相同的位置（ **master分支的关联，这个也叫做 跟踪远程分支** ），你可以就此开始工作。

原理如下图所示：

![][113]

### 跟踪远程分支 

从远程分支checkout出来的本地分支，称为：跟踪分支 (`tracking branch`)。 **跟踪分支的本质就是使得本地分支名指向远程分支名指向的内容** **（本质就是设置指针）**，如上一小节的图，使得本地分支名（master）指向远程分支名（`origin/master`）指向的节点。 **设置了跟踪分支之后，使用git push/git pull命令就会自动使得本地分支（local-branch-name）自动push/pull远程分支（remote-branch-name）的内容。**

命令格式如下：

    git checkout -b [local-branch-name] [remote-name]/[remote-branch-name] //传统格式
    git checkout --track [remote-name]/[remote-branch-name] //简化格式
    

第一行的是传统格式，第二行的是简化格式，简化格式中，本地分支名默认和远程分支名相同。

举例：假设远程仓库中现在有一个分支dev11。

用传统格式跟踪分支后， **设置本地分支名和远程分支名相同时** ，用命令`git remote show origin`命令查看。这时候，`git push`配置为把`本地dev11分支`推送到`远程dev11分支`，`本地master分支`推送到`远程master分支`；`git pull`命令配置为`远程dev11分支`合并到`本地dev11分支`，`远程master分支`合并到`本地master分支`。

![][114]

但是假如用传统方式， **设置本地分支名和远程分支名不相同时** ，用命令`git remote show origin`命令查看。git pull命令还是2个分支（master和dev11）都能拉取并合并到本地对应的分支，但是push命令就只有master分支能推送了。因此： **最好是配置本地分支名和远程分支名一样。（所以直接用--track就默认本地分支名和远程分支名一样）**

![][115]

### 推送内容 

    git push [remote-name] [local-branch-name]:[remote-branch-name]

注意： 

1、该命令其中`local-branch-name`或者`remote-branch-name`是可以二者省略其一。

2、只有在远程服务器上有写权限，或者同一时刻没有其他人在推数据，这条命令才会如期完成任务。如果在你推数据前，已经有其他人推送了若干更新，那你的推送操作就会被驳回。你必须先把他们的更新抓取（git pull）到本地，合并到自己的项目中，然后才可以再次推送。

举例：`git push origin master`

这里git自动把master扩展成了`refs/heads/master:refs/heads/master`，意为“取出我在本地的master分支，推送到远程仓库的master分支中去”。

若想把远程的master分支叫做other分支，可以使用`git push origin master:other`。

  
### 抓取内容 

    git fetch [remote-name] //抓取远程仓库的全部内容，但是不会自动合并
    git pull //抓取远程仓库跟踪分支的内容，并自动合并到本地相应的分支
    

git pull是 **你在本地的哪个分支使用本命令，它会自动抓取本地这个分支所跟踪的远程分支的内容** ，然后 **合并** 到本地分支上，对其他分支不会抓取内容。

举例1：`git fetch origin`

此命令会到远程仓库中拉取所有你本地仓库中还没有的数据。运行完成后，你就可以在本地访问该远程仓库中的所有分支。

`git fetch`会抓取从你上次克隆以来别人上传到此远程仓库中的所有更新（或是上次fetch以来别人提交的更新）。有一点很重要，需要记住， **git fetch命令只是将远端的数据拉到本地仓库，并不自动合并到当前工作分支，需要你自己手工合并** 。如下图所示：

![][116]

在本地新建一个`git_repo`文件夹，不通过git clone命令来克隆一个远程库，而是通过`git remote add`关联远程库，并用`git fetch`来抓取远程库的所有未抓取过的数据，当抓取下来之后，必须手动合并`git merge`远程库的内容，才会显示出分支内容，否则，没有手动合并，git fetch是不会自动合并的。

举例2：git pull

实验前提：在github页面上，dev11分支创建一个新文件remote_file.txt，在master分支创建新文件master_remote_file.txt。并且用`git checkout -b`跟踪远程分支dev11和master了。接下来用git pull命令（注意：是在本地dev11分支上使用git pull命令，所以会抓取dev11分支跟踪的远程分支的内容，并合并到本地dev11分支上。

![][117]

但是这时候master分支的内容还是没有改变。如下：

![][118]

### 删除远程分支 

即删除远程仓库中的分支。

    git push [remote-name] :[remote-branch-name]

  
还记得git push命令吗？`git push [remote-name] [local-branch-name]:[remote-branch-name]`，把本地分支设置为空（冒号之前的内容），就相对于把一个空分支推送到远程仓库中的`remote-branch-name`分支去了，就相当于删除了远程仓库中的该分支。 举例：

![][119]

### 删除远程仓库本地关联  

    git remote rm [remote-name]

举例：`git remote rm origin`

### 删除远程分支本地关联 

删除远程分支关联也即删除本地跟踪分支。

    git branch -d [local-branch-name]

和删除本地分支一样的命令。

## 分支管理命令

### 创建、切换、查看分支 

假设dev是要创建分支名字。

    git branch //查看分支
    git branch dev //创建分支
    git checkout dev //切换分支
    git checkout -b dev //创建并切换分支
    

  
### 合并分支 

假设dev是要创建分支名字。

    git merge --no-ff -m "commit comment" dev //禁用Fast forward模式合并分支
    git merge dev //Fast forward模式合并分支

在`Fast forward`模式下，当删除分支后，会丢掉分支信息。

### 删除分支 

假设dev是要创建分支名字。

    git branch -d dev
    git branch -D dev //强行删除掉还未合并的分支
  
### bug分支 

由于git的分支功能强大，所以修改bug一般也是新建一个bug分支，修改后，合并到你的工作分支（master分支或者dev分支或者其他），再删除bug分支。

假设场景，当你正在dev分支上编码工作，突然接到一个需要紧急修复的bug，你应该会想建立一个bug分支，但是你dev的工作还没有提交（无论是add或者commit），这时候可以使用“存储工作现场”命令，如下：

    git stash //存储工作现场，对working directory来说

**然后首先确定要在哪个分支上去解决bug** ，比如我想要在master分支上解决bug，就先切换到master分支，然后从master分支上创建一个bug分支。（当然你也可以从别的分支上解决bug，比如dev分支上解决bug）。解决bug后合并到工作分支（就是你从哪个分支创建bug分支的那个分支）。

![][120]

注意： **git stash是针对工作区（working directory）来说的，对缓存区无效** 。所以再切换到bug分支的时候，缓存区的内容要先commit一下，要不然bug解决后，切换回工作分支，缓存区的内容就丢失了，`git stash只`存储工作区现场的内容。

    git checkout master //先切换到master分支
    git checkout -b bug //创建bug分支，并切换到bug分支上
    ... //解决bug
    git add . //在bug分支上add
    git commit -m "fix bug" //在bug分支上commit
    git checkout master //切换回master分支
    git merge --no-ff bug //在master分支上合并bug分支
    git branch -d bug //删除bug分支
    

现在bug解决了，要回到原来dev上的工作，可是时间太久了，忘记了。那么可以用一下命令查看工作现场列表。 

    git stash list //查看工作现场列表

![][121]

可以看出来工作现场保存的是dev分支上的现场。所以要先切换回dev分支，在恢复工作现场。 **如果没有先切换到dev分支，比如在master分支就去恢复工作现场，那么会执行合并dev分支的操作** 。

恢复工作现场的命令，如下：

    git stash apply //恢复工作现场
    git stash drop //删除工作现场列表中的对应项

或者直接 

    git stash pop //恢复工作现场并删除工作现场列表中的对应项

## 自定义git命令

### 忽略特殊文件 

**问题提出**：

有些时候，你必须把某些文件放到Git工作目录中，但又不能提交它们，比如保存了[数据库][122]密码的配置文件啦，等等，每次git status都会显示Untracked files ...，这样显示对于用户很不友好，应该怎么办？

**回答**：

step1：使用`.gitignore`文件，然后把要忽略的文件名填进去，git就会自动忽略这些文件。  
step2：把`.gitignore`文件加入到git仓库（目录）中。

**忽略文件的原则**是：  
1、忽略[操作系统][123]自动生成的文件，比如缩略图等；  
2、忽略编译生成的中间文件、可执行文件等；  
3、忽略你自己的带有敏感信息的配置文件，比如存放口令的配置文件。

在github里有一些`.gitignore`文件的例子，网址：[https://github.com/github/gitignore][124]

举个例子：

    # Compiled Object files
    *.slo
    *.lo
    *.o
    *.obj
    
    # Precompiled Headers
    *.gch
    *.pch
    
    # Compiled Dynamic libraries
    *.so
    *.dylib
    *.dll

### 配置git命令 

    git config //不带global参数的是对当前仓库进行配置（当前仓库）
    git config --global  //带global参数的是对本地所有仓库的配置（当前用户）

当前仓库的git配置文件放在`.git/config`文件；  
当前用户的git配置文件放在用户主目录下的一个隐藏文件`.gitconfig`。

### 搭建自己的git服务器 

github托管私有项目是要收费的，所以可能会有搭建自己的git服务器的需求，具体不详述，直接参考网址：[点击这里][125]

平常开源的我就用github，闭源的可以用git@OSC（开源中国的代码托管平台，私有仓库也免费）

# Github使用

## 账号设置

本地Git仓库和GitHub仓库之间的传输是通过SSH加密的。

为何GitHub需要SSH Key呢？因为GitHub需要识别出你推送的提交确实是你推送的，而不是别人冒充的，而Git支持SSH协议，所以，GitHub只要知道了你的公钥，就可以确认只有你自己才能推送。  
当然，GitHub允许你添加多个Key。假定你有若干电脑，你一会儿在公司提交，一会儿在家里提交，只要把每台电脑的Key都添加到GitHub，就可以在每台电脑上往GitHub推送了。

**step1：创建github账号。**（我这里的账号是chenj-freedom，后续文章会使用到）

**step2：创建SSH key。**

    ssh-keygen -t rsa -C "chenj_freedom@qq.com"

使用上面的命令创建SSH key（把邮箱换成你自己的邮箱）。命令中会要求你设置ssh key生成的路径，设置密码（passphrase）等等，命令执行完毕后，会在你设置的路径中生成.ssh目录，里面含有id_rsa（私钥）文件和id_rsa.pub文件（公钥）。 **私钥文件不能外泄，公钥文件可以公开** 。 **这个生成的key是针对每一台电脑的，每个机器根据邮箱名字来识别用户的** 。

**step3：登陆github网站，设置公钥。**  
打开"Account settings"->"SSH Keys"页面，然后点击"Add SSH Key"，在Key文本框中粘贴id_rsa.pub公钥文件的内容，点击"Add Key"，就可以看到刚添加的key了。

![][126]

>>>

![][127]

## 把本地库推送到github远程库

**step1：登陆github，点击"Create a new repo"。**  
填入仓库名字（最好和本地库名字一样），其他保持默认，创建一个新的代码仓库。创建完毕后，会显示如下提示：

![][128]

**github上的仓库有https和ssh两种连接方式，都是OK的。**

**step2：把本地库推送到github远程库上（关联）。**

    git remote add origin git@server-name:path/repo-name.git //关联远程库
    git push -u origin master //第一次推送master分支内容，-u参数指定远程库所在的服务器为默认服务器，在此例即为github服务器
    git push origin master //后续推送master分支内容不需要带参数-u
    

 **说明：**

1、 **git remote add命令就是把git@server-name:path/repo-name.git和origin关联起来** ，**origin的名字是你自己可以随意取的。**

2、`git push origin master`，就是把master分支推送到远程的origin上，也就是推送到git@server-name:path/repo-name.git仓库中。

3、`git push origin master`，这里也可以不推送master分支，可以推送其他分支。

关联远程库后，可以用`git remote -v`命令查看，如下：

![][129]

根据step1中图上面的提示，你可以create a new repository on the command line或者push an existing repository from the command line或者import code from another repository，我们根据第二种提示来把本地库关联到github远程库上（推送）。

![][130]

推送成功后，可以看到github页面中显示的远程库和本地是一样的了。

## 从github远程库克隆到本地库

    git clone git@github.com:chenj-freedom/git_repo.git

## github原理应用

![][131]

比如A有一个开源项目托管在github上，地址是git@github.com:A/git_A.git。账户A对该地址的远程仓库是有读/写的权限的，但是其他账户，比如账户B和账户C对git@github.com:A/git_A.git只有读的权限，但是没有写的权限。（ **这是github这个平台帮我们设置的git服务器的权限，每个账户只能对自己账户下的远程仓库读/写，对其他账户边的远程仓库只有读权限，没有写权限** ）

其他账户（比如账户B）要读git@github.com:A/git_A.git的仓库，可以在github网站上进行 **fork操作** ，就会克隆一个git_A仓库到自己的账户下，然后本地对自己账户下的这个git_A仓库是有读/写的权限的。

当想把自己的代码贡献给账户A，那么在github网站上进行 **pull request操作** 即可，至于账户A是否愿意接受你的代码，那是由账户A决定的。

# 其它

推荐相关git教程（此教程更为详细）：[http://www.liaoxuefeng.com/wiki/0013739516305929606dd18361248578c67b8067c8c017b000][132]

[http://git.oschina.net/progit/][133]

[http://www.yiibai.com/git/home.html][134]

</font>

[0]: http://blog.csdn.net/chenj_freedom/article/details/50543152
[1]: http://www.csdn.net/tag/git
[2]: http://www.csdn.net/tag/linux
[7]: #
[8]: #t0
[9]: #t1
[10]: #t2
[11]: #t3
[12]: #t4
[13]: #t5
[14]: #t6
[15]: #t7
[16]: #t8
[17]: #t9
[18]: #t10
[19]: #t11
[20]: #t12
[21]: #t13
[22]: #t14
[23]: #t15
[24]: #t16
[25]: #t17
[26]: #t18
[27]: #t19
[28]: #t20
[29]: #t21
[30]: #t22
[31]: #t23
[32]: #t24
[33]: #t25
[34]: #t26
[35]: #t27
[36]: #t28
[37]: #t29
[38]: #t30
[39]: #t31
[40]: #t32
[41]: #t33
[42]: #t34
[43]: #t35
[44]: #t36
[45]: #t37
[46]: #t38
[47]: #t39
[48]: #t40
[49]: #t41
[50]: #t42
[51]: #t43
[52]: #t44
[53]: #t45
[54]: #t46
[55]: #t47
[56]: #t48
[57]: #t49
[58]: #t50
[59]: #t51
[60]: #t52
[61]: #t53
[62]: #t54
[63]: #t55
[64]: http://lib.csdn.net/base/git
[65]: http://git-scm.com
[66]: http://git-scm.com/download/
[67]: http://lib.csdn.net/base/linux
[68]: https://github.com/
[69]: https://bitbucket.org/
[70]: https://gitcafe.com/
[71]: http://www.gitlab.cc/
[72]: http://git.oschina.net/
[73]: https://code.csdn.net
[74]: http://img.blog.csdn.net/20160119173915757?watermark/2/text/aHR0cDovL2Jsb2cuY3Nkbi5uZXQv/font/5a6L5L2T/fontsize/400/fill/I0JBQkFCMA==/dissolve/70/gravity/SouthEast
[75]: http://img.blog.csdn.net/20160120171556411
[76]: http://img.blog.csdn.net/20160120171943352
[77]: http://img.blog.csdn.net/20160120173019263
[78]: http://img.blog.csdn.net/20160122230457471
[79]: http://img.blog.csdn.net/20160122231232795
[80]: http://img.blog.csdn.net/20160122231356422
[81]: http://img.blog.csdn.net/20160122231702926
[82]: http://img.blog.csdn.net/20160122233146679
[83]: http://img.blog.csdn.net/20160122233448030
[84]: http://img.blog.csdn.net/20160122231752833
[85]: http://img.blog.csdn.net/20160122230250829
[86]: http://img.blog.csdn.net/20160124142003146
[87]: http://img.blog.csdn.net/20160124142957218
[88]: http://img.blog.csdn.net/20160124143001176
[89]: http://git-scm.com/download/win
[90]: http://img.blog.csdn.net/20160120145031497
[91]: http://img.blog.csdn.net/20160120112756684
[92]: http://img.blog.csdn.net/20160120113658376
[93]: http://img.blog.csdn.net/20160120145500847
[94]: http://img.blog.csdn.net/20160120145727657
[95]: http://img.blog.csdn.net/20160120151636549
[96]: http://img.blog.csdn.net/20160120153131475
[97]: http://img.blog.csdn.net/20160120151957634
[98]: http://img.blog.csdn.net/20160120153404734
[99]: http://img.blog.csdn.net/20160120183236162
[100]: http://img.blog.csdn.net/20160120154207707
[101]: http://img.blog.csdn.net/20160120175154458
[102]: http://img.blog.csdn.net/20160120182253723
[103]: http://img.blog.csdn.net/20160120154900311
[104]: http://img.blog.csdn.net/20160120183142427
[105]: http://img.blog.csdn.net/20160120161020373
[106]: http://img.blog.csdn.net/20160120185346656
[107]: http://img.blog.csdn.net/20160120185609893
[108]: http://img.blog.csdn.net/20160121095120351
[109]: http://img.blog.csdn.net/20160121095417800
[110]: http://img.blog.csdn.net/20160121101851990
[111]: http://img.blog.csdn.net/20160124131822080
[112]: http://img.blog.csdn.net/20160124231205035
[113]: http://img.blog.csdn.net/20160124232058702
[114]: http://img.blog.csdn.net/20160125104632987
[115]: http://img.blog.csdn.net/20160125105116373
[116]: http://img.blog.csdn.net/20160124234452502
[117]: http://img.blog.csdn.net/20160125110544916
[118]: http://img.blog.csdn.net/20160125111128947
[119]: http://img.blog.csdn.net/20160125000721360
[120]: http://img.blog.csdn.net/20160123175949209
[121]: http://img.blog.csdn.net/20160123005642124
[122]: http://lib.csdn.net/base/mysql
[123]: http://lib.csdn.net/base/operatingsystem
[124]: https://github.com/github/gitignore
[125]: http://www.liaoxuefeng.com/wiki/0013739516305929606dd18361248578c67b8067c8c017b000/00137583770360579bc4b458f044ce7afed3df579123eca000
[126]: http://img.blog.csdn.net/20160122100028675
[127]: http://img.blog.csdn.net/20160122100107020
[128]: http://img.blog.csdn.net/20160122101130373
[129]: http://img.blog.csdn.net/20160122104728613
[130]: http://img.blog.csdn.net/20160122102459175
[131]: http://img.blog.csdn.net/20160125153435009
[132]: http://www.liaoxuefeng.com/wiki/0013739516305929606dd18361248578c67b8067c8c017b000
[133]: http://git.oschina.net/progit/
[134]: http://www.yiibai.com/git/home.html
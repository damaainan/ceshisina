## 聊聊 Git 原理

来源：[https://mp.weixin.qq.com/s/uBkRUkUKrVHnf4lt9vASIA](https://mp.weixin.qq.com/s/uBkRUkUKrVHnf4lt9vASIA)

时间 2018-08-20 08:44:26


说起Git，相信大家都很熟悉了，毕竟作为程序猿，每天的业余时间除了吃饭睡觉就是逛一下全世界最大的开（tong）源（xing）代（jiao）码（you）网站GitHub了。在那里Git是每个人所要具备的最基本的技能。今天我们不聊Git的基本应用，来聊一聊Git的原理。 <!-- more -->

Git给自己的定义是一套内存寻址文件系统，当你在一个目录下执行git init命令时，会生成一个.git目录，它的目录结构是这样的：

```
.git/
├── branches
├── config
├── description
├── HEAD
├── hooks
│   ├── applypatch-msg.sample
│   ├── commit-msg.sample
│   ├── post-update.sample
│   ├── pre-applypatch.sample
│   ├── pre-commit.sample
│   ├── prepare-commit-msg.sample
│   ├── pre-push.sample
│   ├── pre-rebase.sample
│   └── update.sample
├── info
│   └── exclude
├── objects
│   ├── info
│   └── pack
└── refs
    ├── heads
    └── tags
```

其中branches目录已经不再使用，description文件仅供GitWeb程序使用，config文件保存了项目的配置。

需要我们重点关注的是HEAD和index文件以及objects和refs目录。其中index中保存了暂存区的一些信息，这里不做过多介绍。


#### objects目录   

这个目录是用来存储Git对象的（包括tree对象、commit对象和blob对象），对于一个初始的Git仓库，objects目录下只有info和pack两个子目录，并没有常规文件。随着项目的进行，我们创建的文件，以及一些操作记录，都会作为Git对象被存储在这个目录下。

在该目录下，所有对象都会生成一个文件，并且有对应的SHA-1校验和，Git会创建以校验和前两位为名称的子目录，并以剩下的38位为名称来保存文件。

接下来让我们一起看一下当我们进行一次提交时，Git具体做了哪些事情。

```
$ echo 'test content'>test.txt
$ git add .
```

执行上述命令后，objects目录结构如下：

```
.git/objects/
├── d6
│   └── 70460b4b4aece5915caf5c68d12f560a9fe3e4
├── info
└── pack
```

这里多了一个文件夹，如上面所述，这个就是Git为我们创建的一个对象，我们可以使用底层命令来看一下这个对象的类型以及它存储的是什么。

```
$ git cat-file -t d670460b4b4aece5915caf5c68d12f560a9fe3e4
blob
$ git cat-file -p d670460b4b4aece5915caf5c68d12f560a9fe3e4
test content
```

可以看到，这是一个blob对象，存储内容就是我们刚刚创建的文件的内容。接下来继续执行提交操作。

```
$ git commit -m 'test message'
[master (root-commit) 2b00dca] test message
 1 file changed, 1 insertion(+)
 create mode 100644 test.txt
 $ tree .git/objects/
.git/objects/
├── 2b
│   └── 00dcae50af70bb5722033b3fe75281206c74da
├── 80
│   └── 865964295ae2f11d27383e5f9c0b58a8ef21da
├── d6
│   └── 70460b4b4aece5915caf5c68d12f560a9fe3e4
├── info
└── pack
```

此时objects目录下又多了两个对象。再用cat-file命令来查看一下这两个文件。

```
$ git cat-file -t 2b00dcae50af70bb5722033b3fe75281206c74da
commit
$ git cat-file -p 2b00dcae50af70bb5722033b3fe75281206c74da
tree 80865964295ae2f11d27383e5f9c0b58a8ef21da
author jackeyzhe <jackeyzhe59@163.com> 1534670725 +0800
committer jackeyzhe <jackeyzhe59@163.com> 1534670725 +0800

test message
$ git cat-file -t 80865964295ae2f11d27383e5f9c0b58a8ef21da
tree
$ git cat-file -p 80865964295ae2f11d27383e5f9c0b58a8ef21da
100644 blob d670460b4b4aece5915caf5c68d12f560a9fe3e4    test.txt
```

可以看到一个是commit对象，一个是tree对象。commit对象通常包括4部分内容：


* 工作目录快照的Hash，即tree的值

    
* 提交的说明信息

    
* 提交者的信息

    
* 父提交的Hash值


由于我是第一次提交，所以这里没有父提交的Hash值。

tree对象可以理解为UNIX文件系统中的目录，保存了工作目录的tree对象和blob对象的信息。接下来我们再来看一下Git是如何进行版本控制的。

```
echo 'version1'>version.txt
$ git add .
$ git commit -m 'first version'
[master 702193d] first version
 1 file changed, 1 insertion(+)
 create mode 100644 version.txt
$ echo 'version2'>version.txt
$ git add .
$ git commit -m 'second version'
[master 5333a75] second version
 1 file changed, 1 insertion(+), 1 deletion(-)
$ tree .git/objects/
.git/objects/
├── 1f
│   └── a5aab2a3cf025d06479b9eab9a7f66f60dbfc1
├── 29
│   └── 13bfa5cf9fb6f893bec60ac11d86129d56fcbe
├── 2b
│   └── 00dcae50af70bb5722033b3fe75281206c74da
├── 53
│   └── 33a759c4bdcdc6095b4caac19743d9445ca516
├── 5b
│   └── dcfc19f119febc749eef9a9551bc335cb965e2
├── 70
│   └── 2193d62ffd797155e4e21eede20897890da12a
├── 80
│   └── 865964295ae2f11d27383e5f9c0b58a8ef21da
├── d6
│   └── 70460b4b4aece5915caf5c68d12f560a9fe3e4
├── df
│   └── 7af2c382e49245443687973ceb711b2b74cb4a
├── info
└── pack
$ git cat-file -p 1fa5aab2a3cf025d06479b9eab9a7f66f60dbfc1
100644 blob d670460b4b4aece5915caf5c68d12f560a9fe3e4    test.txt
100644 blob 5bdcfc19f119febc749eef9a9551bc335cb965e2    version.txt
$ git cat-file -p 2913bfa5cf9fb6f893bec60ac11d86129d56fcbe
100644 blob d670460b4b4aece5915caf5c68d12f560a9fe3e4    test.txt
100644 blob df7af2c382e49245443687973ceb711b2b74cb4a    version.txt
```

Git将没有改变的文件的Hash值直接存入tree对象，对于有修改的文件，则会生成一个新的对象，将新的对象存入tree对象。我们再来看一下commit对象的信息。

```
$ git cat-file -p 5333a759c4bdcdc6095b4caac19743d9445ca516
tree 2913bfa5cf9fb6f893bec60ac11d86129d56fcbe
parent 702193d62ffd797155e4e21eede20897890da12a
author jackeyzhe <jackeyzhe59@163.com> 1534672270 +0800
committer jackeyzhe <jackeyzhe59@163.com> 1534672270 +0800

second version
$ git cat-file -p 702193d62ffd797155e4e21eede20897890da12a
tree 1fa5aab2a3cf025d06479b9eab9a7f66f60dbfc1
parent 2b00dcae50af70bb5722033b3fe75281206c74da
author jackeyzhe <jackeyzhe59@163.com> 1534672248 +0800
committer jackeyzhe <jackeyzhe59@163.com> 1534672248 +0800

first version
```

此时的commit对象已经有parent信息了，这样我们就可以顺着parent一步步往回进行版本回退了。不过这样是比较麻烦的，我们一般习惯用的是git log查看提交记录。


#### refs目录   

在介绍refs目录之前，我们还是先来看一下该目录结构

```
$ tree .git/refs/
.git/refs/
├── heads
│   └── master
└── tags

2 directories, 1 file
$ cat .git/refs/heads/master 
5333a759c4bdcdc6095b4caac19743d9445ca516
```

在一个刚刚被初始化的Git仓库中，refs目录下只有heads和tags两个子目录，由于我们刚刚有过提交操作，所以git为我们自动生成了一个名为master的引用。master的内容是最后一次提交对象的Hash值。看到这里大家一定在想，如果我们对每次提交都创建一个这样的引用，不就不需要记住每次提交的Hash值了，只要看看引用的值，复制过来就可以退回到对应版本了。没错，这样是可以方便的退回，但是这样做的意义不大，因为我们并不需要频繁的退回，特别是比较古老的版本，退回的概率更是趋近于0。Git用这个引用做了更有意义的事，那就是分支。

当我新建一个分支时，git就会在.git/refs/heads目录下新建一个文件。当然新建的引用还是指向当前工作目录的最后一次提交，一般情况下我们不会主动去修改这些引用文件，不过如果一定要修改，Git为我们提供了一个update-ref命令。可以改变引用的值，使其指向不同的commit对象。

tags目录下的文件存储的是标签对应的commit，当为某次提交打上一个tag时，tags目录下就会被创建出一个命名为tag名的文件，值是此次提交的Hash值。


#### HEAD   

新建分支的时候，Git是怎么知道我们当前是在哪个分支的，Git又是如何实现分支切换的呢？答案就在HEAD这个文件中。

```
$ cat .git/HEAD 
ref: refs/heads/master
$ git checkout test 
Switched to branch 'test'
$ cat .git/HEAD 
ref: refs/heads/test
```

很明显，HEAD文件存储的就是我们当前分支的引用，当我们切换分支后再次进行提交操作时，Git就会读取HEAD对应引用的值，作为此次commit的parent。我们也可以通过symbolic-ref命令手动设置HEAD的值，但是不能设置refs以外的形式。


#### Packfiles   

到这里我们在文章开头所说的重点关注的目录和文件都介绍完毕了。但是作为一个文件系统，还存在一个问题，那就是空间。前文介绍过，当文件修改后进行提交时，Git会创建一份新的快照。这样长久下去，必定会占用很大的存储空间。而比较古老的版本的价值已经不大，所以要想办法清理出足够的空间供用户使用。

好消息是，Git拥有自己的gc（垃圾回收）方法。当仓库中有太多松散对象时，Git会调用git gc命令（当然我们也可以手动调用这个命令），将这些对象进行打包。打包后会出现两个新文件：一个idx索引文件和一个pack文件。索引文件包含了packfile的偏移信息，可以快速定位到文件。打包后，每个文件最新的版本的对象存的是完整的文件内容。而之前的版本只保存差异。这样就达到了压缩空间的目的。


#### Ending   

本文只介绍了Git的原理，如果对Git基本操作不熟悉的话，可以点击阅读原文学习 Pro Git      。


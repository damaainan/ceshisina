# Git 原生钩子的深度优化

 时间 2017-11-22 20:25:16  Charlie's Reflections

原文[http://forcemz.net/git/2017/11/22/GitNativeHookDepthOptimization/][2]


## 前言

Git 是最流行的版本控制工具，和大多数版本控制工具一样，Git 也拥有钩子特性，用户可以利用钩子实现一些附加功能，在 《Pro Git v2》 中，对钩子类型，消息格式等有详细介绍： [8.3 Customizing Git - Git Hooks][4] 。 

代码托管平台也会使用钩子，一般是使用 **Server-Side Hooks** 。包括 **pre-receive****update****post-receive** 。 

为什么要使用钩子？我们得先思考目前的 git 代码托管平台架构大多数是无状态的，也就是说 Web 是 Web, git 是 git。究其原因，目前服务器上的 git 传输实现基本上还是使用 git 命令做 smart 传输，这种传输本质上是一对命令做输入输出交换，类似 inetd。这种协议的缺陷在于：在子进程中传输的数据是隔离的，不透明的，不可控的。启动 Git 子命令后，权限控制，大文件检测等操作已不是 SSH 或者 HTTP 服务器能控制的了。当然，劫持网络数据进行深度分析是可以的，但那相当于重新实现一套 git。并且，这种性能上的损失也是平台不可接受的。 代码托管平台绝不能裹足不前，对于不适合的数据推送当然要拒绝他！幸运的是，我们还可以使用 hook 来阻挡不合适的数据推送了。

## Gitlab 的 Update 钩子

码云最初利用 Gitlab 搭建起来，而钩子的使用策略是 Gitlab 早先的策略，即使用 **Update** 钩子。Sidekiq, 以及后来推出的分支保护功能以及大文件检测，都是利用 **Update** 钩子实现的。这块代码是在 Gitlab-Shell 中。保护分支实际上是在运行 Update 钩子时，请求 Gitlab 判断引用是否被允许修改。而大文件检测则是使用 **Commit-Between** 进行一个回溯 **diff** ，深度最大为 _20_ 。Sidekiq 则是插入 redis 队列实现的。 

我们知道推送代码时需要在远程服务器中运行 git-receive-pack 命令， recieve-pack 会在整个生命周期运行三种钩子，也就是前面所说的 **Server-Side Hooks** （这里当然有个前提，钩子不存在就不会被调用）， **Update** 是第二个被调用的钩子。receive-pack 将使用如下命令执行钩子： 

    $GIT_DIR/hooks/update refname oldrev newrev

每更新一个引用执行一次，当钩子返回值不为零时，当前引用不会被更新。

新建分支时，oldrev 值为 0000000000000000000000000000000000000000 。 

删除分支时，newrev 值为 0000000000000000000000000000000000000000 。 

既然每一个引用都会执行一次，那么我们试想一下，一次性推送多个分支，并且分支都是新建分支，那么可以预见，无论是 **Commit-Between Diff** 还是保护分支还是任务队列的消耗时间都是成倍增加的。事实上也是如此，我们在测试服务器上推送大存储库，多分支，多 commit 时就发现了这个问题。 

由于 **Commit-Between Diff** 深度的限制，一个精心构造的大文件是能够被推送到服务器而不被拒绝。 

## 完全检测的 Git 原生钩子

既然 Update 钩子并不好，我们就得使用替代方案。 **pre-receive** 是第一个被 receive-pack 调用的钩子，没有额外的命令行参数，无论更新多少引用都只会调用一次，引用列表会被 receive-pack 写入到 pre-receive 钩子进程的标准输入。格式原语如下： 

    refname SP oldrev SP newrev LF
    refnameN SP oldrev SP newrev LF

这个时候，我们可以将保护分支功能移入到此钩子，使用此钩子实现保护分支与 update 不一致的是同时推送多个引用，一旦有一个分支被拒绝，所有的分支都会被拒绝，而 update 钩子并不是如此。不过带来的好处是显而易见的，在推送镜像存储库，多分支项目时，可以避免多次发起对 Gitlab 的网络请求。

post-receive是最后被调用的钩子，格式与 **pre-receive** 完全一致，我们不能使用 pre-receive 更新 Sidekiq ，这是由于只有再在调用 update 钩子后，引用才会被更新，若 Sidekiq 在 pre-receive 钩子执行期间就响应可能会导致错误，因此在 post-receive 中更新 Sidekiq（redis）才是最安全的，在 post-receive 中执行 redis 命令还可以利用 KeepAlive 减少对 redis 的请求次数，从而优化服务器内部的网络。 

update 钩子最后的功能只剩大文件检测了。如果将此功能移除，就完全不再需要 update 钩子。

在前面的博客：Git 存储格式与运用，我正是直接解析 pack/idx 文件格式来实现大文件检测。 

一开始，我还使用 zip 解压松散文件读取文件大小，然后在 pack 文件中使用 libgit2 解析原始文件大小。实际上这种事情意义并不大，远程服务器上的存储库是一个 _bare_ 存储库，所有的文件都是被压缩的，我们在统计存储库大小的时候也只是统计裸仓库的 _objects_ 目录占用空间大小，因此，我们不需要检测原始文件大小，这样一来，钩子能够避免检测原始大小带来的性能损失。（实际上检测原始大小有个策略，只有超过一定值的对象才会检测原始大小用来判断文件是否超大。） 

原生钩子使用 C++ 开发，经测试，效率远比 Update 钩子效率高，实际上在解析 commit 的过程中就避免不了性能损失了。

这个时候的原生钩子还有一些不足，比如一些大的 pack 文件需要频繁检测，因此，我还实现了一个缓存机制，将 pack 检测到的数据写入到缓存文件中，避免频繁检测对应的 pack。这一点，我们需要知道，pack 文件一旦内容改变，名字也会改变，名字格式为 pack-$sha1.pack 。 

## 原生钩子使用环境隔离特性

在 Git 2.11.0 时，git 改进了其推送的工作流程，增加了 [**Quarantine Environment**][5] 机制，此时，receive-pack 将会把所有推送的对象放置在隔离的临时目录中，一旦推送被接受才会将对象移动到常规的对项目录，环境隔离的机制在整个 pre-receive 钩子的生命周期中是有效的。启动 update 钩子之前就会失效。 

因此，我将原生钩子使用环境隔离机制进行改造。结果显而易见，pack 文件不用完全检测，只需要检测隔离目录中的 pack。pack 缓存也不再需要。对于大文件检测的效果更明显，比如超出警告的大文件只会在第一次推送时发出警告，而不必每次警告，提高了用户体验。

当推送被拒绝时，临时目录会被删除，这样能够避免重复的失败推送造成存储库的无效膨胀，存储库的无效膨胀会占用用户的配额，也会给服务器的 git gc 带来过重的压力。

这种特性也决定了 update 钩子无法胜任此工作。

而 Gitlab 也支持了此特性 ： [Accept environment variables from the pre-receive script][6]

环境隔离需要理解几个环境变量：

    $GIT_QUARANTINE_PATH
    $GIT_OBJECT_DIRECTORY
    $GIT_ALTERNATE_OBJECT_DIRECTORIES

我只用到了 GIT_QUARANTINE_PATH 。 

在 git 2.15.0 之前的版本中，如果在隔离目录中运行 git update-ref 会损坏存储库，之后的版本已经拒绝了 git update-ref 在隔离目录中运行。

## 延时读的原生钩子

我在解析 pack 文件时，设计了一个 ObjectIndex 结构，读取 index 文件中关于 pack 中的文件数目后，使用一个 vector 存储对象。将所有的偏移依次读取，然后通过 std::sort 将偏移按大到小排序，依次相减，就得到对应的对象压缩后的体积。然后判断是否超限。 

    struct ObjectIndex{
        uint8_t sha1[20];
        uint32_t offset;
    }

在使用 std::vector 之前，使用 std::list 存储对象，效率不高，在分析 FreeBSD 的 1G 多大的存储库时，在我的破笔记本上跑出了 9s 耗时，对象 300 多万。太慢了，而改成 vector 后，耗时为 3s。 

最近，笔者决定优化一下，第一步是将比较函数的内联。最初的比较函数如下：

    bool objectidxcompare(const ObjectIndex &first, const ObjectIndex &second) {
      return (first.offset > second.offset);
    }

我们知道，函数调用是需要耗费时间的，随着对象数目增多，这种影响愈加明显。于是我将 ObjectIndex 改造成如下： 

    struct ObjectIndex {
      /// DON't Modify
      bool operator<(const ObjectIndex &o) { return offset > o.offset; }
      uint8_t sha1[20];
      uint32_t index{0};
    };

通过内联，运行时间减少了 13.8%。这还不够，std::sort 内部使用了 std::swap 交换对象，而 ObjectIndx::sha1 的交换需要拷贝，并且读取 sha1 值也是需要系统调用的。为什么不先不读取 sha1 值，而是保存 sha1 值的 index。ObjectIndex 格式改成如下：

    struct ObjectIndex {
      /// DON't Modify
      bool operator<(const ObjectIndex &o) { return offset > o.offset; }
      uint32_t offset{0};
      uint32_t index{0};
    };

这样，我们不再读取 sha1 值，需要 sha1 值的时候，再通过偏移计算 sha1 在 idx 文件中的位置。

    #define GIT_SHA1_RAWSZ 20
    // so buffer >41,
    char *sha1_to_hex_r(char *buffer, const unsigned char *sha1) {
      static const char hex[] = "0123456789abcdef";
      char *buf = buffer;
      int i;
    
      for (i = 0; i < GIT_SHA1_RAWSZ; i++) {
        unsigned int val = *sha1++;
        *buf++ = hex[val >> 4];
        *buf++ = hex[val & 0xf];
      }
      *buf = '\0';
    
      return buffer;
    }
    
    inline const char *Sha1FromIndex(FILE *fp, char *buf, std::uint32_t i) {
      unsigned char sha1__[20];
      constexpr int offsetbegin = 4 + 4 + 4 + 255 * 4;
      fseek(fp, offsetbegin + i * 20, SEEK_SET);
      if (fread(sha1__, 1, 20, fp) != 20) {
        return "unkown";
      }
      ::sha1_to_hex_r(buf, sha1__);
      return buf;
    }

这样真的减少了一半的时间。比如 Linux 内核源码 1.9GB 数据，562 W 对象，从 1442 毫秒减少到 700 多毫秒。内存占用也减少了 2/3。不要小看 16Byte 字节的节省，几百万个对象节省的空间就很客观了。

## 利用内存布局减少系统调用次数

就函数调用而言，要尽可能的减少频繁调用的函数的调用次数，特别是达到百万级别的，在读取偏移时就可以一次性读取，于是我将读取偏移改为一次性读写，利用 vector 预先分配的内存，核心代码如下：

    std::vector<ObjectIndex> objs(counts);
      auto objsraw = objs.data();
      auto bufc = reinterpret_cast<char *>(objsraw);
      /// 4*counts
      auto binteger =
          reinterpret_cast<int *>(bufc + sizeof(ObjectIndex) * counts / 2);
      if (fread(binteger, 4, counts, fp) != counts) {
        console::Printeln("fread error ");
        fclose(fp);
        return false;
      }
      for (uint32_t i = 0; i < counts; i++) {
        /// DON'T  change the order of operations
        objsraw[i].offset = ntohl(binteger[i]);
        objsraw[i].index = i;
      }
    
      std::sort(objs.begin(), objs.end());

这里一定要注意，index 的填充一定要后于偏移的计算。

这次优化比前面的 700 多毫秒减少了 100 多毫秒。

## 检测何时引入大文件

GitNativeHook 为了性能还是损失了一个功能，无法检测何时引入了大文件，大文件的文件名是什么，这个时候大家可以使用我开发的 git-analyze 工具去检测什么时候引入了大文件以及文件名： [Git-Analyze][7]

## 最后

优化是无止尽的。如果大家有更好的方案可以与我讨论。


[2]: http://forcemz.net/git/2017/11/22/GitNativeHookDepthOptimization/

[4]: https://git-scm.com/book/en/v2/Customizing-Git-Git-Hooks
[5]: https://git-scm.com/docs/git-receive-pack#_quarantine_environment
[6]: https://gitlab.com/artofhuman/gitlab-ce/commit/022242c30fe463d2b82c18c687088786b306415f
[7]: https://gitee.com/oscstudio/git-analyze
# Git 巨型存储库的解决方案

 时间 2017-12-06 18:00:00  

原文[http://forcemz.net/git/2017/12/06/MassiveRepositoriesAndGit/][2]


## 前言

通常来说，分布式版本控制系统适合体积较小的存储库， [分布式版本控制系统][4] 意味着存储库和工作目录都放置在开发者自己的机器上，当开发者需要克隆一个巨大的存储库时，为了获得完整的拷贝，版本控制软件不得不从远程服务器上下载大量的数据。这是分布式版本控制系统最大的缺陷之一。 

这种缺陷并不会阻碍 git 的流行，自 2008 年以来，git 已经成为事实上的版本控制软件的魁首，诸如 GCC 1 ，LLVM 2 这样的基础软件也已迁移到或者正在迁移到 git。那么 git 如何解决这种大存储库的麻烦呢？ 

## 浅克隆和稀疏检出

很早之前，我的构建 LLVM 的时候，都使用 svn 去检出 LLVM 源码，当时并不知道 git 能够支持浅克隆。后来从事代码托管开发，精通git 后，索性在 Clangbuilder 3 中使用 git 浅克隆获取 LLVM 源码。 

浅克隆意味着只克隆指定个数的 commit，在 git 克隆的时候使用 --depth=N 参数就能够支持克隆最近的 N 个 commit，这种机制对于像 CI 这样的服务来说，简直是如虎添翼。 

    --depth <depth>
               Create a shallow clone with a history truncated to the specified number of commits. Implies
               --single-branch unless --no-single-branch is given to fetch the histories near the tips of all branches.

与常规克隆不同的是，浅克隆可能需要多执行一次请求，用来协商 commit 的深度信息。

在服务器上支持浅克隆一般不需要做什么事。如果使用 _git-upload-pack_ 命令实现克隆功能时，对于 HTTP 协议要特殊设置，需要及时关闭 _git-upload-pack_ 的输入。否则，git-upload-pack 会阻塞不会退出。对于 Git 和 SSH 协议，完全不要考虑那么多，HTTP协议是 **Request–Respone** 这种类型的，而 Git 和 SSH 协议则没有这个限制。 

而稀疏检出指得是在将文件从存储库中检出到目录时，只检出特定的目录。这个需要设置 .git/info/sparse-checkout 。稀疏检出是一种客户端行为，只会优化用户的检出体验，并不会减少服务器传输。 

## Git LFS 的初衷

Git 实质上是一种文件快照系统。创建提交时会将文件打包成新的 blob 对象。这种机制意味着 git 在管理大文件时是非常占用存储的。比如一个 1GB 的 PSD 文件，修改 10 次，存储库就可能是 10 GB。当然，这取决于 zip 对 PSD 文件的压缩率。同样的，这种存储库在网络上传输，需要耗费更多的网络带宽。

对于 Github 而言，大文件耗费了他们大量的存储和带宽。Github 团队于是在 2015 年推出了 Git LFS，在前面的博客中，我介绍了如何实现一个 Git LFS 服务器 4 ，这里也就不再多讲了。 

## GVFS 的原理

好了，说到今天的重点了。微软有专门的文件介绍了 **《Git 缩放》**5**《GVFS 设计历史》**6 ，相关的内容也就不赘述了。 

GVFS 协议地址为： [The GVFS Protocol (v1)][5]

GVFS 目前只设计和实现了 HTTP 协议，我将其 HTTP 接口整理如下表：

Method | URL | Body | Accept 
-|-|-|-
GET | /gvfs/config NA application/json, gvfs not care 
GET | /gvfs/objects/{objectId} NA application/x-git-loose-object 
POST | /gvfs/objects Json Objects application/x-git-packfile; application/x-gvfs-loose-objects(cache server) 
POST | /gvfs/sizes JOSN Array application/json 
GET | /gvfs/prefetch[?lastPackTimestamp={secondsSinceEpoch}] NA application/x-gvfs-timestamped-packfiles-indexes 

GVFS 最初要通过 /gvfs/config 接口去判断远程服务器对 GVFS 的支持程序，以及缓存服务器地址。获取引用列表依然需要通过 GET /info/refs?service=git-upload-pack 去请求远程服务器。 

    //https://github.com/Microsoft/GVFS/blob/b07e554db151178fb397e51974d76465a13af017/GVFS/FastFetch/CheckoutFetchHelper.cs#L47
                GitRefs refs = ;
                string commitToFetch;
                if (isBranch)
                {
                    refs = this.ObjectRequestor.QueryInfoRefs(branchOrCommit);
                    if (refs == )
                    {
                        throw new FetchException("Could not query info/refs from: {0}", this.Enlistment.RepoUrl);
                    }
                    else if (refs.Count == 0)
                    {
                        throw new FetchException("Could not find branch {0} in info/refs from: {1}", branchOrCommit, this.Enlistment.RepoUrl);
                    }
    
                    commitToFetch = refs.GetTipCommitId(branchOrCommit);
                }
                else
                {
                    commitToFetch = branchOrCommit;
                }

拿到引用列表后才能开始 GVFS clone。分析 POST /gvfs/objects 接口规范，我们知道，最初调用此接口时，只会获得特定的 commit 以及 tree 对象。引用列表返回的都是 commit id。拿到 tree 对象后，就可以拿到 tree 之中的 blob id。通过 POST /gvfs/sizes 可以拿到需要获得的对象的原始大小，通常而言， /gvfs/sizes 请求的对象的类型一般都是 blob，在 GVFS 源码的 QueryForFileSizes 正是说明了这一点。实际上一个完整功能的 GVFS 服务器实现这三个接口就可以正常运行。 

POST /gvfs/objects 请求类型： 

    {
        "objectIds":[
            "e402091910d6d71c287181baaddfd9e36a511636",
            "7ba8566052440d81c8d50f50d3650e5dd3c28a49"
        ],
        "commitDepth":2
    }
    

    struct GvfsObjects{
        std::vector<std::string> objectIds;
        int commitDepth;
    };

    POST /gvfs/sizes    [
            "e402091910d6d71c287181baaddfd9e36a511636",
            "7ba8566052440d81c8d50f50d3650e5dd3c28a49"
    ]
    

对于 Loose Object，目前的 git 代码托管平台基本上都不支持哑协议了，GVFS 这里支持 loose object 更多的目的是用来支持缓存，而 prefetch 的道理类似，像 Windows 源码这样体积的存储库，一般的代码托管平台优化策略往往无效。每一次计算 commit 中的目录布局都是非常耗时的，因此，GVFS 在设计之处都在尽量的利用缓存服务器。

## 使用 Libgit2

据我所知，国内最早实现 gvfs 服务器的是华为开发者庄表伟，具体介绍在简书上： [《GVFS协议与工作原理》][6] 。我在实现 gvfs 的过程也参考了他的实现。与他的基于 rack 用 git 命令行实现的服务器不同的是，我是使用 libgit2 实现一个 git-gvfs 命令行，然后被 git-srv 和 bzsrv 调用。采取这种机制一是使用 git 命令行需要多个命令的组合，无论是 git-srv 还是基于 go 的 bzsrv 还要处理各种各样的命令，不利于细节屏蔽。二来是我对 libgit2 已经比较熟，并且也对 git 的存储协议，pack 格式比较了解。 

git-srv 是码云分布式 git 传输的核心组件，无论是 HTTP 还是 SSH 还是 Git 协议，其传输数据都由其前端转发到 git-srv，最后通过 git-* 命令实现，支持的命令有 git-upload-pack git-upload-archive git-receive-pack git-archive，如果直接使用 git 命令实现 gvfs 功能不吝于重写 git-srv，很容易对线上的服务造成影响。简单的方法就是使用 libgit2 实现一个 git-gvfs cli.

git-gvfs 命令的基本用法是：

    git-gvfs GVFS impl cli
    usage: [command] [args] gitdir
        config         show server config
        sizes           input json object ids, show those size
        pack-objects   pack input oid's objects
        loose-object   --oid; send loose object
        prefetch       --lastPackTimestamp; prefetch transfer

git-gvfs config 命令用于显示服务器配置，在 brzo 或者 bzsrv 就可以被拦截，这里保留。 

git-gvfs sizes 命令对应 POST /gvfs/sizes 请求，请求体写入到 git-gvfs 的 _stdin_ ，git-gvfs 使用 nlohmann::json 解析请求，然后使用 git_odb 去查询所有输入对象的未压缩大小。 

pack-objects 命令对应 POST /gvfs/objects 请求，输入的对象是 commit 时，使用 commitDepth 的长度回溯遍历，取第一个 parent commit。如果对象的类型不是 blob，则向下解析，直到树没有子树。构建 pack 可以使用 [git_packbuilder][7] ，写入文件使用 [git_packbuilder_write][8] ，直接写入 stdout 用 [git_packbuilder_foreach][9] 。为了支持缓存，要先写入磁盘，然后从磁盘读取再写入到 **stdout** 。 

loose-object 即读取松散对象写入到标准输出。 

prefetch 对应 GET /gvfs/prefetch[?lastPackTimestamp={secondsSinceEpoch}]| 这里核心是扫描 gvfs 临时目录。将所有某个时间点之后创建的 pack 文件打包成一个 pack。这里需要对 pack 对象进行遍历，最初的 pack 遍历我是使用 Git Native Hook 的机制，但后来发现 odb 边界导致性能不太理想，于是我使用 git_odb_new 新建 odb，然后使用 git_odb_backend_one_pack 创建 git_odb_backend 打开一个个的 pack 文件，使用 git_odb_add_backend 将 odb_backend 添加到 odb，这时候就可以对 odb 进行遍历，获得所有的对象，要创建 packbuilder 需要 git_repositroy 对象，因此，可以使用 git_repository_warp_odb 创建一个 fake repo. 代码片段如下： 

    class FakePackbuilder {
    private:
      git_odb *db{nullptr};
      git_repository *repo{nullptr};
      git_packbuilder *pb{nullptr};
      std::vector<std::string> pks;
      bool pksremove{false};
      std::string name;
      /// skip self
      inline void removepkidx(const std::string &pk) {
        if (pk.size() > name.size() &&
            pk.compare(pk.size() - name.size(), name.size(), name) != 0) {
          auto idxfile = pk.substr(0, pk.size() - 4).append("idx");
          std::remove(pk.c_str()); ///
          std::remove(idxfile.c_str());
        }
      }
    
    public:
      FakePackbuilder() = default;
      FakePackbuilder(const FakePackbuilder &) = delete;
      FakePackbuilder &operator=(const FakePackbuilder &) = delete;
      ~FakePackbuilder() {
        if (pb != nullptr) {
          git_packbuilder_free(pb);
        }
        if (repo != nullptr) {
          git_repository_free(repo);
        }
        if (db != nullptr) {
          git_odb_free(db);
        }
        if (pksremove) {
          for (auto &p : pks) {
            removepkidx(p);
          }
        }
      }
      std::vector<std::string> &Pks() { return pks; }
      const std::vector<std::string> &Pks() const { return pks; }
      /// packbuilder callback
      static int PackbuilderCallback(const git_oid *id, void *playload) {
        auto fake = reinterpret_cast<FakePackbuilder *>(playload);
        git_odb_object *obj;
        if (git_odb_read(&obj, fake->db, id) != 0) {
          return -1;
        }
        if (git_odb_object_type(obj) != GIT_OBJ_BLOB) {
          if (git_packbuilder_insert(fake->pb, id, nullptr) != 0) {
            git_odb_object_free(obj);
            return 1;
          }
        }
        git_odb_object_free(obj);
        return 0;
      }
    
      std::string Packfilename(const git_oid *id) {
        return std::string("pack-").append(git_oid_tostr_s(id)).append(".pack");
      }
      bool Repack(const std::string &gvfsdir, std::string &npk) {
        if (git_odb_new(&db) != 0) {
          fprintf(stderr, "new odb failed\n");
          return false;
        }
        for (auto &p : pks) {
          auto idxfile = p.substr(0, p.size() - 4).append("idx");
          git_odb_backend *backend = nullptr;
          if (git_odb_backend_one_pack(&backend, idxfile.c_str()) != 0) {
            auto err = giterr_last();
            fprintf(stderr, "%s\n", err->message);
            return false;
          }
          /// NOTE backend no public free fun ?????
          if (git_odb_add_backend(db, backend, 2) != 0) {
            // backend->free(backend);///
            if (backend->free != nullptr) {
              backend->free(backend);
            }
            return false;
          }
        }
        if (git_repository_wrap_odb(&repo, db) != 0) {
          fprintf(stderr, "warp odb failed\n");
          return false;
        }
        if (git_packbuilder_new(&pb, repo) != 0) {
          fprintf(stderr, "new packbuilder failed\n");
          return false;
        }
        if (git_odb_foreach(db, &FakePackbuilder::PackbuilderCallback, this) != 0) {
          return false;
        }
        if (git_packbuilder_write(pb, gvfsdir.c_str(), 0, nullptr, nullptr) != 0) {
          return false;
        }
    
        auto id = git_packbuilder_hash(pb);
        if (id == nullptr) {
          return false;
        }
        pksremove = true;
        name = Packfilename(id);
        npk.assign(gvfsdir).append("/").append(name);
        return true;
      }
    };

上述 FakePackBuilder 还支持删除旧的 pack，新的 pack 产生，旧的几个 pack 文件就可以被删除了。

在 git-gvfs 稳定后，或许会提供一个开源跨平台版本。

## GVFS 应用分析

GVFS 有哪些应用场景？

实际上还是很多的。比如，我曾经帮助同事将某客户的存储库由 svn 迁移到 git，迁移的过程很长，最后使用 svn-fast-export 实现，转换后，存储库的体积达到 80 GB。就目前码云的线上限制而言，这种存储库都无法上传上去，而私有化，这种存储库同样会给使用者带来巨大的麻烦。如果使用 GVFS，这就相当于只下载目录结构，浅表的 commit，然后需要时才下载所需的文件，好处显而易见。随着码云业务的发展，这种拥有历史悠久的存储库的客户只会越来越多，GVFS 或许必不可少了。

## 相关信息

在微软的 GVFS 推出后，Google 开发者也在修改 Git 支持部分克隆 7 ，用来改进巨型存储库的访问体验。代码在 Github 上 8 目前还处于开发过程中。部分克隆相对于 GVFS 最大的不足可能是 FUFS。而 GVFS 客户端仅支持 Windows 10 14393 也正是由于这一点，GVFS 正因这一点才被叫做 GVFS (Git Virtual Filesystem)。FUFS 能够在目录中呈现未下载的文件，在文件需要读写时，由驱动触发下载，这就是其优势。 

## 最后

回过头来一想，在支持大存储库的改造上，git 越来越不像一个分布式版本控制系统，除了提交行为还是比较纯正。软件的发展正是如此，功能的整合使得界限变得不那么清晰。

## 链接

1. [Moving to git][10]
1. [Moving LLVM Projects to GitHub][11]
1. [Checkout LLVM use –depth][12]
1. [Git LFS 服务器实现杂谈][13]
1. [Git at scale][14]
1. [GVFS Design History][15]
1. [Make GVFS available for Linux and macOS][16]
1. [jonathantanmy/git][17]


[2]: http://forcemz.net/git/2017/12/06/MassiveRepositoriesAndGit/

[4]: https://en.wikipedia.org/wiki/Distributed_version_control
[5]: https://github.com/Microsoft/GVFS/blob/master/Protocol.md
[6]: http://www.jianshu.com/p/5a74c5194fa6
[7]: https://libgit2.github.com/libgit2/#HEAD/type/git_packbuilder
[8]: https://libgit2.github.com/libgit2/#HEAD/group/packbuilder/git_packbuilder_write
[9]: https://libgit2.github.com/libgit2/#HEAD/group/packbuilder/git_packbuilder_foreach
[10]: https://gcc.gnu.org/ml/gcc/2015-08/msg00140.html
[11]: http://llvm.org/docs/Proposals/GitHubMove.html
[12]: https://github.com/fstudio/clangbuilder/blob/63f45b5b99d6b2f8473356dfbe3454238f6dee2e/bin/LLVMRemoteFetch.ps1#L33
[13]: http://forcemz.net/git/2017/04/16/Moses/
[14]: https://www.visualstudio.com/zh-hans/learn/git-at-scale/
[15]: https://www.visualstudio.com/zh-hans/learn/gvfs-design-history/
[16]: https://public-inbox.org/git/20170915134343.3814dc38@twelve2.svl.corp.google.com/T/#u
[17]: https://github.com/jonathantanmy/git/tree/partialclone2
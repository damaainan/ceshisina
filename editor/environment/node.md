## [windows 上优雅的安装 node 和 npm](http://imweb.io/topic/57289aa78a0819f17b7d9d5e)

> 本文作者：IMWeb 江源  原文出处：[IMWeb社区][0] **未经同意，禁止转载**

我一直觉得我掌握的这份优雅是被许多人所知道了，直到我发现小伙伴们都下载 `.msi` 来装 node ，我心中的优雅感终于压制不住。

## .msi 安装

windows 的一键安装包，应该是最简单的 node 安装方式，但存在几个缺陷。

* 比如不能安装多个 node 版本，现在 node 的版本就像火箭似的，所以多个 node 版本并行的需求还是很强烈。
* 一键安装对 npm 的理解也存在问题，我们完全不知道安装过程中，和 npm 相关的目录有哪些，以及怎样配置这些目录。

多版本的方式当然可以去找些 nvm-windows 之类的解决方案，所以这里着重讲解和 npm 相关的东西。

## 优雅安装

### 目录

新建一个目录专门了管理 node 和 npm 。 比如在 E 盘下新建一个目录 NODE ，如下：

    E:\NODE\node
    E:\NODE\npm-global
    E:\NODE\npm-cache
    

node 目录用来存放 `node.exe` ，当然可以放多个版本； **`npm-global`** 是 npm i xxxxx -g 的安装目录； **`npm-cache`** 是 npm 的缓存目录，避免相同的包每次都联网下载。

### 下载 .exe 
下载可执行文件(.exe)，放入上述 node 目录，这时候的目录如下：

    E:/NODE/
        node/
            node.exe
            node-v0.12.0.exe
        npm-global/
        npm-cache
    

可以存放多个 node 版本，在命令行中可以如下使用：

    node --version
    node-v0.12.0 --version
    

当然，你现在直接运行上述命令会报错，因为 node 没有配置到环境变量。 **E:\NODE\node 和 E:\NODE\npm-global 都要配置到环境变量**。 怎样配置环境变量就不是本文关注的了。

### npm

目前为止，我们可以在命令行中执行 node 命令了，而且可以多版本共存。接下来我们要让 npm 命令顺心如意。

#### 下载安装 npm 
第一次要手动下载并安装 npm 。 在上述 npm-global 目录下新建 node_modules 目录。 来[这里][1]下载一个最新版的 npm ，将其解压至 node_modules 目录下，并将 npm-x.x.x 重命名为 npm 。 这时整体目录如下：

    E:/NODE/
        node/
        npm-global/
            node_modules/
                npm/
                    bin/
                    xxx
        npm-cache
    

将 bin 目录下的 npm 文件和 `npm.cmd` 文件拷贝至 `npm-global` 目录下，这个时候应该就可以执行 npm --version 命令了。

#### 设置 npm 的相关目录

不急着执行 npm install 命令。 我们建了 `npm-global` 和 `npm-cache` ，是时候把它们利用起来了。

    npm config set prefix "E:\NODE\npm-global" # npm install -g xxx 的包都会装到这个目录
    npm config set cache "E:\NODE\npm-cache" # 缓存都会装到这个目录
    

现在试着安装一个包：

    npm i -g es-checker
    es-checker
    

回顾下，我们现在讲所有 node 相关的东西全集中在 `E:\NODE\` 目录中，并且指定了 npm 的安装目录，npm 对我们不再是黑箱。 优雅安装方式结束。

加  `NODE_PATH`    `E:\NODE\npm-global\node_modules`


[0]: http://imweb.io/topic/57289aa78a0819f17b7d9d5e
[1]: https://github.com/npm/npm/releases
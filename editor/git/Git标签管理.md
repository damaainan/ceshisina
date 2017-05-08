# [Git标签管理][0]

## 前面的话

发布一个版本时，我们通常先在版本库中打一个标签(tag)。这样，就唯一确定了打标签时刻的版本。将来无论什么时候，取某个标签的版本，就是把那个打标签的时刻的历史版本取出来。所以，标签也是版本库的一个快照，实质上它就是指向某个commit的指针。所以，创建和删除标签都是瞬间完成的。简而言之，标签tag就是一个让人容易记住的有意义的名字，它跟某个commit绑在一起。本文将详细介绍Git标签管理

### 创建标签

在Git中打标签非常简单，首先，切换到需要打标签的分支上

![][1]

然后，敲命令git tag <name>就可以打一个新标签

![][2]

可以用命令git tag查看所有标签

![][3]

默认标签是打在最新提交的commit上的。有时候，如果忘了打标签怎么办呢？方法是找到历史提交的commit id，然后打上就可以了

![][4]

比方说要对create b.txt这次提交打标签，它对应的commit id是7ec9296，敲入命令：

![][5]

再用命令git tag查看标签，注意，标签不是按时间顺序列出，而是按字母排序的

![][6]

可以用git show <tagname>查看标签信息

![][7]

### 附注标签

实际上，Git使用的标签有两种类型：轻量级的(lightweight)和含附注的(annotated)。上面介绍的就是轻量级标签，轻量级标签就像是个不会变化的分支，实际上它就是个指向特定提交对象的引用。而含附注标签，实际上是存储在仓库中的一个独立对象，它有自身的校验和信息，包含着标签的名字，电子邮件地址和日期，以及标签说明，标签本身也允许使用GNU Privacy Guard(GPG)来签署或验证。一般我们都建议使用含附注型的标签，以便保留相关信息；当然，如果只是临时性加注标签，或者不需要旁注额外信息，用轻量级标签也没问题

创建一个含附注类型的标签非常简单，用-a(取annotated的首字母，中文意思为注释)指定标签名字即可，而-m选项则指定了对应的标签说明，Git会将此说明一同保存在标签对象中。如果没有给出该选项，Git会启动文本编辑软件供你输入标签说明

    $ git tag -a v1.4 -m 'my version 1.4'

我们可以看到在提交对象信息上面，列出了此标签的提交者和提交时间，以及相应的标签说明

![][8]

### 签署标签

如果有自己的私钥，还可以用GPG来签署标签，只需要把之前的-a改为-s(取signed的首字母，中文意思为有符号的)即可

    $ git tag -s v0.2 -m 'signed version 0.2 released'

签名采用PGP签名，因此，必须首先安装gpg(GnuPG)，如果没有找到gpg，或者没有gpg密钥对，就会报错

![][9]

现在再运行 git show 会看到对应的 GPG 签名也附在其内

    $ git show v0.2
    tag v0.2
    Tagger: Michael Liao <askxuefeng@gmail.com>
    Date:   Mon Aug 26 07:28:33 2013 +0800
    
    signed version 0.2 released
    -----BEGIN PGP SIGNATURE-----
    Version: GnuPG v1.4.12 (Darwin)
    
    iQEcBAABAgAGBQJSGpMhAAoJEPUxHyDAhBpT4QQIAKeHfR3bo...
    -----END PGP SIGNATURE-----
    
    commit fec145accd63cdc9ed95a2f557ea0658a2a6537f
    Author: Michael Liao <askxuefeng@gmail.com>
    Date:   Thu Aug 22 10:37:30 2013 +0800
    
        branch test

用PGP签名的标签是不可伪造的，因为可以验证PGP签名

可以使用git tag -v [tagname] (取verify的首字母，中文意思为核实)的方式验证已经签署的标签。此命令会调用GPG来验证签名，所以你需要有签署者的公钥，存放在keyring中，才能验证

    $ git tag -v v1.4.2.1
    object 883653babd8ee7ea23e6a5c392bb739348b1eb61
    type commit
    tag v1.4.2.1
    tagger Junio C Hamano <junkio@cox.net> 1158138501 -0700
    
    GIT 1.4.2.1
    
    Minor fixes since 1.4.2, including git-mv and git-http with alternates.
    gpg: Signature made Wed Sep 13 02:08:25 2006 PDT using DSA key ID F3119B9A
    gpg: Good signature from "Junio C Hamano <junkio@cox.net>"
    gpg:                 aka "[jpeg image of size 1513]"
    Primary key fingerprint: 3565 2A26 2040 E066 C9A7  4A7D C0C6 D9A4 F311 9B9A

若是没有签署者的公钥，会报告类似下面这样的错误：

    gpg: Signature made Wed Sep 13 02:08:25 2006 PDT using DSA key ID F3119B9A
    gpg: Can't check signature: public key not found
    error: could not verify the tag 'v1.4.2.1'

### 操作标签

如果标签打错了，也可以删除

    $ git tag -d <tagname>

![][10]

因为创建的标签都只存储在本地，不会自动推送到远程。所以，打错的标签可以在本地安全删除。

默认情况下，git push并不会把标签传送到远端服务器上，只有通过显式命令才能推送标签到远端仓库

    $ git push origin <tagname>

![][11]

或者，一次性推送全部尚未推送到远程的本地标签

    $ git push origin --tags

![][12]

如果标签已经推送到远程，要删除远程标签就麻烦一点，先从本地删除

![][13]

然后，从远程删除。删除命令也是push，但是格式如下

![][14]

最后一个问题，如何查看发送到远程的标签呢？

点击Github项目中的release

![][15]

即可看到远程标签的信息

![][16]



[0]: http://www.cnblogs.com/xiaohuochai/p/6722039.html
[1]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417103754227-1448150223.png
[2]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417103837493-2120292646.png
[3]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417103906852-1114302541.png
[4]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417104702462-1548495225.png
[5]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417104923446-1928185496.png
[6]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417104942977-36395894.png
[7]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417105048852-1073809445.png
[8]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417110407462-1922829404.png
[9]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417110947993-1488523684.png
[10]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417140303790-1043256694.png
[11]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417140443274-1034467311.png
[12]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417140551884-1230733855.png
[13]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417140632274-1737934394.png
[14]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417140732493-1497588517.png
[15]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417141350134-162067534.png
[16]: http://images2015.cnblogs.com/blog/740839/201704/740839-20170417141600431-1067336378.png
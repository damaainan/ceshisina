## 让你的git拥有不同身份

来源：[https://segmentfault.com/a/1190000013283182](https://segmentfault.com/a/1190000013283182)

时间 2018-02-13 14:14:48

 
由于你没有进行过特别的设定，所以`git` 不管它是往`github` 上传还是往你公司的服务器上传，都会以一个完全相同的身份上传，这有时候会造成困扰，比如说这样： 
 
![][0]  
 
但其实这是我公司的服务器，我不想让它以`fengerzh` 的身份上传，我想只有在我往`github` 上传的时候才以`fengerzh` 上传，而我往公司服务器上传的时候就以`zhangjing` 的身份上传，那该怎么做呢？ 
 
最直接的方法是在你`git clone` 下来的仓库里，有一个`.git` 文件夹，`.git` 文件夹里有一个`config` 文件，在这个文件里写上 

```sh
[user]
    email = zhangjing@mydomain.com
    name = zhangjing
```
 
就行了。
 
但问题是我有几十个仓库，不能一个一个设吧，而且万一我忘记了怎么办？所以我们需要有一些自动化的小工具来帮助我们完成这件事情。
 
首先，你要先建立这么一个文件夹：

```sh
mkdir -p ~/.git-templates/hooks
```
 
然后你要告诉`git` 这个文件夹就是你的模板文件夹： 

```sh
git config --global init.templatedir ~/.git-templates
```
 
再然后，你在这个文件夹里放上一个钩子文件：

```sh
vi ~/.git-templates/hooks/post-checkout
```
 
这个钩子文件的内容就是下面这样：

```sh
#!/bin/bash

function warn {
  echo -e "\n$1 Email and author not initialized in local config!"
}

email="$(git config --local user.email)"
name="$(git config --local user.name)"

if [[ $1 != "0000000000000000000000000000000000000000" || -n $email || -n $name ]]; then
  exit 0
fi

remote="$([[ $(git remote | wc -l) -eq 1 ]] && git remote || git remote | grep "^origin$")"

if [[ -z $remote ]]; then
  warn "Failed to detect remote."
  exit 0
fi

url="$(git config --local remote.${remote}.url)"

if [[ ! -f ~/.git-clone-init ]]; then
cat << INPUT > ~/.git-clone-init
#!/bin/bash
case "\$url" in
  *@github.com:*    ) email=""; name="";;
  *//github.com/*   ) email=""; name="";;
esac
INPUT
  warn "\nMissing file ~/.git-clone-init. Template created..."
  exit 0
fi
. ~/.git-clone-init

if [[ -z $name || -z $email ]]; then
  warn "Failed to detect identity using ~/.git-clone-init."
  exit 0
fi

git config --local user.email "$email"
git config --local user.name "$name"

echo -e "\nIdentity set to $name <$email>"
```
 
切记，一定要赋予这个文件可执行权限，否则你的钩子工作不起来：

```sh
chmod +x ~/.git-templates/hooks/post-checkout
```
 
接下来，你还要再建立另一个文件：

```sh
vi ~/.git-clone-init
```
 
这个文件的内容是像下面这样：

```sh
case "$url" in
  *@github.com:*  ) email="buzz.zhang@gmail.com";    name="fengerzh";;
  *//github.com/* ) email="buzz.zhang@gmail.com";    name="fengerzh";;
  *@mydomain.com:*    ) email="zhangjing@mydomain.com"; name="zhangjing";;
  *//mydomain.com/*   ) email="zhangjing@mydomain.com"; name="zhangjing";;
esac
```
 
在这里，我们指明了如果仓库来源是`github` 的话我们用哪个用户，如果仓库来源是公司服务器的话又该用哪个用户。 
 
做完了这些事，我们来重新`git clone` 一下我们的仓库看看吧： 

```sh
$ git clone ssh://git@mydomain.com/source/ys.git
Cloning into 'ys'...
remote: Counting objects: 1003, done.
remote: Compressing objects: 100% (591/591), done.
remote: Total 1003 (delta 476), reused 506 (delta 221)
Receiving objects: 100% (1003/1003), 691.97 KiB | 1.71 MiB/s, done.
Resolving deltas: 100% (476/476), done.

Identity set to zhangjing <zhangjing@mydomain.com>
```
 
可以看到，已经设置成功了。再来看一下克隆之后生成的配置文件吧：

```sh
$ cat ys/.git/config
[core]
    repositoryformatversion = 0
    filemode = true
    bare = false
    logallrefupdates = true
    ignorecase = true
    precomposeunicode = true
[remote "origin"]
    url = ssh://git@mydomain.com/source/ys.git
    fetch = +refs/heads/*:refs/remotes/origin/*
[branch "master"]
    remote = origin
    merge = refs/heads/master
[user]
    email = zhangjing@mydomain.com
    name = zhangjing
```
 
在这里我们看到文件末尾自动增加了两行关于身份的配置，有了这两行，我们再也不用担心`push` 的时候弄错身份了。 
 
整个原理其实就是利用了`git` 的三个特性： **`初始模板`**  、 **`钩子函数`**  和 **`本地配置`**  。在初始模板里我们设定好了一个钩子函数，这样只要一执行克隆操作，首先`git` 会把我们的模板文件里的钩子函数复制到本地仓库里，然后开始执行这个钩子函数，最后根据`URL` 地址设置我们的本地配置。 
 
以上这些代码其实并不是我写的，而是来源于一个`github` 项目，感兴趣的同学可以去 [这里][1] 参观学习。 
 


[1]: https://github.com/DrVanScott/git-clone-init
[0]: ../img/BnYJVjI.png 
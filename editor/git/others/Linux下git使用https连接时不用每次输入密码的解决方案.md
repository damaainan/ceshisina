## Linux下git使用https连接时不用每次输入密码的解决方案

来源：[https://pjf.name/blogs/save-git-password-via-https-on-linux.html](https://pjf.name/blogs/save-git-password-via-https-on-linux.html)

时间 2018-06-09 19:11:00


在命令行下我们一般情况下都是习惯使用ssh进行git的操作，但是某些情况只能使用https时只能使用账号密码登录时每次push等需要和git服务器进行交互的时候都提示我们输入账号和密码，经常push和fetch的时候这个操作是相当烦人的，那么如何保存git密码呢？


## 方法一

首先在home目录下创建`.git-credentials`,然后输入：

```
https://{username}:{password}@github.com
```

如果有多个，一行一个，`:wq`保存退出

然后在终端执行命令

```
git config --global credential.helper store
```

如果我们看到`~/.gitconfig`文件下存在下面的内容就代表成功了

```
[credential]
helper = store
```


## 方法二

这个方法需要git版本需要>=1.7.10才行，用`git version`查看版本号看是否支持，不支持又想用，那就自行升级git版本吧

终端下执行

```
git config --global credential.helper cache
```

默认会缓存密码15分钟，如果想改的更长，比如1个小时，那么可以

```
git config --global credential.helper 'cache --timeout=3600'
```

这里的3600指的是秒，其他时间自行更改即可



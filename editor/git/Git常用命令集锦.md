# Git常用命令集锦

 时间 2017-09-07 22:01:00  薄雾's Blog

原文[http://www.bowu8.com/archives/272/][2]


## I. 一. Windows配置SSH keys连接Github

### 1. 安装Git工具

下载地址：https://git-scm.com/downloads

### 2. 配置全局的name和email

这里是的你的github或者bitbucket的name和email

    git config --global user.name "bowu"
    git config --global user.email "bowu@163.com"

### 3. 生成key

    ssh-keygen -t rsa -C "bowu@163.com"
    
    //连续按三次回车，这里设置的密码就为空了，并且创建了key。
    
    Your identification has been saved in /User/Admin/.ssh/id_rsa.
    Your public key has been saved in /User/Admin/.ssh/id_rsa.pub.
    The key fingerprint is: ………………

最后得到了两个文件： `id_rsa` 和 `id_rsa.pub`

### 4. Github添加key

用记事本打开 `id_rsa.pub` ，复制里面的内容添加到你 `github` 或者 `bitbucket ssh` 设置里即可 

![][4]

### 5. 测试是否添加成功

* bitbucket 输入命令：
```
    ssh -T git@bitbucket.org
    //提示：“You can use git or hg to connect to Bitbucket. Shell access is disabled.” 说明添加成功了
```
* github 输入命令：
```
    ssh git@github.com
    //提示：“Hi bowu! You've successfully authenticated, but GitHub does not provide shel l access.”说明添加成功。
```
![][5]

至此，本地连接远程就是基于SSH了

## II. 二. 本地项目推送到远程

### 1. 初始化项目文件夹

    git init //把所在目录变成git可以管理的仓库
    git add . //把目录下全部文件都添加到暂存区
    git commit -m "xxx"  //告诉Git，把文件提交到仓库
    git status //查看是否还有文件未提交

### 2. 修改回退版本

    git diff readme.txt //查看readme.txt文件修改了什么内容
    git log //查看历史记录
    git reset  –hard HEAD^  //把当前的版本回退到上一个版本
    git reset  –hard HEAD^  //把当前的版本回退到上上一个版本

### 3. 推送到远程

    git remote add origin https://github.com/bowu/testgit.git //添加远程源
    git push -u origin master //–u参数，Git不但会把本地的master分支内容推送的远程新的master分支，还会把本地的master分支和远程的master分支关联起来，在以后的推送或者拉取时就可以简化命令。推送成功后，可以立刻在github页面中看到远程库的内容已经和本地一模一样了

### 4.分支操作

    git branch test //创建分支
    git checkout test //切换分支
    git checkout -b test //加上 –b参数表示创建并切换，相当于上面2条命令
    git merge name //合并某分支到当前分支：
    git branch –d name //删除分支

### 5.删除远程分支或文件

    git push origin :test //删除远程test分支
    git rm --cached readme.txt //删除远程文件
    git commit -m "删除readme.txt"
    git push
    git rm -r --cached a/2.txt //删除a目录下的2.txt文件
    git rm -r --cached a //删除a目录
    
    //用-r参数删除目录, git rm --cached a.txt 删除的是本地仓库中的文件，且本地工作区的文件会保留且不再与远程仓库发生跟踪关系，如果本地仓库中的文件也要删除则用git rm a.txt

### 6. 查看远程连接状态

    git remote
    git remote -v //查看详细信息

## III. 三. 资料

* [git删除远程仓库的文件或目录][6]
* [Git 删除远程仓库文件][7]
* [git push 分支 选定文件夹下内容推送][8]
* [git向github推送小白教程][9]
* [Windows 7下Git SSH 创建Key的步骤][10]
* [git 删除本地分支和远程分支、本地代码回滚和远程代码库回滚][11]
* [手把手教你使用Git][12]


[2]: http://www.bowu8.com/archives/272/

[4]: ./img/M3Mfiye.png
[5]: ./img/7ziYjeJ.png
[6]: http://www.cnblogs.com/toward-the-sun/p/6015284.html
[7]: http://blog.csdn.net/xing_sky/article/details/50069617
[8]: https://segmentfault.com/q/1010000003869022
[9]: http://blog.csdn.net/bitboss/article/details/53037540
[10]: http://blog.csdn.net/lsyz0021/article/details/52064829
[11]: http://www.cnblogs.com/hqbhonker/p/5092300.html
[12]: http://blog.jobbole.com/78960/
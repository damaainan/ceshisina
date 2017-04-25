# Git 命令总结


## 基本命令

    # 安装Git
    $ sudo apt install git
    
    # 配置个人信息
    $ git config --global user.name "Your Name"
    $ git config --global user.email "email@example.com"
    
    # 切换目录初始化
    $ git init
    
    # 文件添加到仓库
    $ git add -p <file>
    
    # 把文件提交到仓库
    $ git commit -m "add LICENSE"
    
    # 查看仓库当前状态
    $ git status
    
    # 查看difference
    $ git diff
    
    # 显示从最近到最远的提交日志
    $ git log --pretty=oneline # 格式化输出信息
    
    # 版本退回
    $ git reset --hard HEAD^ # 当前版本HEAD,上一个版本HEAD^,上上个版本HEAD^^
    $ git reset --hard 130f10a # 或HEAD~100
    
    # 查看命令记录
    $ git reflog
    
    # 丢弃工作区的修改，回到最近一次git commit或git add时的状态：
    $ git checkout -- README.md
    
    # 把暂存区的修改撤销掉（unstage）
    $ git reset HEAD READER.md
    
    # 从版本库中删除该文件
    $ git rm README.md
    $ git commit -m "remove READER.md"
    
    # 把误删的文件恢复到最新版本，checkout其实用版本库里的版本替换工作区的版本
    $ git checkout -- README.md
    

## 远程仓库


    $ ssh-keygen -t rsa -C "youremail@example.com"
    # 测试是否成功
    $ ssh -T git@github.com
    
    # 把一个已有的本地仓库与之关联
    $ git remote add origin git@github.com:Windrivder/Windrivder.git
    
    # 把本地库的所有内容推送到远程库上（推送master分支的内容）
    $ git push -u origin master
    
    # 向远程库推送更新
    $ git push origin master
    
    # 从远程库克隆
    $ git clone git@github.com:michaelliao/gitskills.git
    

## 分支管理

    # 创建+切换dev分支
    $ git checkout -b dev
    
    # 相当于
    $ git branch dev # 创建分支
    $ git checkout dev
    
    # 查看当前分支，当前分支前面标有×号
    $ git branch
    
    # 切换回master分支
    $ git checkout master
    
    # 合并指定分支到当前分支
    $ git merge dev
    
    # 删除dev分支
    $ git branch -d dev
    
    # 查看分支合并情况
    $ git log --graph --pretty=oneline --abbrev-commit
    *   59bc1cb conflict fixed
    |\
    | * 75a857c AND simple
    * | 400b400 & simple
    |/
    * fec145a branch test
    
    # 删除feature1分支
    $ git branch -d feature1
    
    # 创建并切换dev分支
    $ git checkout -b dev
    
    # 修改readme.txt文件，并提交一个新的commit
    $ git add readme.txt
    $ git commit -m "add merge"
    
    # 切换回master
    $ git checkout master
    
    # 合并dev分支，请注意--no-ff参数，表示禁用Fast forward
    $ git merge --no-ff -m "merge with no-ff" dev
    
    # 看看分支历史
    $ git log --graph --pretty=oneline --abbrev-commit
    *   7825a50 merge with no-ff
    |\
    | * 6224937 add merge
    |/
    *   59bc1cb conflict fixed
    
    # 如果需要临时修复Bug，可以把当前工作现场“储藏”起来，等Bug修复后恢复现场后继续工作
    $ git stash
    
    # 此时查看工作区是干净
    # 切换到需要修复Bug的分支，创建临时分支来修复
    $ git checkout master
    $ git checkout -b issue-101
    
    # 修复完成后切换到master分支，完成合并，删除临时分支
    $ git checkout master
    $ git merge --no-ff -m "merged bug fix 101" issue-101
    $ git branch -d issue-101
    
    # Bug修复后，切换回dev分支继续干活
    $ git checkout dev
    
    # 查看工作现场列表
    $ git stash list
    
    # 恢复工作现场
    $ git stash pop # 恢复的同时把stash内容也删了
    $ git stash apply # 恢复，不删除stash的内容，使用git stash drop
    
    # 再次查看工作现场列表，干净
    $ git stash list
    
    # 可以多次stash，恢复时指定恢复
    $ git stash apply stash@{0}
    
    # 强行删除一个没有合并过的分支
    $ git branch -D <name>
    
    # 要查看远程库的信息
    $ git remote
    $ git remote -v
    
    # 推送其他分支
    $ git push origin dev
    
    # 从远程库clone，默认情况只能看到master分支，需要在dev分支，必须创建远程origin的dev分支到本地
    $ git checkout -b dev origin/dev
    $ git checkout -b branch-name origin/branch-name
    $ git branch --set-upstream branch-name origin/branch-name # 关联
    
    # 向远程库推送dev有冲突
    $ git pull # 抓取到本地合并解决冲突，再向远程推送
    $ git push origin dev
    

## 标签管理


    # 切换到需要打标签的分支
    $ git branch
    $ git checkout master
    
    # 创建标签
    $ git tag v1.0
    
    # 查看所有标签
    $ git tag
    
    # 给历史提高的commit id打标签
    $ git log --pretty=oneline --abbrev-commit # 查看commit id
    $ git tag v0.9 6224937
    
    # 查看标签信息
    $ git show v0.9
    
    # 创建带有说明的标签
    $ git tag -a v0.1 -m "version 0.1 released" 3628164
    
    # 用PGP签名标签
    $ git tag -s <tagname> -m "blablabla..."
    
    # 推送某个标签到远程
    $ git push origin v1.0
    
    # 一次性推送全部尚未推送到远程的本地标签
    $ git push origin --tags
    
    # 删除远程标签
    $ git tag -d v0.9 # 删除本地
    $ git push origin :refs/tags/v0.9 # 删除远程

## 自定义 Git

    # 显示颜色，会让命令输出看起来更醒目
    $ git config --global color.ui true
    
    # 忽略某些文件时，需要编写.gitignore，然后将.gitignore放到版本库中
    # st就表示status
    $ git config --global alias.st status
    
    # 配置一个unstage别名
    $ git config --global alias.unstage 'reset HEAD'
    $ git unstage test.py # 等价于
    $ git reset HEAD test.py
    
    # 显示最后一次提交信息
    $ git config --global alias.last 'log -1'
    
    # log
    git config --global alias.lg "log --color --graph --pretty=format:'%Cred%h%Creset -%C(yellow)%d%Creset %s %Cgreen(%cr) %C(bold blue)<%an>%Creset' --abbrev-commit"
    # 每个仓库的配置文件放在.git/config
    # 当前用户的配置文件放在用户主目录下的一个隐藏文件.gitconfig中

## 搭建 Git 服务器

1. 安装 git：
```
    $ sudo apt-get install git
```

2. 创建一个 git 用户，用来运行 git 服务：

```
    $ sudo adduser git
```

3. 创建证书登录：收集所有需要登录的用户的公钥，就是他们自己的 `id_rsa.pub` 文件，把所有公钥导入到 `/home/git/.ssh/authorized_keys` 文件里，一行一个

4. 初始化 Git 仓库：
```
    # 选定一个目录作为 Git 仓库，假定是 /srv/sample.git，在 /srv 目录下输入命令

    $ sudo git init --bare sample.git
    # 把 owner 改为 git
    $ sudo chown -R git:git sample.git
```

5. 禁用 shell 登录：
```
    # 编辑/etc/passwd文件
    git:x:1001:1001:,,,:/home/git:/bin/bash # 修改成下面的内容
    git:x:1001:1001:,,,:/home/git:/usr/bin/git-shell
```

6. 克隆远程仓库，在各自的电脑上运行
```
    $ git clone git@server:/srv/sample.git
```


see http://scottchacon.com/2011/08/31/github-flow.html

Git Standard
====


1. ##  拆分变更
> 每次我们修改代码, 可能同时根据无数的需求改了非常多的地方, 
> 我们在提交的时候, 需要尽量按照逻辑拆分出来, 分别commit它们. 
> 这样出来的历史才有足够的可读性. 没有可读性我们还记录它们干什么?

1. ## 分支管理
> 我们需要有至少2个分支: 开发分支和发布分支, 
> 平时在开发分支干活, 需要发布的时候, 提交到发布分支上面去, 并且根据版本加tag. 
> 注意, 提交到发布分支上面的代码是通过流程保证稳定性的.

1. ## 有`错误`，请尝试从头执行，如果还有错误，请立刻停下来
1. ## 禁止使用`Rebase` see [衍合的风险](http://iissnan.com/progit/html/zh/ch3_6.html)
1. ## 禁止使用`push -f` 
1. ## `git pull` 必须加上 `--ff-only` 参数
1. ## `尊重历史`,如果你已经 push 出去了，请千万不要做 `rewrite history` 的动作，会天下大乱。
1. ## Anything in the `master` branch is `deployable`
1. ## 禁止直接提交到 `master` 分支
1. ## `master` 是稳定分支
1. ## `其他分支` 都是不稳定分支
1. ## 一个分支做且只做一件事 (包括但不限于`功能开发` `修复Bug` `整合` `发布` `紧急修复` 等)
1. ## 如果要做多件事，开多个分支
1. ## 尽量`避免`多个人在同一分支上做事
 * 如果避免不了的多个功能，
 * 请谨慎使用`集成分支流程`
 * 只能同时上线，
 * 不能分开上线，
 * 不能合并到其他分支
1. ## 如果做完一件事，分支的生命周期就结束了，要删掉这个分支
 * 主干不死
 * `功能分支`结束于`发布分支`
 * `集成分支`结束于`发布分支`
 * 集成流程的`功能分支`结束于`集成分支`
 * `发布分支`结束于`主干`
 * `紧急修复分支`结束于`主干`
1. ## 分支的生命周期，越短越好，不要长期持有一个分支
 * 大版本`集成分支`生命周期可以适当延长，生命周期应该结束于大版本上线
 * 大版本`功能分支`的生命周期，越短越好，不要长期持有一个分支
 * `发布分支`、`紧急修复分支`的生命周期为上线当天，`推荐`即开即用，用完立刻关闭，否则容易出现`代码回退`
1. ## 分支的`上游`只能是 `master`
 * 大版本的`集成分支`上游只能是`master`
 * 大版本的`功能分支`上游可以是 `master`或`相关的集成分支`，`不能`是其他分支
1. ## 分支的`下游`只能是 `release`
 * 不同大版本的`集成分支`之间，`不允许`耦合
 * 大版本的`集成分支`的下游只能是`release`
 * 大版本的`功能分支`的下游只能是`当前`大版本的`集成分支`，`不能`是其他分支
 * 合并到`集成分支`的`所有`功能分支必须`同时上线`，不能分开上线
1. ## `测试`的版本就是`上线`的版本，`上线`的版本必须经过`测试`
1. ## `集成分支流程`较为复杂，请谨慎使用，并添加充分的单元测试

--- 

### 集成分支特别说明

1. `集成分支`有且仅有`一个负责人`，必须明确；
1. `集成分支`交付QA测试时，QA发现的每一个bug，都要转化为`单元测试`用例；
1. 不同的大版本`集成分支`之间，不能互相耦合，不能相互合并；
1. 合并到`集成分支`的`所有`功能分支必须`同时上线`，不能分开上线；
1. 上线大版本`集成分支`时，请线下沟通，尽量`避免`发布其他分支；
1. 上线大版本`集成分支`时，如果一定要同时上其他功能，请当面沟通说服`唯一负责人`，如 @wengxuejie；

---

流程图
====

![](http://www.blackgate.net/blog/wp-content/uploads/2013/08/git-arrows31.png)
![](http://plog.longwin.com.tw/files/gitcheat-newbie-2010.png)
![](https://hackpad-attachments.s3.amazonaws.com/hackpad.com_CCEc96rfYc6_p.427403_1443300672287_undefined)

开发角色
====

| |系统管理员|配置管理员|发布工程师|整合工程师|模块负责人|开发工程师 |
| --- | --- | --- | --- | --- | --- | --- | --- |
| |(SYSadm)|(SCMadm)|(RELeng)|(INTegrator)|(MODmaster)|(DEV) |
|创建版本库| |✔| | | |  |
|版本库授权| |✔| | | |  |
|版本库改名|✔|?| | | |  |
|删除版本库|✔|?| | | |  |
|创建Tag| | |✔| | |  |
|删除Tag| |✔| | | |  |
|创建一级分支| |✔| | | |  |
|为分支授权| |✔| | | |  |
|向 maint 分支强推| |✔| | | |  |
|向 master 分支强推| |✔| | | |  |
|向 maint 分支写入| | |✔| | |  |
|向 master 分支写入| | | |✔|✔|  |
|创建个人专有分支| |✔|✔|✔|✔|✔ |
|创建个人专有版本库| |✔|✔|✔|✔|✔ |
|为个人专有版本库授权| |✔|✔|✔|✔|✔ |


分支类型
====

* 主要分支
 * `master`: 永远处在 production-ready 状态
* 支持性分支
 * `Feature branches`: 开发新功能都从 master 分支出来，完成后 merge 回 release OR hotfix
 * `Release branches`: 最新的下次发布开发状态，准备要 release 的版本，只修 bugs。从 master分支出来，完成后 merge 回 master
 * `Hotfix branches`: 等不及 release 版本就必须马上修 master  赶上线的情况。会从 master 分支出来，完成后 merge 回 master

新工程师 `on board`
====

* 申请 gitlab 账号
* 申请项目权限
* 更新 `SSH Key`
* 创建本地工作区

```sh
$ git clone git@www.github.com:group/project.git
```

新功能开发 `new feature`
====
* :star:  功能分支 `只能` 从master分支开出 :star: 
* 切换到 `master` 分支

```sh
$ git checkout master
Checking out files: 100% (195/195), done.
Switched to branch 'master'
```

* 拉取最新代码

```sh
$ git pull --ff-only
From gitlab.miyabaobei.com:miya/miya
 * branch            master    -> FETCH_HEAD
Already up-to-date.
```

* 创建分支 `{yourname}_{featurename}` ，遵守命名规范

```sh
$ git checkout -b user1_feature
Switched to a new branch 'user1_feature'
```
日常开发 `daily work`
====

`以下，每天做一次`
* 拉取master分支

```sh
$ git checkout master
Checking out files: 100% (195/195), done.
Switched to branch 'master'

$ git pull --ff-only
From gitlab.miyabaobei.com:miya/miya
 * branch            master    -> FETCH_HEAD
Already up-to-date.
```
* 回到开发分支`featureBranch`

```sh
$ git checkout featureBranch
```
* merge master

```sh
$ git merge master
```
`以上，每天做一次`
* 修改并提交内容
* 推送`featureBranch`到远端

```sh
$ git push origin featureBranch:featureBranch
```
* :star:  此时如果有冲突，执行 `pull` 后，再次 `push`

```sh
$ git pull origin featureBranch
```
 
日常发布小云，也就是用户可以动态更新的 `release`
====
* :star:  release分支`只能`从master分支开出 :star: 
* :email:  发邮件给全体开发者master准备锁定 [Master Locked]
* 切换到master分支

```sh
$ git checkout master
Switched to branch 'master'
```

* 拉取master分支到最新的代码

```sh
$ git pull --ff-only
From gitlab.miyabaobei.com:miya/miya
 * branch            master    -> FETCH_HEAD
Already up-to-date.
```

* 创建分支，命名遵循 `release-YYYYMMDD`

```sh
$ git checkout -b release-20131227 master
Switched to a new branch 'release-20131227'
```

* 推送release分支到远端

```sh
$ git push -u origin release-20131227
Total 0 (delta 0), reused 0 (delta 0)
To git@gitlab.miyabaobei.com:miya/miya.git
 * [new branch]      release-20131227 -> release-20131227
Branch release-20131227 set up to track remote branch release-20131227 from origin.
```
* 发布者在QQ群里通知大家，分支名，准备合并啦
* 开发者创建 `Merge Request`
* 请求其他工程师review，没问题以后，通过 `Merge Request` 将代码合并到 release 分支上
* 发布后端程序到 alpha 环境上
* 通过Jenkins发布小云到 alpha 环境上
* 通知`QA` 进行 `Integration Test`
* 发布到线上，确保功能完整行，并观察数据
* 通知`QA` 进行 `BVT`
* QA sign off :+1: 
* 成功发布
* 此处如果有bug，直接修改提交到 `release-YYYYMMDD`，再次发布，通知`QA` 进行测试
* 将 `release-YYYYMMDD` merge 回 `master`
* :email:  发邮件给全体开发者master已经解锁 [Master Unlocked]

定期发布需要用户强制更新的ipa，也就是大的 `release`
====
* :star: release分支 `只能` 从master分支开出 :star: 
* :email: 发邮件给全体开发者master准备锁定 [Master Locked]
* 拉取master分支
* 创建分支，命名遵循 `release-1.2.0`，
* Merge feture to `release-1.2.0`
* 推送到远端
* send `release-1.2.0` to other devs for merging
* 其他工程师创建 `Merge Request` 等待其他人进行 `code review`
* 脚本发布
* 通知`QA` 进行 `SANITY TEST`
* 发布到线上，确保功能完整行，并观察数据
* 查看并`确认` http://tool.miyabaobei.com/env.php 版本及发布分支
* 通知`QA` 进行 `BVT`
* QA sign off :+1: 
* 成功发布
* 将 `release-1.2.0` merge 回 `master`
* :email: 发邮件给全体开发者master已经解锁 [Master Unlocked]

紧急修复 `hotfix`
====
* :star: hotfix分支 `只能` 从master分支开出 :star: 
* :email: 发邮件给全体开发者master准备锁定 [Master Locked]
* 拉取master分支
* 创建 `hotfix-YYYYMMDD`
* 修复bug
* 脚本发布
* 通知`QA` 进行 `SANITY TEST`
* 发布到线上，确保功能完整行，并观察数据
* 查看并`确认` http://tool.miyabaobei.com/env.php 版本及发布分支
* 通知`QA` 进行 `BVT`
* QA sign off :+1: 
* 成功发布
* 将 `hotfix-YYYYMMDD` merge 回 `master`
* :email: 发邮件给全体开发者master已经解锁 [Master Unlocked]
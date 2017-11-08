**前提** `保证当前工作区是清洁的`

CodeReview 原则
=====

> 单元测试只是开始，不是结束

### 1. 如果有单元测试，保证修改有`单元测试`覆盖才能上线
### 2. 没有单元测试，`不能`上线
### 3. 所有上线代码`必须经过`CodeReview
### 4. 未进行CodeReview的任何代码均`不能`上线
### 5. 所有CodeReview必须由`其他人`进行
### 6. 所有CodeReview必须`不能`由自己进行

上线联系方式 :loudspeaker: 
====

项目 | 上线群 | 群号码
---- | ---- |---- 
UMS | MIA.UMS上线发布 | 130057506
WMS | MIA.WMS 上线发布 | 387150746
OMS | MIA.OMS 上线发布 | 498430879

流程执行说明
====

* 即日起至 `2017-01-01`:
 * 每周`二`、`四`为日常发布日期，其余均为紧急修复；
 * 每个日常发布日期，最多创建`2`个发布分支；
 * 每个日常发布分支，都需要执行一次`完整`上线流程；
 * 如在上线过程中需要紧急修复，请按照以下步骤执行：
 * 按照约定窗口跟随日常上线 `或` 独立执行一次全新紧急修复流程;
 * 上线邮件申请模板 请查阅 [120-220 上线申请模板][1]
* `2017-01-01` 起:
 * 每个日常发布日期，最多创建`1`个发布分支；
 * 其余按照以上约定进行;

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

* 创建分支，命名遵循 `release-YYYYMMDD`，发布当天`必须`完成发布流程；

> 第n次上线，起名 `release-YYYYMMDD-n`

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
* :loudspeaker: QQ群 发布者通知大家，分支名，准备合并啦

> 今天的发布分支已经创建 release-20131227，

> 请`开发完`的功能且`需要上线的`抓紧时间merge request，

> 没开发完的，请继续开发

* 开发者`必须`执行一下 `./sync test MyTest`， 确保新加单元测试成功
* 开发者`必须`执行一下 `./sync`， 确保全部单元测试成功
* 开发者创建 `Merge Request`
* 请求其他工程师review，没问题以后，通过 `Merge Request` 将代码合并到 release 分支上
 * 如果有冲突，请执行 :one: - :three: - :two:  手动合并  [发布/集成分支创建后，如何合并功能分支到集成分支，QA测试，加入发布队列](How-To-Merge-Code-For-QA-Testing)
* :loudspeaker: QQ群 所有等待上线内容，准备完毕以后，在通知所有人`分支名`和`版本号`准备发布
* `分支名`和`版本号` 将用于部署系统的审核

> release-20131227 将在5分钟后上线，`停止合并`，版本是 f0e1149201aeec

* 通过[Jenkins](http://dev232.jenkins.miyabaobei.com/) 发布后端程序到 alpha 环境上
* 通过[Jenkins](http://dev232.jenkins.miyabaobei.com/) 发布小云到 alpha 环境上
* 通知`QA` 进行 `Integration Test`
* `Integration Test` 过程中，QA发现的Bug，功能开发者`必须`转化为`单元测试用例`

:pill:  以下`每件事`都要做 :star: 

> 思考一下为什么? :secret:

 * **前提** `保证当前工作区是清洁的`

```
 $ git checkout release-20131227
 $ git fetch origin -p
 $ git pull --ff-only
```

 * 此时需要和主干同步，参考文档[和主干同步](how-to-merge-master-to-feature-branch) `思考一下为什么?` :secret:
 * `必须`执行一下 `./sync`， 确保单元测试成功
 * 如果单元测试出错，请`发布工程师`仅执行后续一步操作（备选方案两种）：
 * 方案一，`发布工程师`通知单元测试作者，进行修复，保证测试通过，继续`本文档`流程
 * 方案二，确认单元测试无法在`半小时内`修复，`发布工程师`进行如下操作：
  * 通知`系统管理员`锁定当前发布分支；
  * 从头重新走发布流程，分支名称 `n+1`；
  * 通知相关`开发工程师`，`正常分支`重新合并到新发布分支；
  * 通知相关`开发工程师`，`单元测试失败分支`，继续开发，修复后方能继续发布流程；
 * 如果单元测试出错，请`发布工程师`停止`本文档`后续操作

:pill:  部署代码到线上，目前有两种方案，可以根据项目情况选择 :star: 
> ~ ~方案一为旧方案，适用于从SVN迁移到GIT的项目；~ ~方案二为新方案 :star: 

* `↓↓↓↓↓↓↓↓ 方案二开始  ↓↓↓↓↓↓↓↓`
 * `上线工程师`发送 :email: 申请上线；
 * `上线邮件申请模板` 请查阅 [120-220 上线申请模板][1]
 * `上线工程师`登陆http://walle64.miyabaobei.com ，并提交上线申请单
 * 查看`提交上线单`，并在`线上环境`中选择要上线的项目
 * `上线标题`填写`分支名称`，只接受 `release-XXXXXXXX` 或 `hotfix-XXXXXXXX`
 * 选择需要上线的分支
 * 核对最新的`版本号`
 * 提交`上线申请单`后，通知并等待`项目审核管理员`的审核
 * 第三方`项目审核管理员`必须审核`分支名`和`版本号`，是否与QQ群公告相符
 * 审核通过后，点击`部署`按钮将代码部署到线上
* `↑↑↑↑↑↑↑↑ 方案二结束 ↑↑↑↑↑↑↑↑`

* 需要再次上线，请重复上一步 :star:  :star:  :star: 


* :loudspeaker: QQ群 `发布工程师` 通知所有研发上线完毕
* 上线的分支 ( [http://dev45.gitlab.miyabaobei.com/wms/wms/network/master](http://dev45.gitlab.miyabaobei.com/wms/wms/network/master) 从此图形获取 )
* 上线的`分支列表`可以用 ./mergeworklog 获取
* 上线的`版本号`，以`线上`产品`页脚`为准，发布分支及上线时间，形如：`WMS v1.0.4 r:7831250 b:release-20131227 t:2015-10-22 13:00:24` 

> release-20131227 已经上线了，请相关人员验证功能

> 版本是 `WMS v1.0.4 r:7831250 b:release-20131227 t:2015-10-22 13:00:24` 

> 分支：

> xxx_xxx

> xxx_xxx

> ...

> 请相关同学关注

* :loudspeaker: QQ群 通知`QA` 进行 `BVT`
* 相关工程师请`验证`自己的功能
*  发布工程师访问 [open falcon][2] ，并`至少观察`30分钟；
* 任何指标`异常`持续5分钟，请`立刻回滚`，并`终止`后续流程；
* QA sign off :+1: 
* 成功发布
* 此处如果有bug，直接修改提交到 `release-YYYYMMDD`，再次发布，通知`QA` 进行测试
* :loudspeaker: QQ群 通知所有开发工程师，代码准备回主干

> 五分钟后 代码 release-20131227 将回主干，

> 今天发布结束，

> 如还有要发布的同学，请下次走发布流程

* 将 `release-YYYYMMDD` Merge Request 回 `master`
* 打标签 `release-YYYYMMDD`
* :loudspeaker: QQ群 OR  :email:  发邮件给全体开发者master已经解锁 [Master Unlocked]
 * 主干最新信息，可以从以下页面获取 http://dev45.gitlab.miyabaobei.com/ums/ums/branches
 * 形如

> 代码已经回主干

>  79ed3de6 – Merge branch 'release-20131227' into 'master


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
*  发布工程师访问 [open falcon][2] ，并`至少观察`30分钟；
* 任何指标`异常`持续5分钟，请`立刻回滚`，并`终止`后续流程；
* 查看并`确认` http://tool.miyabaobei.com/env.php 版本及发布分支
* 通知`QA` 进行 `BVT`
* QA sign off :+1: 
* 成功发布
* 将 `release-1.2.0` merge 回 `master`
* 打标签 `release-1.2.0`
* :email: 发邮件给全体开发者master已经解锁 [Master Unlocked]

紧急修复 `hotfix`
====
* **前提** `保证当前工作区是清洁的`
* :star: hotfix分支 `只能` 从master分支开出 :star: 
* :email: 发邮件给全体开发者master准备锁定 [Master Locked]
* 拉取master分支
* 创建 `hotfix-YYYYMMDD`

 > 第n次 则创建 `hotfix-YYYYMMDD-n`

*  :loudspeaker: QQ群 发布者通知大家，分支名，准备`发布`啦

> hotfix-20160527-1 将在5分钟后上线，版本是  bfd9fe7，`请勿合并`

* 修复bug
* 脚本发布
* 通知`QA` 进行 `SANITY TEST`
* 发布到线上，确保功能完整行，并观察数据
*  发布工程师访问 [open falcon][2] ，并`至少观察`30分钟；
* 任何指标`异常`持续5分钟，请`立刻回滚`，并`终止`后续流程；
* 查看并`确认` http://tool.miyabaobei.com/env.php 版本及发布分支
* 通知`QA` 进行 `BVT`
* QA sign off :+1: 
* 成功发布
* :loudspeaker: QQ群 通知 发布成功

> hotfix-20160527-1 已经上线了，

> 版本是 UMS v1.0.1 r:bfd9fe7 b:hotfix-20160527-1 t:2016-05-27 10:36:33 

* 将 `hotfix-YYYYMMDD` merge 回 `master`
* 打标签 `hotfix-YYYYMMDD`
* :email: 发邮件给全体开发者master已经解锁 [Master Unlocked]

[1]:http://wiki.mia.com/pages/viewpage.action?pageId=1704622
[2]:http://10.1.15.20:8081/screen/1045
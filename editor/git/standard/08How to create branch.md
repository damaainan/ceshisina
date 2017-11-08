**前提** `保证当前工作区是清洁的`
=============


流程总览
=======

 | 从主干master派生 | 从集成分支派生
---- | ---- | ----
普通功能分支 | :heavy_check_mark: | :u7981:
集成分支 | :heavy_check_mark: | :u7981:
发布分支 | :heavy_check_mark: | :u7981:
紧急修复分支 | :heavy_check_mark: | :u7981:
集成分支的功能分支 | :heavy_check_mark: | :heavy_check_mark:

# `一`、从主干master派生分支。适用情形：
* 普通功能分支
* 集成分支
* 发布分支
* 紧急修复分支
* 集成流程中的功能分支

```
$ git checkout master
$ git status
```
#### `必须`是以下提示

> On branch master

> Your branch is up-to-date with 'origin/master'.

#### `禁止`出现以下提示

> Your branch is ahead of 'origin/master' by xxx commits.

#### 此处必须保证本地 `master` 和 `origin/master` 一致

## 切换到master分支 并`快进式`同步到本地工作区
```bash
$ git checkout master
$ git fetch origin -p
$ git pull --ff-only
```

## 如果出现 `no tracking`提示，请按照提示执行下面的命令
```bash
$ git branch --set-upstream-to=origin/master master
```

## 更新master分支到最新的代码 [可选]
```bash
$ git pull origin master --ff-only
```

## 只允许从master开出你的开发分支，

* 非集成分支`命名规则` 
 * {拥有者}_{功能} 
 * `不要包含日期`

```bash
$ git checkout -b devname_featurename [master]
```

* 集成分支`命名规则` 
 * {integration}_{功能} 
 * `不要`包含日期

```bash
$ git checkout -b integration_packagename [master]
```

## [必选]，需要多人协作，请push你的分支到remote
```bash
$ git push origin devname_featurename [-u]
```

## 可选，如果第一次push你的分支到remote，请绑定你的本地分支和remote的同名分支
```bash
$ git branch --set-upstream-to=origin/devname_featurename
```

# `二`、从集成分支派生分支。适用情形：
* 集成流程中的功能分支

```
$ git checkout integration_packagename 
$ git status
```
#### `必须`是以下提示

> On branch integration_packagename 

> Your branch is up-to-date with 'origin/integration_packagename'.

#### `禁止`出现以下提示

> Your branch is ahead of 'origin/integration_packagename' by xxx commits.

#### 此处必须保证本地 `integration_packagename` 和 `origin/integration_packagename` 一致

## 切换到integration_packagename分支 并`快进式`同步到本地工作区
```bash
$ git checkout integration_packagename 
$ git fetch origin -p
$ git pull --ff-only
```

## 如果出现 `no tracking`提示，请按照提示执行下面的命令
```bash
$ git branch --set-upstream-to=origin/integration_packagename integration_packagename
```

## 更新integration_packagename 分支到最新的代码 [可选]
```bash
$ git pull origin integration_packagename --ff-only
```

## 只允许从integration_packagename 开出你的集成流程中的功能分支，

* `命名规则` 
 * {拥有者}_{功能} 
 * `不要包含日期`

```bash
$ git checkout -b devname_featurename [master]
```

## [必选]，需要多人协作，请push你的分支到remote
```bash
$ git push origin devname_featurename [-u]
```

## 可选，如果第一次push你的分支到remote，请绑定你的本地分支和remote的同名分支
```bash
$ git branch --set-upstream-to=origin/devname_featurename
```

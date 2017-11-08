**前提** `保证当前工作区是清洁的`
=============

* 同步流程以主干【master】为主
* `普通功能`分支、`发布`分支、`紧急修复`分支、`集成`分支请使用 主干 【master】 同步
* `集成流程`的功能分支，请使用 相关的 集成分支 【`integration_packagename`】 同步
* `集成流程`的功能分支，`不要`使用 主干【master】同步
* `集成流程`的功能分支，`不要`使用 其他集成分支

流程总览
=======

 | 与主干同步 | 与集成分支同步
---- | ---- | ----
普通功能分支 | :heavy_check_mark: | :u7981:
发布分支 | :heavy_check_mark: | :u7981:
紧急修复分支 | :heavy_check_mark: | :u7981:
集成分支 | :heavy_check_mark: | :u7981:
集成分支的功能分支 | :u7981: | :heavy_check_mark:


## `↓↓↓↓↓↓↓↓ 以下，每天做一次 ↓↓↓↓↓↓↓↓`

# `一`、与主干同步。
* 适用情形：
 * 普通功能分支
 * 集成分支
 * 发布分支
 * 紧急修复分支
* `不适用`情形：
 * 集成流程中的功能分支


## 切换到master分支 并`快进式`同步到本地工作区
```bash
$ git checkout master
$ git fetch origin -p
$ git pull --ff-only
```

## 切换到feature分支
```bash
$ git checkout your_branch
$ git fetch origin -p
$ git pull --ff-only
```
或者
```
$ git checkout your_branch
$ git pull origin your_branch --ff-only
```
:pill:  `此处可能需要手动解决冲突` :star: 
> 思考一下为什么?

## 合并master到feature分支
```bash
$ git merge master --no-ff
```
:pill:  `此处可能需要手动解决冲突` :star: 

# `二`、与集成分支同步。适用情形：
* 集成流程中的功能分支

## 切换到 integration_packagename 分支 并`快进式`同步到本地工作区
```bash
$ git checkout integration_packagename
$ git fetch origin -p
$ git pull --ff-only
```

## 切换到feature分支
```bash
$ git checkout your_branch
$ git fetch origin -p
$ git pull --ff-only
```
或者
```
$ git checkout your_branch
$ git pull origin your_branch --ff-only
```
:pill:  `此处可能需要手动解决冲突` :star: 
> 思考一下为什么?

## 合并 integration_packagename 到feature分支
```bash
$ git merge integration_packagename --no-ff
```
:pill:  `此处可能需要手动解决冲突` :star: 
## `↑↑↑↑↑↑↑↑ 以上，每天做一次 ↑↑↑↑↑↑↑↑`


* 修改并提交内容
* 推送`featureBranch`到远端

## 必选，需要多人协作，请push你的分支到remote
```bash
$ git push origin your_branch
```
**前提** `保证当前工作区是清洁的`
=============


### 拉取远端版本库

```bash
$ git fetch origin -p
```

### 切换到集成分支，

* 集成分支是一列火车，
* 按需不定期开出，
* 所有需要交付QA进行真机测试的代码必须merge到集成分支上，
* 不允许任何直接merge到 master 分支的操作

```bash
$ git checkout integration_YYYYMMDDD
```


### 更新集成分支到最新的代码

```bash
$ git pull origin integration_YYYYMMDDD
```


### 把你的分支merge回integration分支，请一定加上 `--no-ff` 参数
```bash
$ git merge --no-ff your_branch
```
:pill:  `此处可能需要手动解决冲突` :star: 

* 冲突发生时，请直接找到`冲突文件`的最后一个作者，当面沟通`确认`后，才能提交修改

如果出现以下信息：

```
All conflicts fixed but you are still merging.
  (use "git commit" to conclude merge)
```
请按照提示执行，一定`不要`手动输入提交信息:

``` bash

$ git commit

```

* 请保留系统提交日志：
 * 原始分支名
 * 目标分支名
 * 是否发生冲突
 * 冲突范围

### 推送集成分支到远端

```bash
$ git push origin integration_YYYYMMDDD:integration_YYYYMMDDD
```

### 可选，删除你的本地分支

```bash
$ git branch -d your_branch
``` 


### 可选，删除你的远程分支

```bash
$ git push origin :your_branch
```



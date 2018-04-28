## Git Commit Log的小型团队最佳实践

来源：[https://segmentfault.com/a/1190000014431845](https://segmentfault.com/a/1190000014431845)

随着团队的变大，最近在开发过程中，越来越感觉到commit log的重要性。之前的时候，团队内有人写中文log，有人写英文log；有人写的还算清晰，有人一笔更新bug就概括全貌。这些良莠不齐的commit log充斥在我们的项目中，不仅影响了查阅的效果，还会对code review产生负面的影响。因此，本文是意图从commit log的书写规范入手，并提供相应的解决方案。 **`注意`** ：2016年1月6日，阮一峰老师写了一篇《[Commit message 和 Change log 编写指南][2]》，本文主要来源于这篇文章，只是针对我们的团队，进行了一些改造和简化，以及对一些阮老师没有提及的细小之处进行了指出。
## 1. 书写规范

经过一番调研，因为我们是小团队，需要快速迭代，容易上手，所以对阮老师提到的commit log规范进行了 **`简化`** ，具体如下：

``` 
<type>: <subject>
<body>
```
### 1.1 type

提交 commit 的类型，包括以下几种


* **`feat: 新功能`** 
* **`fix: 修复问题`** 
* docs: 修改文档
* style: 修改代码格式，不影响代码逻辑
* refactor: 重构代码，理论上不影响现有功能
* **`perf: 提升性能`** 
* test: 增加修改测试用例
* **`revert: 回退，建议直接使用Github Desktop回退，而不是使用命令`** 


### 1.2 subject

用一句话清楚的描述这次提交做了什么。书写要遵循以下四种规则：

* **`格式尽量使用谓宾，使用谓宾不通顺时，可以使用主谓`** ，例如：

谓宾：修复xxxx
主谓：中间件支持xxxx

* **`除了名称之外，描述尽可能使用中文`** ，方便不同开发者理解
* **`结尾不加句号`** 
* **`描述控制在20个汉字以内`** 


### 1.3 body

对本地提交的详细描述， **`不建议`** 。我们建议多次少量提交，而不是一次巨量的提交，有助于revert和code review，也对灾难存储有容灾。
## 2. 撰写工具

有工具辅助，一定比手写好，这里我们使用Commitizen这个库。
安装命令：

``` 
cd 项目目录
npm install -g commitizen
commitizen init cz-conventional-changelog --save --save-exact
// 项目做些更改之后
git add .
git cz
```

安装完毕之后，使用git cz来代替git commit命令即可，新的commit log提交界面会如下所示：


![][0]

写完了之后的commit log如下图所示：


![][1]
 **`是不是比之前的commit log看起来清晰很多？`** 
 **`注意：`** 
git bash在windows下不能通过箭头符号上下移动选择，这时候我们可以下载[Cmder][3]来作为我们的命令行工具。
## 2. 使用问题
 **`1. commit log 我用20个字描述不清楚怎么办？`** 
我们期望尽可能多次的提交，一个feature提交一次，不要出现积攒多个feature提交情况，既不有利于code review，也不有利于代码revert
 **`2. 为什么不使用强制验证手段来限制commit log的格式？`** 
尽管没有使用自动化验证的手段（阮老师的文章中提到了，可以自行查看），但是 **`如果不符合书写逻辑的话，code reviewer不应该让其merge request到dev分支中`** 。这一块我觉得[天猪说的很有道理][4]，通过人工的手段去实现这种验证，这也是为了大家养成一个良好的代码习惯。
## 参考文档：


* 《[Commit message 和 Change log 编写指南][2]》
* 《[代码贡献规范][6]》


[2]: http://www.ruanyifeng.com/blog/2016/01/commit_message_change_log.html
[3]: http://cmder.net/
[4]: https://www.zhihu.com/question/21209619
[5]: http://www.ruanyifeng.com/blog/2016/01/commit_message_change_log.html
[6]: https://eggjs.org/zh-cn/contributing.html
[0]: https://segmentfault.com/img/bV8IzM
[1]: https://segmentfault.com/img/bV8Izu
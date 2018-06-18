## 【VIM 插件】⚡如何以光速查看一行代码的提交记录

来源：[https://zhuanlan.zhihu.com/p/37599990](https://zhuanlan.zhihu.com/p/37599990)

时间 2018-06-02 19:38:55

 
![][0]
 
:question:这行代码谁写的 ？
 
在多人协作的项目中进行开发，总是会遇到这些问题：
 
- 这行代码谁写的？明显是个坑，上线肯定是个故障。
 
- 这行代码谁写的？相当优雅，得学习下。
 
- 这行代码谁写的？看不懂，得咨询下。
 
- 这行代码谁写的？不是我呀，得查一下。
 
好，问题来了，这行代码谁写的？
 
## 怎么查是谁写的？
 
## :anger:命令行工具 git blame
 
例如，查询 request.js 第 99 行代码是谁提交的，命令为：
 
``` 
$ git blame -L 99,99 request.js

-> dadd208596 lib/core/app/extend/request.js (fengmk2 2016-07-17 12:48:09 +0800 99)    * @member {String} Request#ip
```
 
即使把这个命令设置为快捷方式，一行一行的查询也是非常耗费精力的，那么有没有一眼可以看到的方式呢？那就是直接在 GitHub 上查。
 
## :expressionless:使用 GitHub 查询
 
直接打开 [GitHub 查询 request.js 提交记录][9] ，就可以看到了。
 
 ![][1]
 
然而，代码敲的好好的，能不能不切换窗口呢？那就是使用各大 IDE 的插件实现了。
 
## :fire:使用 VS Code 查询
 
VS Code 在我的电脑上存在有两个原因：
 
- 写 TypeScript
 
- 使用 Git Blame 插件
 
主要说一下 Git Blame 插件，迄今为止用过的最方便的查询代码提交记录的工具，来张截图感受下：
 
 ![][2]
 
光标在哪行，状态栏就显示该行代码的提交者。如果你使用 VS Code 用户，恭喜你，已经完美解决了本篇文章的问题。如果你喜欢轻量级编辑器，喜欢秒开，喜欢像特斯拉 P100D 一样百公里加速 2.5 秒的感觉，那就接着往下看吧。
 
## :star2:使用 Sublime Text 查询
 
好好好，你要的，都给你。
 
安装 Git Blame 插件后，需要在光标所在行启动命令框，输入："Git Blame"，效果如下：
 
 ![][3]
 
重点到了，身为 VIM 用户，我们的插件呢？
 
## :rocket:在 VIM 中使用 git-blame.vim 快速查询
 
我是一只小小鸟，想要飞呀却飞也飞不高
 
我遨游在 GitHub 中，寻找着 VIM 适合的查询插件， [tpope/vim-fugitive][10] 太重，没有个轻量的插件么？ [git-blame.vim][11] 横空出世！先看效果：
 
 ![][4]
 
 ![][5]
 
 ![][6]
 
怎么做到的？只需要同时按`,s`即可。当然也支持自定义快捷键了。
 
对实现感兴趣，请戳 [git-blame.vim 代码仓库][12]
 
## :wrench:安装
 
最直接的方式：
 
``` 
cd ~/.vim/bundle
git clone git@github.com:zivyangll/git-blame.vim.git
```
 
如果你跟你一样用的是 [Vundle][13] ：
 
``` 
Plugin 'zivyangll/git-blame.vim'
```
 
## :clap:使用
 
默认快捷键“逗号+s”，可在 .vimrc 中覆盖：
 
``` 
nnoremap <Leader>s :<C-u>call gitblame#echo()<CR>
```
 
若未设置 Leader，建议设置为逗号：
 
``` 
let mapleader = ","  " map leader键设置
let g:mapleader = ","
```
 
## 如果你是 VIM 新手，一键上手
 
没有用过 VIM 没关系，可以参考我的 vim 配置： [zivim][14] ，一键安装：
 
``` 
$ curl -k https://raw.githubusercontent.com/zivyll/zivim/master/install.sh
```
 
配置文件及其适合新手阅读和学习，截两张图
 
 ![][7]
 
 ![][8]
 
## :end:结束语
 
如果你觉得这个插件对你有帮助，不要吝啬你的 star： [zivyangll/git-blame.vim][15] ，哈哈:smile:，也可以在vim.org 投出你宝贵的一票。
 
使用中有任何问题请提 issue。
 


[9]: https://link.zhihu.com/?target=https%3A//github.com/eggjs/egg/blame/master/app/extend/request.js
[10]: https://link.zhihu.com/?target=https%3A//github.com/tpope/vim-fugitive
[11]: https://link.zhihu.com/?target=https%3A//github.com/zivyangll/git-blame.vim
[12]: https://link.zhihu.com/?target=https%3A//github.com/zivyangll/git-blame.vim
[13]: https://link.zhihu.com/?target=https%3A//github.com/VundleVim/Vundle.vim
[14]: https://link.zhihu.com/?target=https%3A//github.com/zivyangll/zivim
[15]: https://link.zhihu.com/?target=https%3A//github.com/zivyangll/git-blame.vim
[0]: ./img/7V3AVbF.jpg 
[1]: ./img/MnINJjj.jpg 
[2]: ./img/YviUbqi.jpg 
[3]: ./img/faQnErF.jpg 
[4]: ./img/VvqMf2n.jpg 
[5]: ./img/qyQfiia.jpg 
[6]: ./img/BrqAvur.jpg 
[7]: ./img/6fYrmur.jpg 
[8]: ./img/qIBBJzu.jpg 
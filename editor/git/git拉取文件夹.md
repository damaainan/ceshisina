Git1.7.0以后加入了`Sparse Checkout`模式，这使得Check Out指定文件或者文件夹成为可能。  
具体实现如下：  
  
    $mkdir project_folder  
    $cd project_folder  
    $git init  
    $git remote add -f origin <url>  
  
上面的代码会帮助你创建一个空的本地仓库，同时将远程Git Server URL加入到Git Config文件中。   
接下来，我们在Config中允许使用Sparse Checkout模式：  
  
    $git config core.sparsecheckout true  
  
接下来你需要告诉Git哪些文件或者文件夹是你真正想Check Out的，你可以将它们作为一个列表保存在 .git/info/sparse-checkout 文件中。   
例如：  
  
    $echo libs >> .git/info/sparse-checkout    ## 目录名没有引号
    $echo apps/register.go >> .git/info/sparse-checkout  
    $echo resource/css >> .git/info/sparse-checkout  
  
最后，你只要以正常方式从你想要的分支中将你的项目拉下来就可以了：  
  
    $git pull origin master  
  
具体可参考Git的Sparse checkout文档： [http:// schacon.github.io/git/g it-read-tree.html#_sparse_checkout][0]

[0]: https://link.zhihu.com/?target=http%3A//schacon.github.io/git/git-read-tree.html%23_sparse_checkout
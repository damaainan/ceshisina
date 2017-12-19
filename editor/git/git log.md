## 各种 git log 命令

#### 获取git commit中完整的message  
commit message通常有几行组成，第一行称为subject，其余的称为body。在git log或者git show中，可以分别用pretty format %s和%b获取到，也可以用%B同时获取到两者。

    git log --pretty='%s%b%B'
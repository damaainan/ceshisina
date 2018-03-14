## 各种 git log 命令

#### 获取git commit中完整的message  
commit message通常有几行组成，第一行称为subject，其余的称为body。在git log或者git show中，可以分别用pretty format %s和%b获取到，也可以用%B同时获取到两者。

    git log --pretty='%s%b%B'


 git log 一个常用的选项是 `--pretty`。这个选项可以指定使用不同于默认格式的方式展示提交历史。这个选项有一些内建的子选项供你使用。比如用 `oneline` 将每个提交放在一行显示，查看的提交数很大时非常有用。另外还有 `short`，`full` 和 `fuller` 可以用

    git log --pretty=format:"%h - %an, %ar : %s"

`git log --pretty=format`常用的选项

| 选项 | 说明 |
|-|-|
| `%H` | 提交对象（commit）的完整哈希字串 |
| `%h` | 提交对象的简短哈希字串 |
| `%T` | 树对象（tree）的完整哈希字串 |
| `%t` | 树对象的简短哈希字串 |
| `%P` | 父对象（parent）的完整哈希字串 |
| `%p` | 父对象的简短哈希字串 |
| `%an` | 作者（author）的名字 |
| `%ae` | 作者的电子邮件地址 |
| `%ad` | 作者修订日期（可以用 --date= 选项定制格式） |
| `%ar` | 作者修订日期，按多久以前的方式显示 |
| `%cn` | 提交者（committer）的名字 |
| `%ce` | 提交者的电子邮件地址 |
| `%cd` | 提交日期 |
| `%cr` | 提交日期，按多久以前的方式显示 |
| `%s` | 提交说明 |


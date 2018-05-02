## Sublime Text一键去掉所有注释

来源：[https://www.vtrois.com/sublime-text-remove-all-comments.html](https://www.vtrois.com/sublime-text-remove-all-comments.html)

时间 2018-03-18 13:39:03

 
Sublime Text 是一款具有代码高亮、语法提示、自动完成且反应快速的编辑器软件，不仅具有华丽的界面，还支持插件扩展机制，用她来写代码，绝对是一种享受。
 
相对于其他的编辑器我还是独爱 Sublime Text，那么假设有一个需求是需要将整篇代码的注释都去掉，那么该怎么做呢？接下来将给大家介绍一个适用于Sublime Text 2 和 3 的方法。
 
## 战斗准备
 

* Sublime Text 编辑器 
* 一个聪明的大脑 
![][0]

 
 

## 战斗开始
 
1、将下面的 Python 代码保存到`Packages/User`目录下（可以通过点击`Preferences -> Browse Packages`进入`Packages`目录，然后再进入`User`目录），并命名为`remove_comments.py`。
 
```python
import sublime_plugin
 
class RemoveCommentsCommand(sublime_plugin.TextCommand):
 
    def run(self, edit):
        comments = self.view.find_by_selector('comment')
        for region in reversed(comments):
            self.view.erase(edit, region)


```
 
2、现在可以通过控制台运行这个插件，只需输入下面的命令即可
 
```
view.run_command('remove_comments')


```
 
![][1]
 
也可以将插件绑定为快捷键，方便之后的调用，点击`Preferences -> Key Bindings`并写入下面代码，即可使用 <kbd>Ctrl</kbd> + <kbd>Alt</kbd> + <kbd>Shift</kbd> + <kbd>C</kbd> 快捷调用。
 
```
{ "keys": ["ctrl+alt+shift+c"], "command": "remove_comments" }


```
 
## 注意事项
 
1、这个插件是使用 Sublime Text 中的语法高亮规则来删除所有注释，因此这个方法仅适用于 Sublime Text 可以识别到的注释情况。
 
2、Windows 可以使用快捷键 <kbd>Ctrl</kbd> + <kbd>`</kbd> 呼出控制台，OSX 可以使用快捷键 <kbd>Control</kbd> + <kbd>`</kbd> 呼出控制台，也可以点击`View -> Show Console`呼出控制台。
 


[0]: ../img/7ZJfAzf.png 
[1]: ../img/VviAfyn.gif
## 利用 CTags 开发一个 Sublime Text 代码补完插件

来源：[https://linux.cn/article-9460-1.html](https://linux.cn/article-9460-1.html)

时间 2018-03-18 21:36:00


喜欢使用 Sublime Text 的朋友们都知道，Sublime Text 相当于 Linux 上的 Vim，它们都具有很强的可扩展功能，功能多样的同时速度也很快，对于处理小型文件和项目效率特别高，因此如果不是特别复杂的项目，我一般都是用 Sublime Text 编写以及编译的。

然而在用 Sublime Text 开发的过程中，我发现了一个问题：Sublime Text 本身的自动完成功能只搜索当前视图中正在编辑文件的函数，当我想用其他文件中自定义的函数时，是没有自动完成功能的。而当自定义函数过多时，效率会大大降低，于是我开始寻找具有相关功能的插件。

一开始我用了非常热门的 “ **`SublimeCodeIntel`** ” 插件，试了一下的确非常好用，但是可惜的是，这个插件不支持 C/C++，而且占用的空间非常大，追求简洁轻便的我不得不另辟蹊径。后来又找到一款 “ **`All AutoComplete`** ” 插件，这款插件扩展了 Sublime Text 默认的自动完成功能，可以在当前视图打开的所有文件里面寻找定义的函数和变量，尽管用起来效果不错，但是它的问题也很明显，必须要同时打开多个文件才行，非常不方便，于是我又放弃了。

在 Package Control 上找了许久，也没能找到我想要的插件，于是我开始考虑不如自己写一个这样的插件，刚好借此机会入门 Python。这时我刚好想到能不能利用 CTags，它能把当前项目中的所有自定义函数提取出来，生成 .tags 文件，并提供符号跳转功能，只要提取 .tags 文件里面的信息，用正则匹配，然后添加到 Sublime Text 的自动完成函数中不就行了。

为了完成这个插件，我在网上搜索相关信息，找到相关素材并重新构思了一下，同时参考了 All Complete 插件的源码。

需要提一下，在 Sublime Text 下安装 CTags 的方法这里不会提到，因此麻烦各位自行查询。


### 插件构思



* 读取设置，设置中添加的语言禁用插件功能

    
* 检测 .tag 文件是否存在，不存在则直接`return`
* 读取当前文件夹中的 .tag 文件

    
* 正则匹配函数名

    
* 正则匹配函数体

    
* 添加到自动完成的接口上

    
  

### 开始编写


#### 新建插件

刚开始接触 Sublime Text 插件的编写，当然需要先了解 Sublime Text 提供的各种接口，为此，我去 Sublime Text 的官网找到了相关文档：    [How to Create a Sublime Text Plugin][0]
，以及    [Sublime Text Unofficial Documentation][1]
。

首先，在 Sublime Text  中选择 “Tools -> Developer -> New Plugin” 新建一个最基本的插件文档：

```python
import sublime
import sublime_plugin

class ExampleCommand(sublime_plugin.TextCommand):
    def run(self, edit):
        self.view.insert(edit, 0, "Hello, World!")
```

这里的`sublime`和`sublime_plugin`是 Sublime 必需的模块，其中具体的类和方法可以参考官方的    [API Reference][2]
。

接着，把这个文件保存到`Package`文件夹（默认的保存位置`User`文件夹的上一层）的`CTagsAutoComplete`文件夹（新建）下，并命名为`CTagsAutoComplete.py`。尽管命名并没有什么限制，但最好还是以插件的名称来统一命名。

然后回到 Sublime Text 中，通过快捷键`Ctrl+``进入 Sublime Text 的 Command Console，然后输入`view.run_command('example')`，如果下方显示 “Hello World”，说明插件已经正常加载。

这里之所以直接用`'example'`，是因为 Command 命令的名称是根据大写字符进行拆分的，例子中的`ExampleCommand`在 Command 中 为`'example_command'`，直接输入`'example'`也可以访问。


#### 文中的术语



* `Window`：Sublime Text 的当前窗口对象

    
* `View`：Sublime Text 当前窗口中打开的视图对象

    
* `Command Palette`：Sublime Text 中通过快捷键`Ctrl+Shift+P`打开的交互式列表

    
  

#### 确定插件接口类型

Sublime Text 下的插件命令有 3 种命令类型（都来自于`sublime_plugin`模块）：



* [TextCommand Class][3]
：通过`View`对象提供对选定文件/缓冲区的内容的访问。

    
* [WindowCommand Class][4]
：通过`Window`对象提供当前窗口的引用

    
* [ApplicationCommand Class][5]
：这个类没有引用任何特定窗口或文件/缓冲区，因此很少使用

    
  

2 种事件监听类型：



* [EventListener Class][6]
：监听 Sublime Text 中各种事件并执行一次命令

    
* [ViewEventListener Class][7]
：为`EventListener`提供类似事件处理的类，但绑定到特定的 view。

    
  

2 种输入处理程序：



* [TextInputHandler Class][8]
：可用于接受 Command Palette 中的文本输入。

    
* [ListInputHandler Class][9]
：可用于接受来自 Command Palette 中列表项的选择输入。

    
  

因为我要实现的功能比较简单，只需要监听输入事件并触发自动完成功能，因此需要用到`EventListener Class`。在该类下面找到了`on_query_completions`方法用来处理触发自动完成时执行的命令。接着修改一下刚才的代码：

```python
import sublime
import sublime_plugin

class CTagsAutoComplete(sublime_plugin.EventListener):
    def on_query_completions(self, view, prefix, locations):
```



* `view`：当前视图

    
* `prefix`：触发自动完成时输入的文字

    
* `locations`: 触发自动完成时输入在缓存区中的位置，可以通过这个参数判断语言来执行不同命令

    
* 返回类型：

    

* `return None`
* `return [["trigger \t hint", "contents"]...]`，其中`\t hint`为可选内容，给自动完成的函数名称添加一个提示        
* `return (results, flag)`，其中`results`是包含自动完成语句的 list，如上；`flag`是一个额外参数，可用来控制是否显示 Sublime Text 自带的自动完成功能        
      


  

#### 读取 CTags 文件

为了读取 .tag 文件，首先得判断当前项目是否打开，同时 .tag 文件是否存在，然后读取 .tag 文件中的所有内容：

```python
import sublime
import sublime_plugin
import os
import re

class CTagsAutoComplete(sublime_plugin.EventListener):
    def on_query_completions(self, view, prefix, locations):
        results = []

        ctags_paths = [folder + '\.tags' for folder in view.window().folders()]
        ctags_rows  = []

        for ctags_path in ctags_paths:
            if not is_file_exist(view, ctags_path):
                return []
            ctags_path = str(ctags_path)
            ctags_file = open(ctags_path, encoding = 'utf-8')
            ctags_rows += ctags_file.readlines()
            ctags_file.close()

def is_file_exist(view, file):
    if (not view.window().folders() or not os.path.exists(file)):
        return False
    return True
```

通过上述操作，即可读取当前项目下所有的 .tag 文件中的内容。


#### 分析 CTags 文件

首先是获取 .tags 文件中，包含`prefix`的行：

```python
for rows in ctags_rows:
    target = re.findall('^' + prefix + '.*', rows)
```

一旦找到，就通过正则表达式对该行数据进行处理：

```python
if target:
    matched = re.split('\t', str(target[0]))
    trigger = matched[0] # 返回的第一个参数，函数名称
    trigger += '\t(%s)' % 'CTags' # 给函数名称后加上标识 'CTags'
    contents = re.findall(prefix + '[0-9a-zA-Z_]*\(.*\)', str(matched[2])) # 返回的第二个参数，函数的具体定义
    if (len(matched) > 1 and contents):
        results.append((trigger, contents[0]))
        results = list(set(results)) # 去除重复的函数
        results.sort() # 排序
```

处理完成之后就可以返回了，考虑到最好只显示 .tags 中的函数，我不需要显示 Sublime Text 自带的自动完成功能（提取当前页面中的变量和函数），因此我的返回结果如下：

```python
return (results, sublime.INHIBIT_WORD_COMPLETIONS | sublime.INHIBIT_EXPLICIT_COMPLETIONS)
```


#### 添加配置文件

考虑到能够关闭插件的功能，因此需要添加一个配置文件，用来指定不开启插件功能的语言，这里我参考了 “All AutoComplete” 的代码：

```python
def plugin_loaded():
    global settings
    settings = sublime.load_settings('CTagsAutoComplete.sublime-settings')

def is_disabled_in(scope):
    excluded_scopes = settings.get("exclude_from_completion", [])
    for excluded_scope in excluded_scopes:
        if scope.find(excluded_scope) != -1:
            return True
    return False

if is_disabled_in(view.scope_name(locations[0])):
    return []
```

这里用到的配置文件需要添加到插件所在的文件夹中，名称为`CTagsAutoComplete.sublime-settings`，其内容为：

``` 
{
    // An array of syntax names to exclude from being autocompleted.
    "exclude_from_completion": [
        "css",
        "html"
    ]
}
```


#### 添加设置文件

有了配置文件，还需要在 Sublime Text 的 “Preferences -> Package settings” 下添加相应的设置，同样也是放在插件所在文件夹中，名称为`Main.sublime-menu`：

``` 
[
    {
        "caption": "Preferences",
        "mnemonic": "n",
        "id": "preferences",
        "children": [
            {
                "caption": "Package Settings",
                "mnemonic": "P",
                "id": "package-settings",
                "children": [
                    {
                        "caption": "CTagsAutoComplete",
                        "children": [
                            {
                                "command": "open_file",
                                "args": {
                                    "file": "${packages}/CTagsAutoComplete/CTagsAutoComplete.sublime-settings"
                                },
                                "caption": "Settings"
                            }
                        ]
                    }
                ]
            }
        ]
    }
]
```


### 总结

首先给出插件的完整源码：

```python
import sublime
import sublime_plugin
import os
import re

def plugin_loaded():
    global settings
    settings = sublime.load_settings('CTagsAutoComplete.sublime-settings')

class CTagsAutoComplete(sublime_plugin.EventListener):
    def on_query_completions(self, view, prefix, locations):
        if is_disabled_in(view.scope_name(locations[0])):
            return []

        results = []

        ctags_paths = [folder + '\.tags' for folder in view.window().folders()]
        ctags_rows  = []

        for ctags_path in ctags_paths:
            if not is_file_exist(view, ctags_path):
                return []
            ctags_path = str(ctags_path)
            ctags_file = open(ctags_path, encoding = 'utf-8')
            ctags_rows += ctags_file.readlines()
            ctags_file.close()

        for rows in ctags_rows:
            target = re.findall('^' + prefix + '.*', rows)
            if target:
                matched = re.split('\t', str(target[0]))
                trigger = matched[0]
                trigger += '\t(%s)' % 'CTags'
                contents = re.findall(prefix + '[0-9a-zA-Z_]*\(.*\)', str(matched[2]))
                if (len(matched) > 1 and contents):
                    results.append((trigger, contents[0]))
                    results = list(set(results))
                    results.sort()

        return (results, sublime.INHIBIT_WORD_COMPLETIONS | sublime.INHIBIT_EXPLICIT_COMPLETIONS)

def is_disabled_in(scope):
    excluded_scopes = settings.get("exclude_from_completion", [])
    for excluded_scope in excluded_scopes:
        if scope.find(excluded_scope) != -1:
            return True
    return False

def is_file_exist(view, file):
    if (not view.window().folders() or not os.path.exists(file)):
        return False
    return True

plugin_loaded()
```

之后我会把这个插件整合好后，上传到 Package Control 上，从而方便更多人使用。通过这次入门，我尝到了甜头，未来的开发过程中，可能会出现各种各样独特的需求，如果已有的插件无法提供帮助，那就自己上吧。



[0]: https://code.tutsplus.com/tutorials/how-to-create-a-sublime-text-2-plugin--net-22685
[1]: http://docs.sublimetext.info/en/latest/
[2]: http://www.sublimetext.com/docs/3/api_reference.html
[3]: http://www.sublimetext.com/docs/3/api_reference.html#sublime_plugin.TextCommand
[4]: http://www.sublimetext.com/docs/3/api_reference.html#sublime_plugin.WindowCommand
[5]: http://www.sublimetext.com/docs/3/api_reference.html#sublime_plugin.ApplicationCommand
[6]: http://www.sublimetext.com/docs/3/api_reference.html#sublime_plugin.EventListener
[7]: http://www.sublimetext.com/docs/3/api_reference.html#sublime_plugin.ViewEventListener
[8]: http://www.sublimetext.com/docs/3/api_reference.html#sublime_plugin.TextInputHandler
[9]: http://www.sublimetext.com/docs/3/api_reference.html#sublime_plugin.ListInputHandler
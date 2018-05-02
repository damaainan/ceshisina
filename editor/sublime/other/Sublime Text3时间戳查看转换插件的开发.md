## Sublime Text3时间戳查看转换插件的开发

来源：[http://www.cnblogs.com/kaleovon/p/8963520.html](http://www.cnblogs.com/kaleovon/p/8963520.html)

时间 2018-04-27 17:19:00

 
平常配置表中，经常需要用到时间配置，比如活动开始结束。从可读性上，我们喜欢 **`2017-04-27 17:00:00`**  ，从程序角度，我们喜欢用 **`1493283600`**  。前者是包含时区概念的，而后者时区无关，所以一般推荐直接使用数字时间戳格式来配置。
 
实际配置时，之前一直用MySQL的 **`FROM_UNIXTIME()`**  和 **`UNIX_TIMESTAMP`**  函数，或者使用网页工具进行时间戳查看转换，还是十分繁琐的。突然想到为什么不直接写个插件，在编辑器里查看转换就好了。参考了网络上的一些示例并查阅了Sublime的相关API，过程如下。
 
  
1.依次执行 **`Tools`**  -> **`Developer`**  -> **`New Plugin`**  ，新建一个插件脚本，命名为 **`timestamp.py`** 
 
  
![][0]


 
2.添加脚本代码，具体可以看注释：
 
```python
from datetime import datetime
    import re
    import time
    import sublime
    import sublime_plugin

    def getParseResult(text):
        #patten1 匹配10位整数时间戳（1493283600）
        pattern1 = re.compile('^\d{10}')
        match1 = pattern1.match(text)

        #pattern2 匹配可读时间格式（2017-04-27 17:00:00）
        pattern2 = re.compile('^(\d{4})-(\d{1,2})-(\d{1,2})\s(\d{1,2}):(\d{1,2}):(\d{1,2})')
        match2 = pattern2.match(text)

        if text in ('now'):
            result = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        elif text in ('ts', 'timestamp'):
            result = str(time.time()).split('.')[0]
        elif match1:
            timestamp = int(match1.group(0))
            timeArray = time.localtime(timestamp)
            result = time.strftime('%Y-%m-%d %H:%M:%S', timeArray)
        elif match2:
            timeArray = time.strptime(text, "%Y-%m-%d %H:%M:%S")
            result = str(time.mktime(timeArray)).split('.')[0]
        return result

    class TimestampCommand(sublime_plugin.TextCommand):
        def run(self, edit):
            for s in self.view.sel():
                if s.empty() or s.size() <= 1:
                    break

                # 只处理第一个Region
                text = self.view.substr(s)
                print(text)

                # 得到转换结果
                result = getParseResult(text)

                # 进行文本替换并弹窗显示
                self.view.replace(edit, s, result)
                self.view.show_popup(result, sublime.HIDE_ON_MOUSE_MOVE_AWAY, -1, 600, 600)
                break
```
 
3.进行快捷键绑定，依次执行 **`Project`**  -> **`Key Bindings`**  ，添加代码`{ "keys": ["ctrl+t"], "command": "timestamp"}`

![][1]
 
  
很简单，一个能自动进行时间戳转换的Sublime Text3插件就好了。选中文本，按快捷键 **`CTRL+t`**  ，效果见下图：
 
  
![][2]


 
代码放在了 [Github][3] ，更多欢迎访问个人网站 [Metazion][4]
 


[3]: https://github.com/Metazion/Misc
[4]: http://metazion.com/
[0]: ../img/6Nnmuuq.gif 
[1]: ../img/ZRN3M3r.gif 
[2]: ../img/Vz6Zb2e.gif 
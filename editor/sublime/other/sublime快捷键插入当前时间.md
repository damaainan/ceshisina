## sublime快捷键插入当前时间

来源：[https://www.cnblogs.com/zhendong/p/8440831.html](https://www.cnblogs.com/zhendong/p/8440831.html)

2018-02-11 11:17

1、创建时间插件  Tools -> developer -> New Plugin...

2、插入如下代码，保存在 Packages\User\addCurrentTime.py　　

```python
import sublime
import sublime_plugin
import datetime

class AddCurrentTimeCommand(sublime_plugin.TextCommand):
    def run(self, edit):
        self.view.run_command("insert_snippet",
            {
                "contents": "%s" % datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + ' By JackLiu '
            }
        )
```


3、设置快捷键映射  Preference → Key Bindings - User

```
{ "keys": ["ctrl+z"], "command": "add_current_time"}
```


4、重启sublime3即可


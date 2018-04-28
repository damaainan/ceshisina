
安装 cTags  安装 cTags 插件

插件配置文件  配置程序目录 "command": "D:/tool/ctags58/ctags.exe",

user 目录 新建 `Default (Windows).sublime-mousemap` 文件

button2 代表右键
```
    [
        {
            "button": "button2", 
            "count": 1, 
            "modifiers": ["ctrl"],
            "press_command": "drag_select",
            "command": "goto_definition"
        }
    ]
```
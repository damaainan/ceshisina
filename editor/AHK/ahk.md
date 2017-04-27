##### AutoHotkey是什么？
根据百度定义，如下：
AutoHotkey是一款免费的、Windows平台下开放源代码的热键脚本语言。有了它，您就可以：  
通过发送键盘或鼠标的键击动作命令来实现几乎所有操作的自动化。您可以自己编写宏或者使用宏录制器来生成；  
为键盘，游戏操纵杆和鼠标创建热键。   事实上任何的按键、按钮或组合键都可以被设置为热键；  
当键入您自定义的缩写时可以扩展缩写。例如， 键入“btw”就可以自动扩展为“by the way”；  
创建自定义的数据输入表格、用户界面、菜单等。详情请看 图形界面 部分；  
映射 键盘、游戏操纵杆和鼠标上的按键或按钮；   
运行现有的AutoIt v2脚本 并用 新功能 来增强它们；  
将脚本文件编译成EXE可执行文件，使得程序在没有安装AutoHotkey的机器上得以运行；   
借助相关工具以实现更强大的功能。   

---

按键说明(功能键可以组合，比如^+c就表示Ctrl+Shift+c了)： 

-|-
-|-
^          |         表示Ctrl键
+          |        表示Shift键
!          |        表示Alt键
\#          |        表示Win键
Up         |         表示上箭头键
Down       |           表示下箭头键
Left       |           表示左箭头键
Right      |            表示右箭头键
PgUp       |           表示PageUp键
PgDn       |           表示PageDn键
F1-F12     |     表示功能键
a-z        |          表示a-z键
LButton    |      表示鼠标左键
RButton    |      表示鼠标右键
MButton    |      表示鼠标中键        
WheelUp    |      表示鼠标滑轮向上
WheelDown  | 表示鼠标滑轮向下
Del        |          表示Del删除
Enter      |            表示Enter回车
Tab        |          表示Table制表符
Space      |    表示Space空格

-----

### 按键, 鼠标按钮和操纵杆控制器的列表

鼠标 | 概述
-|- 
LButton | 鼠标左键 
RButton | 鼠标右键 
MButton | 鼠标中键或滚轮 

高级|-
-|-
XButton1 | 鼠标的第四个按钮。一般和 Browser_Back 执行相同功能。 
XButton2 | 鼠标的第五个按钮。一般和 Browser_Forward 执行相同功能。 

Wheel|-
-|-
WheelDown | 向下转动鼠标滚轮（向您的方向）。 
WheelUp | 向上转动鼠标滚轮（远离您的方向）。 
WheelLeft WheelRight | [v1.0.48+]：向左或向右滚动。需要 Windows Vista 或更高版本。这可以用在某些（但并非所有）带第二个滚轮或支持左右滚动的鼠标热键。在某些情况下，必须通过鼠标的自带软件包控制这个功能。不论鼠标如何特殊，Send 和 Click 都能在支持它们的程序里水平滚动。
 

键盘

注意: 字母和数字按键的名称和单个字母或数字相同. 例如: b 表示 "b" 键而 5 表示 "5" 键.


概述|-
-|-
CapsLock | 大小写锁定键 
Space | 空格键 
Tab | Tab 键 
Enter (或 Return) | 回车键 
Escape (或 Esc) | 退出键 
Backspace (或 BS) | 退格键 

光标控制 |
-|-
ScrollLock | 滚动锁定键 
Delete (或 Del) | 删除键 
Insert (或 Ins) | 插入改写切换键 
Home | Home 键 
End | End 键 
PgUp | 向上翻页键 
PgDn | 向下翻页键 
Up | 向上方向键 
Down | 向下方向键 
Left | 向左方向键 
Right | 向右方向键 

Numpad

NumLock 开启 | NumLock 关闭 | -
-|-|-
Numpad0 | NumpadIns | 0 / 插入改写切换键  
Numpad1 | NumpadEnd | 1 / End 键  
Numpad2 | NumpadDown | 2 / 向下方向键  
Numpad3 | NumpadPgDn | 3 / 向下翻页键  
Numpad4 | NumpadLeft | 4 / 向左方向键  
Numpad5 | NumpadClear | 5 / 通常什么都不做  
Numpad6 | NumpadRight | 6 / 向右方向键  
Numpad7 | NumpadHome | 7 / Home 键  
Numpad8 | NumpadUp | 8 / 向上方向键  
Numpad9 | NumpadPgUp | 9 / 向上翻页键  
NumpadDot | NumpadDel | 十进制分隔符 / 删除键  
NumpadDiv | NumpadDiv | 除  
NumpadMult | NumpadMult | 乘  
NumpadAdd | NumpadAdd | 加  
NumpadSub | NumpadSub | 减  
NumpadEnter | NumpadEnter | 回车键 
 

功能

F1 - F24 在大多数键盘顶部的 12 个或更多的功能键。 

按键修饰符 |
-|-
LWin | 左边的 Windows 徽标键。对应的热键前缀为 <#。 
RWin | 右边的 Windows 徽标键。对应的热键前缀为 >#。注意：与 Control/Alt/Shift 不同，没有一般的/中性的“Win”键，因为操作系统不支持。不过含 # 修饰符的热键可以被任何一个 Win 键触发。 
Control (或 Ctrl) | Control 键。单独作为热键（Control::）时它在弹起时触发，不过如果加上颚化符前缀可以改变这种情况。对应的热键前缀为 ^。 
Alt | Alt 键。单独作为热键（Alt::）时它在弹起时触发，不过如果加上颚化符前缀可以改变这种情况。对应的热键前缀为 !。 
Shift | Shift 键。单独作为热键（Shift::）时它在弹起时触发，不过如果加上颚化符前缀可以改变这种情况。对应的热键前缀为 +。 
LControl（或 LCtrl） | 左 Control 键。对应的热键前缀为 <^。 
RControl（或 RCtrl） | 右 Control 键。对应的热键前缀为 >^。 
LShift | 左 Shift 键。对应的热键前缀为 <+。 
RShift | 右 Shift 键。对应的热键前缀为 >+。 
LAlt | 左 Alt 键。对应的热键前缀为 <!>!。注意：如果您的键盘布局存在 AltGr 而不是 RAlt，那么您完全可以根据这里描述的那样通过 <^>! 把它作为热键前缀使用。此外，LControl & RAlt:: 可以把 AltGr 自身设置成热键。 



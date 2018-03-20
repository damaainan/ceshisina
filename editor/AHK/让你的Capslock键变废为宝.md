## 让你的Capslock键变废为宝

来源：[https://segmentfault.com/a/1190000012889336](https://segmentfault.com/a/1190000012889336)


### Capslock 键的劣性

    如果你是 Windows 系统用户，就会发现，很多时候我们是用不到 Capslock 键的。Capslock 键是切换大小写的锁定，而在平常工作中，只要摁住 Shift 键一样可完成该功能。并且 在键盘的布局上来看 ，Capslock 键 的地理位置 非常便捷，这不可是我们想要的。

    下文将告诉你 如何把 作用微小的 Capslock键 变成你的得力助手。
### 修改前奏

本文使用的修改键的脚本语言 是使用的 AutoHotKey ，一门超级强大的脚本语言。



* 点击[AutoHotKey官网下载链接][5]下载AutoHotKey 安装包 并安装。

* 新建文本文件，文件名格式为`文件名.ahk`, 并用编辑器打开，建议 使用下载 AutoHotKey 插件后的 VSCode。

* 新建完成后的 ahk 文件，你可以双击运行，
或者 右键点击文件，然后单击`Compile Script`转换成 可执行文件再执行。


### 修改进行曲

    俗话说：“授人以鱼不如授人以渔”，所以，我先教你三个简单的例子，以便你能快速掌握 所需的 AHK 脚本知识。
#### 模拟发送命令

![][0]
#### 模拟键盘事件

![][1]
#### 模拟光标行为

![][2]
#### 一些 AHK 小常识

其中还有一些必备的小知识


![][3]
### 修改成果

![][4]
#### 源文件

我调试了很长时间的 Capslock 快捷配置，希望能对你有所帮助。

```
;管理员运行
if not A_IsAdmin
{
   Run *RunAs "%A_ScriptFullPath%" 
   ExitApp
}

;无环境变量
#NoEnv

;高进程
Process Priority,,High

;一直关闭 Capslock
SetCapsLockState, AlwaysOff  

; CapsLock -> Esc
CapsLock::
Send {Esc}
return

; CapsLock & alt -> Enter
CapsLock & alt::
Send {Enter}
return

; CapsLock & Space -> Shift
CapsLock & Space::
    Send {Shift Down}
return
CapsLock & Space up::
    Send {Shift up}
return

; 光标移动
CapsLock & j::
MouseMove, -15, 0, 0, R                                               
return  
CapsLock & k::                                                       
MouseMove, 0, 15, 0, R                                                
return                                                               
CapsLock & i::                                                       
MouseMove, 0, -15, 0, R                                                  
return                                                               
CapsLock & l::                                                       
MouseMove, 15, 0, 0, R                                              
return 

; 左键单击 
CapsLock & u::                                                       
SendEvent {Blind}{LButton down}                                      
KeyWait Enter                                                        
SendEvent {Blind}{LButton up}                                                
return 

; 右键单击 
CapsLock & o::                                                       
SendEvent {Blind}{RButton down}                                      
KeyWait Enter                                                        
SendEvent {Blind}{RButton up}                                                
return

; h 向上滚动
CapsLock & h:: 
SendEvent {Blind}{WheelUp}
return  

; 分号 向下滚动
CapsLock & `;::
SendEvent {Blind}{WheelDown}
return 
CapsLock & 3:: 
SendEvent {Blind}{WheelUp}
return        
CapsLock & 4::
SendEvent {Blind}{WheelDown}
return 

CapsLock & F5::
Reload
return

; 指针移动
CapsLock & e::
Send {Up}
return
CapsLock & d::
Send {Down}
return
CapsLock & s::
Send {Left}
return
CapsLock & f::
    Send {right}
return

; 行首行尾
CapsLock & a::
Send {home}
return
CapsLock & g::
Send {end}
return


; 左右删除
CapsLock & w::
Send {BS}
return
CapsLock & r::
Send {Delete}
return

; 撤销重做
CapsLock & t::
Send ^{z}
return
```
### 结束语

  深入Web全栈各项技术，坚持原创，文章更新虽不定，但只为质量而生。

  请关注 我的个人网站：这个坏掉的番茄  [tomotoes.com][6] ，愿陪你一起在全栈的道路上努力前行！

[5]: https://www.autohotkey.com/download/1.1/AutoHotkey_1.1.27.06.zip
[6]: http://tomotoes.com
[0]: ./img/1460000012889341.png
[1]: ./img/1460000012889342.png
[2]: ./img/1460000012889343.png
[3]: ./img/1460000012889344.png
[4]: ./img/1460000012889345.png
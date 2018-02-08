另一个插件 [InsertNums](https://github.com/jbrooksuk/InsertNums) 单纯插入多个连续数字

MultiFill
====================

多处操作的专用插件。


配置填充
--------------------

主要作用就是在选中多出的时候能够自动填充设置好的文字和数字。

当你选中多处之后按下 <code>[ctrl+m]</code> 和 <code>[ctrl+f]</code>，就可以调出 MultiFill 的主界面，你可以通过这个界面选择要填充的类型


公式填充
--------------------

如果你只是想按照某种规律来填充数字的话，推荐你使用 <code>[ctrl+m]</code> 和 <code>[ctrl+i]</code> ，接在出现的输入框中输入的你的公式即可：
<pre>y = 2000 - 10x</pre>
这个条公式就会在你选中的多处中依次填充 2000 1990 1980 ... 等等


多处选择
--------------------

再也不用再小心翼翼的使用 <code>[ctrl+d]</code> 来选中多个了，你可以用 <code>[ctrl+alt+d]</code> 来保存你当前的坐标和选中项位置，然后慢慢悠悠的使用键盘的方向键找到你要继续选中的东西之后再使用 <code>[ctrl+alt+enter]</code> 来恢复你的所有选中。


多屏切换
--------------------

单个窗口组中可以使用 <code>[alt+left]</code> 和 <code>[alt+right]</code> 在多个编辑页中左右切换（于 ctrl+PageUp 等的区别是不会跑到其他窗口组中）

如果你使用 <code>[alt+shitf+2]</code> 之类的多屏操作，你可以使用 <code>[ctrl+alt+left]</code> 和 <code>[ctrl+alt+right]</code> 在多个窗口组之前左右切换。

最后，你可以使用 <code>[ctrl+alt+shift+left]</code> 和 <code>[ctrl+alt+shift+right]</code> 带着你当前的编辑页一起跳到左右的窗口组中。


安装
--------------------
直接通过 package control 搜索 MultiFill 安装，如果没有 package control 请搜索 “sublime 插件” 然后先安装 package control。


配置文件在 【Preferences】->【Package Settings】->【MultiFill】 中，请参照 Setting - Default 来编写字节的 User - Default， 当然也可以直接在 Setting - Default 中修改

<pre>
{
    "custom":
    [
        {
            "name"  : "Names (ordered)", // 插入项名称(MultiFill界面显示)
            "way"   : "ordered",         // 顺序插入
            "values":   // 插入内容
            [
                "Alan","Bob","Cici","David","Elisabeth","Franklin"
            ]
        },
        {
            "name"  : "Gender (random)",
            "way"   : "random",
            "values": 
            [
                "male","female"
            ]
        },
        {
            "name":"V Roman (ordered)",
            "way"   : "ordered",
            "values":
            [
                "I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII"
            ]
        },
        // 新添加一个试试
        {
            "name":"Names chinese (中文名称)",
            "way"   : "ordered",    // 顺序插入
            "values":
            [
                "张三", "李四", "唐儒马"
            ]
        }
    ]
}
</pre>

配置之后，有图有真相：

![image](https://github.com/Lellansin/MultiFill/raw/master/screenshots/add_chinese.png)
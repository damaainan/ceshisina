##### 安装vimium

首先打开chrome浏览器，安装[vimium插件](https://chrome.google.com/webstore/detail/vimium/dbepggeogbaibhgnhhndojpepiihcmeb?hl=zh-CN)

##### 操作命令

废话不多说，直接上快捷键😁

- - -

**导航当页：**

    ?       显示help，查询vimium的所有使用方法
    h       向左滚动
    j       向下滚动
    k       向上滚动
    l       向右滚动
    gg      滚动到顶部
    G       滚动到底部
    d       向下滚动半页
    u       向上滚动半页面
    f       显示链接字母，在当前页面打开
    F       显示链接字母，在新的页面打开
    r       刷新
    gs      显示网页源代码
    i       进入插入模式，所有按键的命令都无效，直至ESC键退出
    yy      将当前的网址复制到剪贴板
    yf      显示链接字母，并将网址拷贝到剪贴板
    gf      cycle forward to the next frame
    gF      focus the main/top frame

- - -

**打开新的页面：**

    o       搜索网址，书签，或历史记录，在当前页面打开
    O       搜索网址，书签，或历史记录，在新的页面打开
    b       搜索书签，在当前页面打开
    B       搜索书签，在新的页面打开

- - -

**查找：**

    /       进入查找模式，输入关键字查找，ESC退出
    n       切换到下一个匹配
    N       切换到上一个匹配

- - -

**前进后退：**

    H       后退
    L       前进

- - -

**切换tab：**

    J, gT   切换到左边tab
    K, gt   切换到右边tab
    g0      切换到第一个tab
    g$      切换到最后一个tab
    ^       切换到刚才的tab
    t       创建一个新的页面
    yt      复制当前页面
    x       关闭当前页面
    X       恢复刚才关闭的页面
    T       在当前所有的tab页面中搜索
    <a-p>   pin/unpin current tab

- - -

**标记：**

    ma      当页标记，只能在当前tab页面跳转，m + 一个小写字母
    mA      全局标记，可以再切换到其他tab的跳转过来，m + 一个大写字母
    `a      跳转到当页标记
    `A      跳转到全局标记
    ``      跳回之前的位置

- - -

**进阶控制命令：**

    ]], [[  Follow the link labeled 'next' or '>' ('previous' or '<')
              - helpful for browsing paginated sites
    <a-f>   open multiple links in a new tab
    gi      focus the first (or n-th) text input box on the page
    gu      跳转到当前网址的上一级网址
    gU      跳转到当前网址的跟网址
    ge      编辑当前的网址，在当前页面打开
    gE      编辑当前网址，在新的页面打开
    zH      滚动到最左边
    zL      滚动到最右边
    v       enter visual mode; use p/P to paste-and-go, use y to yank
    V       enter visual line mode

- - -

**其他：**

    5t      数字num + t，打开num个tab页面
    <Esc>   ESC按钮，可以从任意控制命令中退出，也可以从任意模式中退出（例如插入模式、查找模式）
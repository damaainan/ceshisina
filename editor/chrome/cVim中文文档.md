## cVim中文文档

来源：[http://blog.collin2.xyz/index.php/archives/118/](http://blog.collin2.xyz/index.php/archives/118/)

时间 2018-09-05 09:09:33



## 什么是cVim?

适用于谷歌浏览器的Vim。我讨厌使用鼠标，尤其是在学习了如何使用vim之后。在我的桌面(Linux)上，我有很多快捷键，可以简化操作:我用Alt+w打开Chrome，我用Alt+Shift+d关闭窗口，我用Alt+t打开终端。这对于Chrome来说很难做到，因为它没有定制键盘快捷键的部分，而且仍然需要使用鼠标来做一些事情，比如点击链接。cVim的目标是尽可能地消除这个问题，正如Chrome扩展API所允许的那样。


### 我在哪里可以得到cVim?

有两种方法：


* 在chrom应用市场安装[cVim][0]
    
* 你可以在[这里][1]下载zip包，然后开启chrome开发者模式，然后通过chrome://extensions 来安装cVim    
  


### 和Vimium、ViChrome以及Vrome的不同之处

这些扩展在向谷歌Chrome添加类似vim的按键绑定方面做得很好，但是它们缺少Firefox Addon和Pentadactyl所具有的许多特性。

cVim为chrome添加了哪些功能？


* google / IMDB /维基百科/亚马逊/ Duckduckgo /雅虎/ Bing搜索
* 支持自定义搜索引擎
* 历史和书签搜索、书签文件夹支持
* 插入/视觉模式
* 高效的链接提示（支持自定义映射）
* 支持自定义键盘映射
* 正则表达式页面搜索高亮显示
* 带有制表符自动补全的命令栏
* 平滑滚动
  


### cVim 帮助文档


#### cVimrc


* 使用`set+<SETTING_NAME>`来启用cVimrc设置，使用`set+no<SETTINT_NAME>`来禁用。(例如：set regexp和set noregexp)    
* 可以在命令末尾添加`!`来反转设置    
* 其他设置用=作为分隔符，并以let为前缀(例如，let hintcharacters="abc")

| setting | type | description | default |
| - | - | - | - |
| searchlimit | integer | 设置命令栏中显示的结果数量 | 25 |
| scrollstep | integer | 设置使用向上滚动和向下滚动命令时滚动的像素数量 | 70 |
| timeoutlen | integer | 等待<Leader>映射的时间量（以毫秒为单位） | 1000 |
| fullpagescrollpercent | integer | 使用scrollFullPageUp和scrollFullPageDown命令设置要滚动的页面的百分比 | 0 |
| typelinkhintsdelay | integer | 打开带有typelinkhints和numerichints的链接提示后，在获取输入之前等待的时间（以毫秒为单位） | 300 |
| scrollduration | integer | 平滑滚动的持续时间 | 500 |
| vimport | integer | 设置要与editWithVim插入模式命令一起使用的端口 | 8001 |
| zoomfactor | integer / double | 缩放页面时的步长 | 0.1 |
| scalehints | boolean | 动画链接提示出现时 | false |
| hud | boolean | 展示平视显示器 | true |
| regexp | boolean | 在查找模式下使用regexp | true |
| ignorecase | boolean | 在查找模式中忽略搜索案例 | true |
| linkanimations | boolean | 链接提示打开和关闭时显示淡入淡出效果 | false |
| numerichints | boolean | 使用数字作为链接提示而不是一组字符 | false |
| dimhintcharacters | boolean | 模糊字母在提示字符中匹配，而不是从提示中删除它们 | true |
| defaultnewtabpage | boolean | 使用默认的chrome://newtab页面而不是空白页面 | false |
| cncpcompletion | boolean | 使用<C-n>和<C-p>循环完成结果（要求您在chrome://extensions页面中设置nextCompletionResult键绑定（右下角） | false |
| smartcase | boolean | 不区分大小写的查找模式搜索，除非输入包含大写字母 | true |
| incsearch | boolean | 当输入长度大于两个字符时，开始自动突出显示查找更多匹配项 | true |
| typelinkhints | boolean | （需要numerichints）在链接中键入文本以缩小数字提示 | false |
| autohidecursor | boolean | 滚动时隐藏鼠标光标（对Linux有用，不会自动隐藏keydown上的光标） | false |
| autofocus | boolean | 允许网站在首次加载时自动聚焦输入框 | true |
| insertmappings | boolean | 使用插入映射在文本框中导航光标（请参阅下面的绑定） | true |
| smoothscroll | boolean | 使用平滑滚动 | false |
| autoupdategist | boolean | 如果GitHub Gist用于同步设置，请每小时提取一次更新（Chrome重启时） | false |
| nativelinkorder | boolean | 打开Chrome等新标签，而不是当前打开的标签旁边 | false |
| showtabindices | boolean | 在选项卡标题中显示选项卡索引 | false |
| sortlinkhints | boolean | Sort link hint lettering by the link’s distance from the top-left corner of the page | false |
| localconfig | boolean | 从配置路径读取cVimrc配置（如果设置了此配置，则无法从cVims选项页面保存 | false |
| completeonopen | boolean | 打开命令栏时自动显示命令完成列表 | false |
| configpath | string | 设置configpath时，从此本地文件中读取cVimrc | "" |
| changelog | boolean | 更新cVim时自动打开更改日志 | true |
| completionengines | array of strings | 仅使用指定的搜索引擎 | ["google", "duckduckgo", "wikipedia", "amazon"] |
| blacklists | array of strings | 在匹配其中一个模式的站点上禁用cVim | [] |
| mapleader | string | 默认的<Leader>键 | \ |
| defaultengine | string | 设置默认搜索引擎 | "google" |
| locale | string | 设置正在完成/搜索的站点的区域设置（请参阅下面的示例配置） | "" |
| homedirectory | string | 使用file命令时要替换的目录 | "" |
| qmark <alphanumeric charcter> | string | 添加持久性QuickMark（例如，let qmark a = [“          [http://google.com][2]
”，“          [http://reddit.com][3]
”]） | none |
| previousmatchpattern | string (regexp) | 导航页面后退按钮时查找的模式 | ((?!last)(prev(ious)?\ | newer\ | back\ | «\ | less\ | <\ | ‹\ | )+) |
| nextmatchpattern | string(regexp) | 导航页面的下一个按钮时查找的模式 | ((?!first)(next\ | older\ | more\ | >\ | ›\ | »\ | forward\ | )+) |
| hintcharacters | string (alphanumeric) | 设置链接提示模式下使用的默认字符 | "asdfgqwertzxcvb" |
| barposition | string ["top", "bottom"] | 设置命令栏的默认位置 | "top" |
| langmap | string | 设置要重新映射的字符列表（请参阅vims langmap） | "" |
  


#### 配置示例

```
" Settings
set nohud
set nosmoothscroll
set noautofocus " The opposite of autofocus; this setting stops
                " sites from focusing on an input box when they load
set typelinkhints
let searchlimit = 30
let scrollstep = 70
let barposition = "bottom"
let locale = "uk" " Current choices are 'jp' and 'uk'. This allows cVim to use sites like google.co.uk
                  " or google.co.jp to search rather than google.com. Support is currently limited.
                  " Let me know if you need a different locale for one of the completion/search engines
let hintcharacters = "abc123"
let searchengine dogpile = "http://www.dogpile.com/search/web?q=%s" " If you leave out the '%s' at the end of the URL,
                                                                    " your query will be appended to the link.
                                                                    " Otherwise, your query will replace the '%s'.
" This will do the same thing as above, except typing ':tabnew withbase' into to command bar
" without any search parameters will open 'http://www.dogpile.com'
let searchengine withbase = ["http://www.dogpile.com", "http://www.dogpile.com/search/web?q=%s"]
" alias ':g' to ':tabnew google'
command g tabnew google
let completionengines = ["google", "amazon", "imdb", "dogpile"]
let searchalias g = "google" " Create a shortcut for search engines.
                             " For example, typing ':tabnew g example'
                             " would act the same way as ':tabnew google example'
" Open all of these in a tab with `gnb` or open one of these with <N>goa where <N>
let qmark a = ["http://www.reddit.com", "http://www.google.com", "http://twitter.com"]
let blacklists = ["https://mail.google.com/*", "*://mail.google.com/*", "@https://mail.google.com/mail/*"]
" blacklists prefixed by '@' act as a whitelist
let mapleader = ","
" Mappings
map <Leader>r reloadTabUncached
map <Leader>x :restore<Space>
" This remaps the default 'j' mapping
map j scrollUp
" You can use <Space>, which is interpreted as a
" literal " " character, to enter buffer completion mode
map gb :buffer<Space>
" This unmaps the default 'k' mapping
unmap k
" This unmaps the default 'h', 'j', 'k', and 'l' mappings
unmap h j k l
" This remaps the default 'f' mapping to the current 'F' mapping
map f F
" Toggle the current HUD display value
map <C-h> :set hud!<CR>
" Switch between alphabetical hint characters and numeric hints
map <C-i> :set numerichints!<CR>
map <C-u> rootFrame
map <M-h> previousTab
map <C-d> scrollPageDown
map <C-e> scrollPageUp
iunmap <C-y>
imap <C-m> deleteWord
" Create a variable that can be used/referenced in the command bar
let @@reddit_prog = 'http://www.reddit.com/r/programming'
let @@top_all = 'top?sort=top&t=all'
let @@top_day = 'top?sort=top&t=day'
" TA binding opens 'http://www.reddit.com/r/programming/top?sort=top&t=all' in a new tab
map TA :tabnew @@reddit_prog/@@top_all<CR>
map TD :tabnew @@reddit_prog/@@top_day<CR>
" Use paste buffer in mappings
map T :tabnew wikipedia @"<CR>
" Code blocks (see below for more info)
getIP() -> {{
httpRequest({url: 'http://api.ipify.org/?format=json', json: true},
            function(res) { Status.setMessage('IP: ' + res.ip); });
}}
" Displays your public IP address in the status bar
map ci :call getIP<CR>
" Script hints
echo(link) -> {{
  alert(link.href);
}}
map <C-f> createScriptHint(echo)
let configpath = '/path/to/your/.cvimrc'
set localconfig " Update settings via a local file (and the `:source` command) rather
                " than the default options page in chrome
" As long as localconfig is set in the .cvimrc file. cVim will continue to read
" settings from there
```


### 黑名单

黑名单设置使用Chrome的@match模式指南的自定义实现。 有关语法的说明，请参阅    [https://developer.chrome.com/extensions/match_patterns][4]
。


### 特定于站点的配置

您可以使用黑名单匹配模式为站点启用某些rc设置，如上所述

```
" this will enable the config block below on the domain 'reddit.com'
site '*://*.reddit.com/*' {
      unmap j
      unmap k
      set numerichints
}
```


### 页面加载时运行命令

以与上述特定于站点的配置类似的方式，cVim可以在使用call关键字加载页面时运行命令

```
" In this case, when pages with a file ending in '.js' are loaded,
" cVim will pin the tab and then scroll down
site '*://*/*.js' {
      call :pintab
      call scrollDown
}
```


### Mappings


* 正常映射使用以下结构定义：map <KEY> <MAPPING_NAME>
* 插入映射使用相同的结构，但使用命令“imap”而不是“map”
* Control，meta和alt也可以使用：
  

```
<C-u> " Ctrl + u
<M-u> " Meta + u
<A-u> " Alt + u
```


* 也可以使用`unmap <KEY>`取消映射默认绑定，并使用`iunmap <KEY>`插入绑定
* 要取消映射所有默认键绑定，请使用unmapAll。 要取消映射所有默认插入绑定，请使用iunmapAll
  


### Tabs

打开链接的命令（`：tabnew`和`：open`）有三个不同的属性:


* `!` =>在新标签页中打开
* `$` =>在新窗口中打开
* `|` =>在隐身窗口中打开
* `＆`=>在新标签页中打开（非活动/未关注）
* `*` =>固定标签
      

    
* ? =>将查询视为搜索
* = =>将查询视为URL      

最好用以下示例解释这些属性的使用：    
  

```
:open! google<CR> " This is the same as :tabnew google<CR>
:open google!<CR> " This is another way of writing the above
                  " (these flags can can be added to either
                  " the base command or the end of the final command)
:open& google<CR> " This will open Google in a new inactive tab
:open$ google<CR> " This will open Google in a new window
:open&* google<CR> " The will open Google in a new inactive, pinned tab
:tabnew google&*<CR> " Once again, this will do the same thing as the above command
:open google&*<CR> " Again, same as above
:open google!& " Here, the & flag will cancel out the ! flag,
               " opening Google in a new inactive tab
" More examples
:bookmarks my_bookmark.com& " inactive,new tab
:bookmarks&* my_bookmark.com " inactive,pinned,new tab
:bookmarks! my_bookmark.com " new tab
:bookmarks$ my_bookmark.com " new window
:bookmarks my_bookmark.com " same tab
```


### 代码块


* 代码块允许您通过cVimrc与cVim的内容脚本进行交互。
* 由于代码块使用eval（...），因此只有在知道自己在做什么时才应使用它们。
  

```
" To be used by the code block
set hintset_a
" Create a code block named switchHintCharacters
switchHintCharacters -> {{
  // We are now in JavaScript mode
  // Settings are contained in an object named settings
  settings.hintset_a = !settings.hintset_a;
  if (settings.hintset_a) {
    settings.hintcharacters = 'abc'; // equivalent to "let hintcharacters = 'abc'"
  } else {
    settings.hintcharacters = 'xyz';
  }
  // Propagate the current settings to all tabs for the
  // rest of the session
  PORT('syncSettings', { settings: settings });
  // Display cVim's status bar for 2 seconds.
  Status.setMessage('Hint Set: ' + (true ? 'a' : 'b'), 2);
}}
" Run the JavaScript block
map <Tab> :call switchHintCharacters<CR>
```


### Completion Engines


* 这些是可以在命令栏中使用的完成引擎列表。 可以通过使用completionengines变量将其名称分配给数组来设置它们。


* google，wikipedia，youtube，imdb，amazon，google-maps，wolframalpha，google-image，ebay，webster，wictionary，urbandictionary，duckduckgo，answers，google-trends，google-finance，yahoo，bing，themoviedb
      

    
* 用法示例：
  

```
let completionengines = ['google', 'google-image', 'youtube'] " Show only these engines in the command bar
```


### 快捷键

| Movement |  | Mapping name |
| - | - | - |
| j, s | 向下滚动 | scrollDown |
| k, w | 向上滚动 | scrollUp |
| h | 向左滚动 | scrollLeft |
| l | 向右滚动 | scrollRight |
| d | 向下滚动半页 | scrollPageDown |
| 未映射 | 向下滚动整页 | scrollFullPageDown |
| u, e | 向上滚动半页 | scrollPageUp |
| 未映射 | 向上滚动整页 | scrollFullPageUp |
| gg | 滚动到页面顶部 | scrollToTop |
| G | 滚动到页面底部 | scrollToBottom |
| 0 | 滚动到页面左侧 | scrollToLeft |
| $ | 滚动到页面右侧 | scrollToRight |
| # | 将滚动焦点重置为主页面 | resetScrollFocus |
| gi | 转到第一个输入框 | goToInput |
| gI | 转到gi的最后一个聚焦输入框 | goToLastInput |
| zz | 中心页面到当前搜索匹配（中） | centerMatchH |
| zt | 中心页面到当前搜索匹配（顶部） | centerMatchT |
| zb | 中心页面到当前搜索匹配（下） | centerMatchB |
| **`Link Hints`** |  | |
| f | 在当前选项卡中打开链接 | createHint |
| F | 在新选项卡打开链接 | createTabbedHint |
| 未映射 | 在新选项卡打开链接(活动) | createActiveTabbedHint |
| W | 在新窗口打开链接 | createHintWindow |
| A | 重复上一个提示命令 | openLastHint |
| q | 触发悬停事件（mouseover + mouseenter） | createHoverHint |
| Q | 触发取消悬停事件（mouseout + mouseleave） | createUnhoverHint |
| mf | 打开多个链接 | createMultiHint |
| 未映射 | 用外部编辑器编辑文本 | createEditHint |
| 未映射 | 使用链接作为第一个参数调用代码块 | createScriptHint(<FUNCTION_NAME>) |
| 未映射 | 在新标签页中打开图片 | fullImageHint |
| mr | 反向图像搜索多个链接 | multiReverseImage |
| my | 猛拉多个链接（用P打开链接列表） | multiYankUrl |
| gy | 将URL从链接复制到剪贴板 | yankUrl |
| gr | 反向图像搜索（谷歌图片） | reverseImage |
| ; | 更改链接提示焦点 | |
| **`QuickMarks`** |  | |
| M<*> | 创建quickmark <*> | addQuickMark |
| go<*> | 在当前选项卡中打开quickmark <*> | openQuickMark |
| gn<*> | 在新标签页中打开quickmark <*> | openQuickMarkTabbed |
| gw<*> | 在新窗口中打开quickmark <*> | openQuickMarkWindowed |
| Miscellaneous |  | |
| a | 别名为“：tabnew google” | :tabnew google |
| . | 重复最后一条命令 | repeatCommand |
| : | 打开命令栏 | openCommandBar |
| / | 打开搜索栏 | openSearchBar |
| ? | 打开搜索栏（反向搜索） | openSearchBarReverse |
| 未映射 | 打开链接搜索栏（与按/?相同） | openLinkSearchBar |
| I | 搜索浏览器历史记录 | :history |
| <N>g% | 向下滚动<N>百分比 | percentScroll |
| <N>unmapped | 将<N>键传递到当前页面 | passKeys |
| i | 进入插入模式 | insertMode |
| r | 重新加载当前页面 | reloadTab |
| gR | 重新加载当前选项卡+本地缓存 | reloadTabUncached |
| rootFrame | ;<*> | 创建标记<*> | setMark |
| '' | 转到最后滚动位置 | lastScrollPosition |
| < C-o > | 转到上一个滚动位置 | previousScrollPosition |
| < C-i > | 转到下一个滚动位置 | nextScrollPosition |
| '<*> | 转到标记<*> | goToMark |
| cm | 将选项卡静音/取消静音 | muteTab |
| none | 从新加载所有选项卡 | reloadAllTabs |
| cr | 从新加载除当前选项卡外所有选项卡 | reloadAllButCurrent |
| zi | 放大页面 | zoomPageIn |
| zo | 缩小页面 | zoomPageOut |
| z0 | 将页面缩放到原始大小 | zoomOrig |
| z<Enter> | 切换图像缩放（与单击图像页面上的图像相同） | toggleImageZoom |
| gd | 别名为：chrome：// downloads <CR> | :chrome://downloads<CR> |
| ge | 别名为：chrome：// extensions <CR> | :chrome://extensions<CR> |
| yy | 将当前页面的URL复制到剪贴板 | yankDocumentUrl |
| yY | 将当前帧的URL复制到剪贴板 | yankRootUrl |
| ya | 复制当前窗口中的URL | yankWindowUrls |
| yh | 从查找模式复制当前匹配的文本（如果有） | yankHighlight |
| b | 搜索书签 | :bookmarks |
| p | 打开剪贴板上内容 | openPaste |
| P | 在新选项卡中打开剪贴板选择 | openPasteTab |
| gj | 隐藏下载栏 | hideDownloadsShelf |
| gf | 循环通过iframe | nextFrame |
| gF | 转到根iframe | rootFrame |
| gq | 停止加载当前选项卡 | cancelWebRequest |
| gQ | 停止加载所有标签 | cancelAllWebRequests |
| gu | go up one path in the URL | goUpUrl |
| gU | 转到基本url上 | goToRootUrl |
| gs | 转到当前Url的view-source://页面 | :viewsource! |
| < C-b > | 创建或切换当前URL的书签 | createBookmark |
| 未映射 | 关闭所有浏览器窗口 | quitChrome |
| g- | 递减URL路径中的第一个数字（例如www.example.com/5 => www.example.com/4） | decrementURLPath |
| g+ | 递增URL路径中的第一个数字 | incrementURLPath |
| **`Tab Navigation`** |  | |
| gt, K, R | 导航到下一个选项卡 | nextTab |
| gT, J, E | 导航到上一个选项卡 | previousTab |
| g0, g$ | 转到第一个/最后一个选项卡 | firstTab, lastTab |
| <C-S-h>, gh | 在新选项卡中打开当前选项卡历史记录中的最后一个URL | openLastLinkInTab |
| <C-S-l>, gl | 在新标签页中打开当前标签历史记录中的下一个网址 | openNextLinkInTab |
| x | 关闭当前标签 | closeTab |
| gxT | 关闭当前选项卡左侧的选项卡 | closeTabLeft |
| gxt | 关闭当前选项卡右侧的选项卡 | closeTabRight |
| gx0 | 关闭当前选项卡左侧的所有选项卡 | closeTabsToLeft |
| gx$ | 关闭当前选项卡右侧所有的选项卡 | closeTabsToRight |
| X | 打开最后一个关闭的标签 | lastClosedTab |
| t | :tabnew | :tabnew |
| T | :tabnew <CURRENT URL> | :tabnew @% |
| O | :open <CURRENT URL> | :open @% |
| <N>% | 切换到标签<N> | goToTab |
| H, S | 后退 | goBack |
| L, D | 前进 | goForward |
| B | 搜索另一个活动标签 | :buffer |
| < | 向左移动当前标签 | moveTabLeft |
| > | 向右移动当前标签 | moveTabRight |
| ]] | 点击页面上的“下一个”链接（参见上面的nextmatchpattern） | nextMatchPattern |
| [[ | 点击页面上的“返回”链接（参见上面的previousmatchpattern） | previousMatchPattern |
| gp | 固定/取消固定当前选项卡 | pinTab |
| < C-6 > | 在最后使用的选项卡之间切换焦点 | lastUsedTab |
| **`Find Mode`** |  | |
| n | 下一个搜索结果 | nextSearchResult |
| N | 上一个搜索结果 | previousSearchResult |
| v | 进入视觉/插入符号模式（突出显示当前搜索/选择） | toggleVisualMode |
| V | 从插入符号模式/当前突出显示的搜索进入可视线模式 | toggleVisualLineMode |
| 未映射 | 清除搜索模式突出显示 | clearSearchHighlight |
| **`Visual/Caret Mode`** |  | |
| ESC | 将视觉模式退出到插入模式/退出插入模式到正常模式 | |
| v | 在视觉/插入符号模式之间切换 | |
| h, j, k, l | 移动插入位置/扩展视觉选择 | |
| y | 复制当前选择 | |
| n | 选择下一个搜索结果 | |
| N | 选择上一个搜索结果 | |
| p | 在当前标签中打开突出显示的文 | |
| P | 在新标签页中打开突出显示的文 | |
| **`Text boxes`** |  | |
| < C-i> | 将光标移动到行的开头 | beginningOfLine |
| < C-e> | 将光标移动到行尾 | endOfLine |
| < C-u> | 删除到行的开头 | deleteToBeginning |
| < C-o> | 删除到行尾 | deleteToEnd |
| < C-y> | 删除一个单词 | deleteWord |
| < C-p> | 删除一个单词 | deleteForwardWord |
| 未映射 | 删除一个字符 | deleteChar |
| 未映射 | 删除前进一个字符 | deleteForwardChar |
| < C-h > | 将光标移回一个单词 | backwardWord |
| < C-l > | 向前移动光标一个字 | forwardWord |
| < C-f > | 将光标向前移动一个字母 | forwardChar |
| < C-b > | 将光标移回一个字母 | backwardChar |
| < C-j > | 将光标向前移动一行 | forwardLine |
| < C-k > | 将光标移回一行 | backwardLine |
| unmapped | 选择输入文本（相当于<C-a>） | selectAll |
| unmapped | 在终端中使用Vim编辑（需要运行的cvim_server.py脚本才能工作，并且在该脚本中设置了VIM_COMMAND） | editWithVim |
  


### Command Mode

| Command | Description |
| - | - |
| `:tabnew (autocomplete)` | 使用键入/完成的搜索打开一个新选项卡 |
| `:new (autocomplete)` | 使用键入/完成的搜索打开一个新窗口 |
| `:open (autocomplete)` | 打开已键入/已完成的网址/ Google搜索 |
| `:history (autocomplete)` | 搜索浏览器历史记录 |
| `:bookmarks (autocomplete)` | 搜索书签 |
| `:bookmarks /<folder> (autocomplete)` | 按文件夹浏览书签/打开文件夹中的所有书签 |
| `:set (autocomplete)` | 暂时更改cVim设置 |
| `:chrome:// (autocomplete)` | open a chrome:// URL |
| `:tabhistory (autocomplete)` | 浏览当前选项卡的不同历史状态 |
| `:command <NAME> <ACTION>` | 别名：<NAME> to：<ACTION> |
| `:quit` | 关闭当前标签 |
| `:qall` | 关闭当前窗口 |
| `:restore (autocomplete)` | 恢复以前关闭的标签（仅限较新版本的Chrome） |
| `:tabattach (autocomplete)` | 将当前选项卡移动到另一个打开的窗口 |
| `:tabdetach` | 将当前选项卡移动到新窗口 |
| `:file (autocomplete)` | 打开本地文件 |
| `:source (autocomplete)` | 将cVimrc文件加载到内存中（如果先前已设置了localconfig设置，则会覆盖选项页面中的设置 |
| `:duplicate` | 复制当前选项卡 |
| `:settings` | 打开设置页面 |
| `:nohlsearch` | 清除上次搜索中突出显示的文本 |
| `:execute` | 执行一系列键（用于映射。例如，“map j：execute 2j <CR>”） |
| `:buffer (autocomplete)` | 更改为其他选项卡 |
| `:mksession` | 从活动窗口中的当前选项卡创建新会话 |
| `:delsession (autocomplete)` | 删除已保存的会话 |
| `:session (autocomplete)` | 在新窗口中打开已保存会话中的选项卡 |
| `:script` | 在当前页面上运行JavaScript |
| `:togglepin` | 切换当前选项卡的引脚状态 |
| `:pintab` | 固定当前标签 |
| `:unpintab` | 取消固定当前选项卡 |
  


### Tips


* 您可以在“open”命令中使用@％来指定当前URL。 例如，：open @％实际上会刷新当前页面。
* 在命令前加一个数字，重复该命令N次
* 使用命令/查找模式中的向上/向下箭头浏览先前执行的命令/搜索 - 您还可以使用此箭头搜索以某些字母组合开头的先前执行的命令（例如，在命令栏中输入ta并按 向上箭头将搜索以ta开头的所有匹配项的命令历史记录
  


### Contributing

很高兴你想花一些时间来改进这个扩展。 解决问题总是受到赞赏。 如果您要添加功能，最好提交问题。 您将获得是否可能合并的反馈。


* 在存储库的根文件夹中运行npm install
* 运行make
* 导航到`chrome：//extensions`
* 切换到开发者模式
* 点击“加载未包装的扩展......”
* 选择cVim目录。
  


[0]: https://chrome.google.com/webstore/detail/cvim/ihlenndgcmojhcghmfjfneahoeklbjjh
[1]: https://github.com/1995eaton/chromium-vim/archive/master.zip
[2]: http://google.com
[3]: http://reddit.com
[4]: https://developer.chrome.com/extensions/match_patterns
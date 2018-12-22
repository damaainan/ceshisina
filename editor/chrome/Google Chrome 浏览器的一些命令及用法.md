## 关于Google Chrome 浏览器的一些命令及用法

来源：[https://blog.csdn.net/zyz511919766/article/details/7356306](https://blog.csdn.net/zyz511919766/article/details/7356306)

时间：

**`一些Chrome的地址栏命令（这些命令会不停的变动，所有不一定都是好用的）`** 

在Chrome的浏览器地址栏中输入以下命令，就会返回相应的结果。这些命令包括查看内存状态，浏览器状态，网络状态，DNS服务器状态，插件缓存等等。

about:version         - 显示当前版本 

about:memory       - 显示本机浏览器内存使用状况

about:plugins        - 显示已安装插件 

about:histograms - 显示历史记录 

about:dns               - 显示DNS状态 

about:cache           - 显示缓存页面

about:network       - 网络监控工具 

about:gpu               -是否有硬件加速

about:flags             -开启一些插件 //使用后弹出这么些东西：“请小心，这些实验可能有风险”，不知会不会搞乱俺的配置啊！

about:stats             - 显示状态  //本人在linux-ubuntu下试过，不好用，不知windows环境下情况如何。

about:internets      //本人在linux-ubuntu下试过，不好用，不知windows环境下情况如何

view-cache:Stats   - 缓存状态  //本人在linux-ubuntu下试过，不好用，不知windows环境下情况如何。

chrome-resource: //new-tab - 新标签页 //本人在linux-ubuntu下试过，不好用，不知windows环境下情况如何。

chrome-resource://favicon    //本人在linux-ubuntu下试过，不好用，不知windows环境下情况如何。



chrome://extensions/    - 查看已经安装的扩展 。


 **`Google Chrome浏览器如何设置默认隐身启动（以此例子来说名参数的使用方法，但不限于这么使用，还可以在shell中使用这些参数）`** 


Chrome浏览器具有隐身浏览的模式，在隐身模式窗口中查看的网页不会显示在浏览器历史记录或搜索历史记录中，关闭隐身窗口后也不会在计算机上留下 Cookie 之类的其他痕迹，但会保留所有下载的文件或创建的书签。 Google官方帮助文件说明中隐身模式的原理如下：“在隐身模式下，打开的网页和下载的文件不会记录到您的浏览历史记录以及下载历史记录中。在您关闭已打开的全部隐身窗口后，系统会删除所有新的 Cookie。Chrome 浏览器会保存您在隐身模式下对书签和常规设置所做的更改。”


通过在启动Chrome浏览器的快捷方式的命令行加上参数“–incognito”（注意是双短划线）就可以直接以隐身模式启动Chrome浏览。（右键单击快捷方式图标，选择属性，找到相应的位置添加进相应的参数即可）（只有在加参数的快捷方式上启动chrome，参数才起作用，外部调用chrome参数就不起作用了。这样可以分别建立有不同参数的不同快捷方式。）
 **`其他的一些关于Chrome的实用参数及简要的中文说明（使用方法同上，当然也可以在shell中使用）`** 

–user-data-dir=”[PATH]”              指定用户文件夹User Data路径，可以把书签这样的用户数据保存在系统分区以外的分区。

–disk-cache-dir=”[PATH]“            指定缓存Cache路径

–disk-cache-size=                         指定Cache大小，单位Byte

–first run                                          重置到初始状态，第一次运行

–incognito                                       隐身模式启动

–disable-javascript                        禁用Javascript

--omnibox-popup-count="num"   将地址栏弹出的提示菜单数量改为num个。我都改为15个了。
 --user-agent="xxxxxxxx"                修改HTTP请求头部的Agent字符串，可以通过about:version页面查看修改效果 



--disable-plugins                             禁止加载所有插件，可以增加速度。可以通过about:plugins页面查看效果 

--disable-javascript                        禁用JavaScript， 如果觉得速度慢在加上这个

--disable-java                                  禁用java 

--start-maximized                           启动就最大化

--no-sandbox                                  取消沙盒模式

--single-process                             单进程运行

--process-per-tab                           每个标签使用单独进程
--process-per-site                           每个站点使用单独进程 


--in-process-plugins                      插件不启用单独进程 
--disable-popup-blocking             禁用弹出拦截 
--disable-plugins                            禁用插件 
--disable-images                            禁用图像 
--incognito                                       启动进入隐身模式 
--enable-udd-profiles                    启用账户切换菜单 
--proxy-pac-url                               使用pac代理 [via 1/2] 
--lang=zh-CN                                 设置语言为简体中文 
--disk-cache-dir                             自定义缓存目录 
--disk-cache-size                          自定义缓存最大值（单位byte） 
--media-cache-size                      自定义多媒体缓存最大值（单位byte） 
--bookmark-menu                         在 工具  栏增加一个书签按钮
 --enable-sync                                启用书签同步


 **`在shell中使用chrome命令`** 

以linux的bash shell为例说明 google-chrome这个命令的使用方法

linux中打开chrome浏览器的命令为:"google-chrome"(打开chromium浏览器的命令为:"chromium-browser",chrome浏览器是基于开源的chromium浏览器开发的)

在basn中输入“google-chrome” 执行命令后即可弹出chrome浏览器的窗口,网址为设置的默认的网址

在ban中输入"google-chrome --help"或者"google-chrome -h"即可弹出关于google-chrome这个命令的一些用法信息

在bash中输入"google-chrome  网址"即可打开指定的网址

在bash中输入"google-chrome --app="http://www.baidu.com"" 就可以以应用程序的方式打开网址

其他命令的使用方式同上


 **`下面是几乎全部的chrome命令及英文解释（看命令名称就可以知道其大概意思，然后根据具体的解释作出判断看是否是您要用的参数）`** 



–disable-hang-monitor

Suppresses hang monitor dialogs in renderer processes.

–disable-metrics

Completely disables UMA metrics system.

–disable-metrics-reporting

Disables only the sending of metrics reports. In contrast to kDisableMetrics, this executes all the code that a normal client would use for reporting, except the report is dropped rather than sent to the server. This is useful for finding issues in the metrics
 code during UI and performance tests.

–assert-test

Causes the browser process to throw an assertion on startup.

–renderer-assert-test

Causes the renderer process to throw an assertion on launch.

–crash-test

Performs a crash test when the browser is starte.

–renderer-crash-test

Causes the renderer process to crash on launch.

–renderer-startup-dialog

Use this argument when you want to see the child processes as soon as Chrome start.

–plugin-startup-dialog

Causes the plugin process to display a dialog on launch.

–testshell-startup-dialog

Causes the test shell process to display a dialog on launch.

–plugin-launcher

Specifies a command that should be used to launch the plugin process. Useful

for running the plugin process through purify or quantify. Ex:

–plugin-launcher=”path\to\purify /Run=yes.

–plugin-launche.

–channel

The value of this switch tells the child process which

IPC channel the browser expects to use to communicate with it.

–testing-channel

The value of this switch tells the app to listen for and broadcast

testing-related messages on IPC channel with the given ID.

–homepage

The value of this switch specifies which page will be displayed

in newly-opened tabs. We need this for testing purposes so

that the UI tests don’t depend on what comes up for http://google.com.

–start-renderers-manually

When this switch is present, the browser will throw up a dialog box

asking the user to start a renderer process independently rather

than launching the renderer itself. (This is useful for debugging..

–renderer

Causes the process to run as renderer instead of as browser.

–renderer-path

Path to the executable to run for the renderer subproces.

–plugin

Causes the process to run as plugin hos.

–single-process

Runs the renderer and plugins in the same process as the browse.

–process-per-tab

Runs each set of script-connected tabs (i.e., a BrowsingInstance) in its own

renderer process. We default to using a renderer process for each

site instance (i.e., group of pages from the same registered domain with

script connections to each other).

–process-per-site

Runs a single process for each site (i.e., group of pages from the same

registered domain) the user visits. We default to using a renderer process

for each site instance (i.e., group of pages from the same registered

domain with script connections to each other).

–in-process-plugins

Runs plugins inside the renderer proces.

–no-sandbox

Runs the renderer outside the sandbox.

–safe-plugins

Runs the plugin processes inside the sandbox.

–trusted-plugins

Excludes these plugins from the plugin sandbox.

This is a comma separated list of plugin dlls name and activex clsid.

–test-sandbox

Runs the security test for the sandbox.

–user-data-dir

Specifies the user data directory, which is where the browser will look

for all of its state.

–app

Specifies that the associated value should be launched in “application” mode.

–upload-file

Specifies the file that should be uploaded to the provided application. This

switch is expected to be used with –app option.

–dom-automation

Specifies if the dom_automation_controller_ needs to be bound in the

renderer. This binding happens on per-frame basis and hence can potentially

be a performance bottleneck. One should only enable it when automating

dom based tests.

–plugin-path

Tells the plugin process the path of the plugin to loa.

–js-flags

Specifies the flags passed to JS engin.

–geoid

The GeoID we should use. This is normally obtained from the operating system

during first run and cached in the preferences afterwards. This is a numeric

value; see http://msdn.microsoft.com/en-us/library/ms776390.aspx .

–lang

The language file that we want to try to open. Of the form

language[-country] where language is the 2 letter code from ISO-639.

–debug-children

Will add kDebugOnStart to every child processes. If a value is passed, it

will be used as a filter to determine if the child process should have the

kDebugOnStart flag passed on or not.

–debug-on-start

Causes the process to start the JIT debugger on itself (mainly used by –debug-children.

–wait-for-debugger-children

Will add kWaitForDebugger to every child processes. If a value is passed, it

will be used as a filter to determine if the child process should have the

kWaitForDebugger flag passed on or not.

–wait-for-debugger

Waits for a debugger for 60 second.

–log-filter-prefix

Will filter log messages to show only the messages that are prefixed

with the specified valu.

–enable-logging

Force logging to be enabled. Logging is disabled by default in release

builds.

–dump-histograms-on-exit

Dump any accumualted histograms to the log when browser terminates (requires

logging to be enabled to really do anything). Used by developers and test

scripts.

–disable-logging

Force logging to be disabled. Logging is enabled by default in debug

builds.

–log-level

Sets the minimum log level. Valid values are from 0 to 3:

INFO = 0, WARNING = 1, LOG_ERROR = 2, LOG_FATAL = 3.

–remote-shell-port

Enable remote debug / automation shell on the specified por.

–uninstall

Runs un-installation steps that were done by chrome first-run.

–omnibox-popup-count

Number of entries to show in the omnibox popup.

–uninstallomnibox-popup-count

Removes the previous set suggestion coun.

–automation-channel

The value of this switch tells the app to listen for and broadcast

automation-related messages on IPC channel with the given ID.

–restore-last-session

Indicates the last session should be restored on startup. This overrides

the preferences value and is primarily intended for testing.

–record-mode

–playback-mode

Chrome supports a playback and record mode. Record mode saves *everything*

to the cache. Playback mode reads data exclusively from the cache. This

allows us to record a session into the cache and then replay it at will.

–no-events

Don’t record/playback events when using record & playback.

–hide-icons

–show-icons

Make Windows happy by allowing it to show “Enable access to this program”

checkbox in Add/Remove Programs->Set Program Access and Defaults. This

only shows an error box because the only way to hide Chrome is by

uninstalling it.

–make-default-browser

Make Chrome default browse.

–proxy-server

Use a specified proxy server, overrides system settings. This switch only

affects HTTP and HTTPS requests.

–dns-log-details

–dns-prefetch-disable

Chrome will support prefetching of DNS information. Until this becomes

the default, we’ll provide a command line switch.

–debug-print

Enables support to debug printing subsystem.

–allow-all-activex

Allow initialization of all activex controls. This is only to help website

developers test their controls to see if they are compatible in Chrome.

Note there’s a duplicate value in activex_shared.cc (to avoid

dependency on chrome module). Please change both locations at the same time.

–disable-dev-tools

Browser flag to disable the web inspector for all renderers.

–always-enable-dev-tools

Enable web inspector for all windows, even if they’re part of the browser.

Allows us to use our dev tools to debug browser windows itself.

–memory-model

Configure Chrome’s memory model.

Does chrome really need multiple memory models? No. But we get a lot

of concerns from individuals about how the changes work on *their*

system, and we need to be able to experiment with a few choices.

–tab-count-to-load-on-session-restore

Used to set the value of SessionRestore::num_tabs_to_load_. See session_restore.h for details.

const wchar_t kTabCountToLoadOnSessionRestore[] .

–memory-profile

Enable dynamic loading of the Memory Profiler DLL, which will trace

all memory allocations during the run.

–enable-file-cookies

By default, cookies are not allowed on file://. They are needed in for

testing, for example page cycler and layout tests. See bug 1157243.

–start-maximized

Start the browser maximized, regardless of any previous settings.

TODO(pjohnson): Remove this once bug 1137420 is fixed. We are using this

as a workaround for not being able to use moveTo and resizeTo on a

top-level window.

–enable-watchdog

Spawn threads to watch for excessive delays in specified message loops.

User should set breakpoints on Alarm() to examine problematic thread.

Usage: -enable-watchdog=[ui][io]

Order of the listed sub-arguments does not matter.

–first-run

Display the First Run experience when the browser is started, regardless of

whether or not it’s actually the first run.

–message-loop-strategy

–message-loop-histogrammer

Enable histograming of tasks served by MessageLoop. See about:histograms/Loop

for results, which show frequency of messages on each thread, including APC

count, object signalling count, etc.

–import

Perform importing from another browser. The value associated with this

setting encodes the target browser and what items to import.

–silent-dump-on-dcheck

Change the DCHECKS to dump memory and continue instead of crashing.

This is valid only in Release mode when –enable-dcheck is specified.

–disable-prompt-on-repost

Normally when the user attempts to navigate to a page that was the result of

a post we prompt to make sure they want to. This switch may be used to

disable that check. This switch is used during automated testing.

–disable-popup-blocking

Disable pop-up blocking.

–disable-javascript

Don’t execute JavaScript (browser JS like the new tab page still runs).

–disable-java

Prevent Java from running.

–disable-plugins

Prevent plugins from running.

–disable-images

Prevent images from loading.

–use-lf-heap

Use the low fragmentation heap for the CRT.

–gears-plugin-path

Debug only switch to specify which gears plugin dll to load.

–gears-in-renderer

Switch to load Gears in the renderer process.

–enable-p13n

–javascript-debugger-path

Allow loading of the javascript debugger UI from the filesystem.

–new-http

Enable new HTTP stack.


通过查看作为 Chrome 浏览器基础的开源版 Chromium 的源代码中的[这个][0]文件，可以找到更多的命令行开关参数，不过有些在Chrome的发行版中是没有作用的，详细的可以看这篇：[http://www.thechromesource.com/where-to-find-command-switches-for-chrome/][1]。


[0]: http://src.chromium.org/svn/trunk/src/chrome/common/chrome_switches.cc
[1]: http://www.thechromesource.com/where-to-find-command-switches-for-chrome/
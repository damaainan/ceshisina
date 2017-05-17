# 从小目标开始，编写一个简洁的二维码chrome扩展

 时间 2016-11-25 10:42:39  

_原文_[https://segmentfault.com/a/1190000007594008][1]


### 小需求小目标

开发目的：因为平时在电脑端需要发送些链接啊，或者文字到手机上时每次都要打开qq，特别是有时候电脑断网了，显得特别麻烦，所以开发了此插件。

最终效果图：

![][4]

![][5]

### 下面讲述编码过程

小需求需要个二维码生成插件，这里引用 [qrcode.js][6] 。有了现有的二维码插件就简单了； 

chrome扩展编写并不是很难的事情，一般一个 manifest.json 文件，一个 popup.html 文件就可以搞定，如果需要配置，再增加一个 options.html 文件；当然根据需求还可以增加其他的文件。 

首先创建一个 manifest.json 文件 

编写 manifest.json 文件 

    {   //这里必须是2
        "manifest_version": 2,
        //扩展名称
        "name": "QRcode tool",
        //扩展版本
        "version": "1.0",
        //扩展描述
        "description": "一个简洁的二维码生成工具，简单实用，小巧玲珑。",
        //扩展图标(在扩展中心显示)
        "icons": {
            "16": "images/icon16.png",
            "48": "images/icon128.png",
            "128": "images/icon128.png"
        },
        //配置页面
        "options_page": "options.html",//配置项页面
        //扩展图标配置
        "browser_action": {
            "default_popup": "popup.html",
            "default_titlec": "QRcode tool",
            "default_icon": {
                "16": "images/icon16.png"
            }
        },
        //权限声明
        "permissions":["tabs"]
    }

这文件配置需要注意的问题：

* 严格的json格式，key/val均需要采用双引号，最后的项后面不能有逗号 , 。
* 扩展的一些功能需要声明权限 permissions 。

编写 popup.html 文件 

    
    <link rel="stylesheet" href="css/qrcode.css">
    <div class="box">
        <h2 class="title">扫码浏览本站</h2>
        <div class="qrcode-bg" id="qrcode-bg">
        </div>
        
    </div>
    <script src="js/qrcode.js" type="text/javascript"></script>
    <script src="js/popup.js" type="text/javascript"></script>
        
        

只粘贴主要代码。

这里需要注意：chrome扩展里的html页面好像是不可以写内联代码的，所以需要另外写个js文件，具体什么原因，请专业人士解答下。

#### popup.js

    onload=function(){
        chrome.tabs.getSelected(function(tab){
            var qrcode = new QRCode('qrcode-bg', {
                  text: tab.url,
                  width: 128,
                  height: 128,
                  /*colorDark : '#000000',
                  colorLight : '#ffffff',*/
                  correctLevel : QRCode.CorrectLevel.H
                });
            console.log(qrcode);
        });
    }

chrome.tabs.getSelected(callback(tab)) 这个函数时chrome扩展里可以使用的，用来获取当前标签页对象 

回调参数 tab 是一个标签页对象，标签页对象内容如下： 

    {
        id: 标签id,
        index: 标签在窗口中的位置，以0开始,
        windowId: 标签所在窗口的id,
        openerTabId: 打开此标签的标签id,
        highlighted: 是否被高亮显示,
        active: 是否是活动的,
        pinned: 是否被固定,
        url: 标签的URL,
        title: 标签的标题,
        favIconUrl: 标签favicon的URL,
        status :标签状态，loading或complete,
        incognito: 是否在隐身窗口中,
        width: 宽度,
        height: 高度,
        sessionId: 用于sessions API的唯一id
    }

到这里已经实现功能了。

调试chrome扩展直接拖拽项目文件夹到chrome的扩展程序页面（ chrome://extensions/ ）松开即可安装。 

效果

![][7]

似乎还少点什么，如何生成自定义文本二维码呢？

好吧，在增加个配置页面，在配置页面实现就可以了。

在 manifest.json 文件增加项 

    //配置页面
    "options_page": "options.html",//配置项页面

options.html 文件内容： 

    
        <div class="mlay">
        <div class="mlay-body">
            <div class="box">
                <div class="qrcode-bg" id="qrcode-bg"></div>
                <textarea placeholder="在这输入文本..." class="text control" id="text"></textarea>
                <button class="btn info" id="exe"><img src="images/icon16.png" class="icon">生成二维码</button>
            </div>
            <div class="footer">
                <p class="copyright">
                    <a href="https://github.com/mengdu" target="_blank">@蓝月萧枫</a> 版权所有
                    <a href="https://github.com/mengdu/QRcode-tool" target="_blank">浏览源码</a>
                </p>
            </div>
        </div>
    </div>
    <script src="js/qrcode.js" type="text/javascript"></script>
    <script type="text/javascript" src="js/options.js"></script>
        

编写 options.js 文件： 

    //options.js
    onload=function(){
        var qrcode = new QRCode('qrcode-bg', {
            text: "欢迎使用QRcode tool !",
            width: 330,
            height: 330,
            //colorDark : '#000000',
            //colorLight : '#F1F1F1',
            correctLevel : QRCode.CorrectLevel.H
        });
        var text = document.getElementById('text');
        document.getElementById('exe').onclick = function(){
            if(text.value.length <= 0){
                alert("请填写内容");
                return false;
            }
    
            qrcode.makeCode(text.value);
        };
    }

调试

![][8]

ok，完美。

打包程序：点击 打包扩展程序 点击 打包扩展程序 即可完成。 

小目标小需求实现。

## 遇到问题

1. 不支持长文本生成二维码，像百度搜索链接生成的二维码特别细，手机扫描解析不了的情况
1. 有时候长文本 qrcode.js 会出现 Uncaught Error: code length overflow. 错误，暂时不知道什么原因
1. 打包扩展安装后会显示 并非来自 Chrome 网上应用店 的字样，没钱放chrome商店，没办法。

## 下载

喜欢的star下吧，谢谢！

[源码地址][9]

[扩展下载地址][10]

[1]: https://segmentfault.com/a/1190000007594008?utm_source=tuicool&utm_medium=referral
[4]: http://img0.tuicool.com/ZZVJj2v.png!web
[5]: http://img2.tuicool.com/aE3Mbq3.png!web
[6]: http://code.ciaoca.com/javascript/qrcode/
[7]: http://img0.tuicool.com/uUNvyuq.png!web
[8]: http://img0.tuicool.com/MZfaQzI.png!web
[9]: https://github.com/mengdu/QRcode-tool
[10]: https://github.com/mengdu/QRcode-tool/releases
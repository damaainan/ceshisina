[https://github.com/wanthering/laipi](https://github.com/wanthering/laipi)

人，活着就是为了赖皮。

作为一个合格的开发人员，把30%的时间用来赖皮（上班偷懒）是值得推荐的。

因为，如果你工作时间无法赖皮，并不能说明你工作认真，只能说明你的工作自动化程度不够。

赖皮狗，一般会在上班时间浏览：SGamer论坛、虎扑论坛、斗鱼、BiliBili这一类的网站。

但在浏览过程中会遇到以下痛点：

- 老板查岗，贴子或直播间打开太多，不能及时关闭全部的赖皮站点。
- 老板走了重新赖皮，不记得之前打开的贴子或直播间在哪里。
- 每次在浏览器里输入赖皮网址，打字真的很麻烦！
- 工作时打开了太多标签页，休息时很难找到想要的赖皮页面。

所以，我们需要：
### 简单的一键赖皮插件功能：
1. 打开浏览器后，一个快捷键，立即打开赖皮页面，喜滋滋开始赖皮的一天。
2. 老板/leader查岗时，一个快捷键，立即关闭所有赖皮页面。
3. 老板走后，或工作一段时间后，一个快捷键，立即打开原来的赖皮贴子和直播间。

### 简单的一键赖皮插件功能：
1. 包含简单的一键赖皮站点功能
2. 能自定义配置赖皮网站。
3. 上传Google，发布插件。

# 从零开始，开发简单的一键赖皮插件

90%的上班族都在使用Chrome浏览器赖皮，所以我们选择采用Chrome插件来实现功能。

Chrome插件没什么大不了的，依然还是采用HTML\CSS\JS的组合。

在这里，我将手把手带你从零开始制作插件。


### mainfest.json

就像node.js的package.json一样，每一个插件必须有一个manifest.json，作为最初配置文件。

我们创建一个新的项目，在根目录下创建manifest.json，填入以下代码

*==mainfest.json==*
```json
 {
  "name": "上班一键赖皮工具",
  "version": "0.1",
  "description": "windows:按Alt+S开启、关闭赖皮网站\nmac:按Control+S开启、关闭赖皮网站",
  "manifest_version": 2
 }
```
解释一下：
- name: 插件的名字
- version: 插件的版本
- description: 插件简介栏
- manifest_version: 这个是写死的，每个文件必须有


接下来请右键保存虎扑logo到根目录，名字还是如`apple-touch-icon.png`就行吧。

![image](https://b1.hoopchina.com.cn/common/apple-touch-icon.png)

也可以点击
https://b1.hoopchina.com.cn/common/apple-touch-icon.png 保存图片

修改mainfest.json，设置四个尺寸的icon都变成apple-touch-icon.png,以及插件栏也显示apple-touch-icon.png。

*==mainfest.json==*
```json
{
  "name": "上班一键赖皮工具",
  "version": "0.1",
  "description": "windows:按Alt+S开启、关闭赖皮网站  \nmac:按Control+S开启、关闭赖皮网站",
  "icons": {
    "16": "apple-touch-icon.png",
    "32": "apple-touch-icon.png",
    "48": "apple-touch-icon.png",
    "128": "apple-touch-icon.png"
  },
  "browser_action": {
    "default_icon": "apple-touch-icon.png",
    "default_popup": "popup.html"
  },
  "commands": {
    "toggle-tags": {
      "suggested_key": {
        "default": "Alt+S",
        "mac": "MacCtrl+S"
      },
      "description": "Toggle Tags"
    }
  },
  "manifest_version": 2
}
```
解释一下：
- `icons`: 配置了显示在不同地方的图标
- `browser_action`: 即右上角插件,`browser_action > default_icon`即右上角插件图标
- `commands`:一般用于快捷键命令。 `commands > toggle-tags > suggested_key`之下，设置了快捷键，只要按下快捷键，即会向Chrome就会向后台发布一个`command`，值为`toggle-tags`。
> 在windows环境下，我们将快捷键设置成`Alt+S`，在mac环境下，我们将快捷键设置成`Control+S`，配置文件中写作`MacCtrl+S`


现在我们有了命令，就需要后台脚本接收，在mainfest.json中添加后台脚本：
*==mainfest.json==*
```json
...
  "background": {
    "scripts": [
      "background.js"
    ]
  }
...
```
并在根目录下创建background.js.
*==background.js==*
```js
chrome.commands.onCommand.addListener(function(command) {
    alert(command)
    console.log(command)
})
```

现在我们的目录结构如下：
```
├── manifest.json
└── background.js
└── sgamers.png
```

### 在chrome内加载插件

点击Chorme右上角的三个点按钮`...`> `More Tools` > `Extensions`

![image](http://sgstatic.boboyoho.com/howto1.png)

在右上角把Developer mode打开

![image](http://sgstatic.boboyoho.com/howto4.png)

再找到顶部的`LOAD UNPACKED`，把项目的根目录导入进去

![image](http://sgstatic.boboyoho.com/howto2.png)

项目导入后会出现一个新的卡片，是这个效果：

![image](http://sgstatic.boboyoho.com/howto8.png?v=2)

这时，你如果在Windows中按下`Alt+S`就会弹出消息，消息为`toggle-tags`，正好是我们在mainfest.json中定义好的。

同时我们可以点击上图蓝色键头所指示的`background page`，打开一个调试工具，可以看到`toggle-tags`的输出。

我们在之后本地编辑插件后，可以按灰色键头所指的刷新，新功能就能立即刷新加载了！

有了这些工作，意味着你可以进入下一步了！

### 标签页配置

一键打开/关闭赖皮网站，实现原理其实就是chrome的标签页功能。

标签页功能访问需要在manifest.json中添加权限
*==mainfest.json==*
```
  ...
  "permissions": ["tabs"]
  ...
```

接下来，我们写一下background.js实现通过快捷键(windows的Alt+S，或mac的Ctrl+S)创建新的主页:
*==background.js==*
```js
// 输入你想要的网站主页
const MainPageUrl = 'http://https://bbs.hupu.com/all-gambia'

chrome.commands.onCommand.addListener(function (command) {
  if (command === 'toggle-tags') {
    chrome.tabs.create({"url": MainPageUrl, "selected": true});
  }
})
```
其实实现很简单，就是调用chrome.tabs.create接口，就创建了一个新的标签页。
刷新一下插件，再试一试快捷键功能————是不是已经能控制浏览器弹出标签 页了！
![image](http://sgstatic.boboyoho.com/howto9.png)

### 实现具体逻辑：
稍显复杂的地方是标签页isOpen状态的处理，
下图主要关注isOpen状态的变化，以及tabCache的值变化。

```
graph TD
A(开始步骤:判断isOpen状态)-->|true| Y(情形1:清空tabCache缓存)
Y-->B(关闭所有符合域名的标签页)
A-->|false| C(情形2:检查标签页状态)
B --> X(将关闭的标签存入tabCache缓存数组)
X --> D(将isOpen状态改为false)
C --> |tabCache缓存有数据|E(把tabCache缓存的所有标签打开)
C --> |tabCache缓存没数据|F(查看是否有域名内标签)
F --> |没有域名内标签|G(新标签页打开主页)
F --> |有域名内标签|H(查看当前页是否在域名内)
G --> I(将isOpen状态改为true)
H --> |当前标签页不是域名内标签|K(定位到最近打开的域名内页面)
H --> |当前标签页就在域名内|J(关闭所有符合标签标签页)
E --> L(将isOpen状态改为true)
K --> M(将isOpen状态改为true)
J --> N(将isOpen状态设置为false)
```


具体后台逻辑如下，可以跟据备注、对照流程图进行理解：
```js
//初始化isOpen和tabCache状态
let isOpen = false
let tabCache = []

//新标签打开的主页
const mainPageUrl = 'https://bbs.hupu.com/all-gambia'
//四个赖皮网站的正则匹配表达式
const myPattern = 'sgamer\.com/|douyu\.com|hupu\.com|bilibili\.com'
//当前页面的Url
let currentPageUrl = ''

/**
 * 开始步骤： 判断isOpen状态
 * 情形一：isOpen为true，则移除页面
 * 情形二：isOpen为false，则重载页面
 */
chrome.commands.onCommand.addListener(function (command) {
  if (command === 'toggle-tags') {
    if (isOpen) {
      //情形一：isOpen为true
      removePages(myPattern)
      //情形二：isOpen为false
    } else {
      reloadPages(myPattern, mainPageUrl)
    }
  }
})


/**
 * 情形1：移除页面
 * 1、清空tabCache缓存
 * 2、关闭所有域名内标签
 * 3、将关闭的标签存入tabCache缓存数组
 * 4、将isOpen状态改为false
 */
function removePages(patternStr) {
  tabCache = []
  chrome.tabs.query({active: true}, function (tab) {
    currentPageUrl = tab[0].url
  })
  let pattern = new RegExp(patternStr)
  walkEveryTab(function (tab) {
    if (pattern.test(tab.url)) {
      chrome.tabs.remove(tab.id,function(){
        tabCache.push(tab.url)
      })
    }
  },function(){
    isOpen = false
  })
}

/**
 * 情形2：重载页面
 * 判断有没有缓存：
 *    情形2-1无缓存：开启新标签或定位到域名内的标签
 *    情形2-2有缓存：打开全部缓存内的页面
 */
function reloadPages(patternStr, mainPageUrl) {
  if (tabCache.length === 0) {
    focusOrCreateTab(patternStr, mainPageUrl)
  } else {
    openAllCachedTab(tabCache)
  }
}

/**
 * 情形2-1：开启新标签或定位到域名内的标签
 * 1、遍历全部标签，记录符合域名的标签的url，以及最后一个标签页
 * 2、如果没有符合域名的标签，则创建主页，并将isOpen状态改为true
 * 3、如果有符合域名的标签：
 *        1、获取当前的页面url
 *        2、如果当前页面url不符合域名，则定位到这个标签页，将isOpen状态改为true
 *        3、如果当前页面url符合域名，则关闭所有标签页（按情形1处理），将isOpen状态改为false
 */
function focusOrCreateTab(patternStr, url) {
  let pattern = new RegExp(patternStr)
  let theTabs = []
  let theLastTab = null
  walkEveryTab(function (tab) {
      if (pattern.test(tab.url)) {
        theTabs.push(tab.url)
        theLastTab = tab
      }
    }, function () {
      if (theTabs.length > 0) {
        chrome.tabs.query({active: true}, function (tab) {
          let currentUrl = tab[0].url
          if (theTabs.indexOf(currentUrl) > -1) {
            removePages(patternStr)
            isOpen = false
          } else {
            chrome.tabs.update(theLastTab.id, {"selected": true});
            isOpen = true
          }
        })
      } else {
        chrome.tabs.create({"url": url, "selected": true});
        isOpen = true
      }
    }
  )
}

/**
 * 情形2-2:
 * 1、把tabCache所有标签页重新打开
 * 2、将isOpen状态改为true
 */
function openAllCachedTab(tabCache) {
  let focusTab = null
  tabCache.forEach(function (url, index) {
    chrome.tabs.create({'url': url}, function (tab) {
      if (tab.url === currentPageUrl) {
        focusTab = tab.id
      }
      if (index === tabCache.length-1 - 1) {
        if (focusTab) {
          chrome.tabs.update(focusTab, {"selected": true},function(){
          });
        }
      }
    })
  })
  isOpen = true
}




/**
 *
 * @param callback
 * @param lastCallback
 * 包装一下遍历全部标签的函数，创建两个回调。
 * 一个回调是每一次遍历的过程中就执行一遍。
 * 一个回调是全部遍历完后执行一遍。
 */
function walkEveryTab(callback, lastCallback) {
  chrome.windows.getAll({"populate": true}, function (windows) {
    for (let i in windows) {
      let tabs = windows[i].tabs;
      for (let j in tabs) {
        let tab = tabs[j];
        callback(tab)
      }
    }
    if(lastCallback) lastCallback()
  })
}

```


## 上传与发布插件
我们需要在Chrome的开发者中心发布插件，进入 [Developer Dashboard](https://chrome.google.com/webstore/devconsole)



> 好了，一个简单易用的上班赖皮插件做好了！在调试模式下，你可以用ctrl+s来快捷寻找、打开、关闭、重新打开赖皮页面。随时随地、全方位赖皮，从容面对老板查岗。




# 可配置的高级赖皮插件
现在我希望我的插件都可以随时配置站点：

那么就需要用到chrome.storage了。

你需要打开storage权限：

**manifest.json**内添加
```json
...
  "permissions": [
    "tabs","storage"
  ],
...
```
然后使用
```js
chrome.storage.local.set({
        'value1':theValue1,
        'value2',theValue2
})
```
这种形式来存放storage。
这后使用
```js
chrome.storage.local.get(['value1'],(res)=>{
    const theValue1 = res.value1
})
```
这样得到存放的value值。


### 开始改写`background.js`
我们把`mainPageUrl`和`myPattern`改成从storage中获取。

```js
const INIT_SITES_LIST = ['bilibili.com','douyu.com','sgamer.com','hupu.com']
const INIT_MAIN_PAGE = 'https://bbs.hupu.com/all-gambia'

// 在安装时即设置好storage
chrome.runtime.onInstalled.addListener(function() {
  chrome.storage.local.set({
    sites: INIT_SITES_LIST,
    mainPage:INIT_MAIN_PAGE
  })
});


//初始化isOpen和tabCache状态
let isOpen = false
let tabCache = []
let currentPageUrl = ''

/**
 * 开始步骤： 判断isOpen状态
 * 情形一：isOpen为true，则移除页面
 * 情形二：isOpen为false，则重载页面
 */
chrome.commands.onCommand.addListener(function (command) {
  if (command === 'toggle-tags') {
    chrome.storage.local.get(['sites','mainPage'],function(res){
      let sites =  res.sites
      let mainPageUrl = res.mainPage
      let myPattern = sites.map(item=>item.replace('.','\\.')).join('|')
      console.log(myPattern)

      if (isOpen) {
        //情形一：isOpen为true
        removePages(myPattern)
        //情形二：isOpen为false
      } else {
        reloadPages(myPattern, mainPageUrl)
      }
    })

  }
})
// ======================== 下面的部分不需要改动，看到这里就够了）

/**
 * 情形1：移除页面
 * 1、清空tabCache缓存
 * 2、关闭所有域名内标签
 * 3、将关闭的标签存入tabCache缓存数组
 * 4、将isOpen状态改为false
 */
function removePages(patternStr) {
  tabCache = []
  chrome.tabs.query({active: true}, function (tab) {
    currentPageUrl = tab[0].url
  })
  let pattern = new RegExp(patternStr)
  walkEveryTab(function (tab) {
    if (pattern.test(tab.url)) {
      chrome.tabs.remove(tab.id,function(){
        tabCache.push(tab.url)
      })
    }
  },function(){
    isOpen = false
  })
}

/**
 * 情形2：重载页面
 * 判断有没有缓存：
 *    情形2-1无缓存：开启新标签或定位到域名内的标签
 *    情形2-2有缓存：打开全部缓存内的页面
 */
function reloadPages(patternStr, mainPageUrl) {
  if (tabCache.length === 0) {
    focusOrCreateTab(patternStr, mainPageUrl)
  } else {
    openAllCachedTab(tabCache)
  }
}

/**
 * 情形2-1：开启新标签或定位到域名内的标签
 * 1、遍历全部标签，记录符合域名的标签的url，以及最后一个标签页
 * 2、如果没有符合域名的标签，则创建主页，并将isOpen状态改为true
 * 3、如果有符合域名的标签：
 *        1、获取当前的页面url
 *        2、如果当前页面url不符合域名，则定位到这个标签页，将isOpen状态改为true
 *        3、如果当前页面url符合域名，则关闭所有标签页（按情形1处理），将isOpen状态改为false
 */
function focusOrCreateTab(patternStr, url) {
  let pattern = new RegExp(patternStr)
  let theTabs = []
  let theLastTab = null
  walkEveryTab(function (tab) {
      if (pattern.test(tab.url)) {
        theTabs.push(tab.url)
        theLastTab = tab
      }
    }, function () {
      if (theTabs.length > 0) {
        chrome.tabs.query({active: true}, function (tab) {
          let currentUrl = tab[0].url
          if (theTabs.indexOf(currentUrl) > -1) {
            removePages(patternStr)
            isOpen = false
          } else {
            chrome.tabs.update(theLastTab.id, {"selected": true});
            isOpen = true
          }
        })
      } else {
        chrome.tabs.create({"url": url, "selected": true});
        isOpen = true
      }
    }
  )
}

/**
 * 情形2-2:
 * 1、把tabCache所有标签页重新打开
 * 2、将isOpen状态改为true
 */
function openAllCachedTab(tabCache) {
  let focusTab = null
  tabCache.forEach(function (url, index) {
    chrome.tabs.create({'url': url}, function (tab) {
      if (tab.url === currentPageUrl) {
        focusTab = tab.id
      }
      if (index === tabCache.length-1 - 1) {
        if (focusTab) {
          chrome.tabs.update(focusTab, {"selected": true},function(){
          });
        }
      }
    })
  })
  isOpen = true
}




/**
 *
 * @param callback
 * @param lastCallback
 * 包装一下遍历全部标签的函数，创建两个回调。
 * 一个回调是每一次遍历的过程中就执行一遍。
 * 一个回调是全部遍历完后执行一遍。
 */
function walkEveryTab(callback, lastCallback) {
  chrome.windows.getAll({"populate": true}, function (windows) {
    for (let i in windows) {
      let tabs = windows[i].tabs;
      for (let j in tabs) {
        let tab = tabs[j];
        callback(tab)
      }
    }
    if(lastCallback) lastCallback()
  })
}



```






那么我们可以写一个popup页面，如果点击图标就会显示，如图：

![image](http://sgstatic.boboyoho.com/update10.png)


下面我们完善一下popup.html和popup.css和pupup.js页面

所有js文件都可以直接调用`chrome.storage.local.get`

只需要特别注意一下js文件的`chrome.storage`调用部分

其它的拷贝即可，我们不是来学页面布局的

**popup.html**
```html
<html>
<head>
  <title>常用网站配置页面</title>
  <link rel="stylesheet" href="popup.css">
</head>
<body>
<div class="container">
  <h2 class="lapi-title">常用赖皮站点域名</h2>
  <ul class="lapi-content">
  </ul>
  <p>
  <label><input type="text" id="add" class="add"></label>
  <button class="button add-button ">+</button>
  </p>
  <p></p>
  <p></p>
  <p></p>
  <h2>我的赖皮主页</h2>
  <div id="change-content">
  <span class="main-page-inactive" id="main-page-inactive"></span><button class="button change-button " id="change">✎</button>
  </div>

  <p class="lapi-tip">按<span class="lapi-key">Alt+S</span>快速开启/关闭赖皮站点</p>
</div>
<script src="zepto.min.js"></script>
<script src="popup.js"></script>
</body>
</html>
```


**popup.css**
```css
* {
    margin: 0;
    padding: 0;
    color:#6a6f77;
}


input, button, select, textarea {
    outline: none;
    -webkit-appearance: none;
    border-radius: 0;
    border: none;
}

input:focus{
    list-style: none;
    box-shadow: none;
}

ol, ul {
    list-style: none;
}

li{
    margin: 5px 0;
}

.container {
    width: 200px;
    padding: 10px;
}

.container h2{
    margin: 10px;
    text-align: center;
    display: block;
}

.lapi-content li{
    transition: opacity 1s;
}

.site{
    cursor: pointer;
    color: #00b0ff;
}

.add, .main-page{
    box-sizing: border-box;
    text-align:center;
    font-size:14px;
    /*height:27px;*/
    border-radius:3px;
    border:1px solid #c8cccf;
    color:#6a6f77;
    outline:0;
    padding:0 10px;
    text-decoration:none;
    width: 170px;
}

#main-page{
    font-size: 12px;
    text-align: left;
    width: 166px;
    margin: 0;
    padding: 2px;
}

.add{
    height: 27px;
}

.main-page{
    width: 170px;
    outline: none;
    resize: none;

}

.main-page-inactive{
    width: 160px;
    line-break: auto;
    word-break: break-word;
    overflow: hidden;
    display: inline-block;
    cursor: pointer;
    color: #00b0ff;
    margin: 3px;
}

.button{

    font-size: 16px;
    /*border: 1px solid #c8cccf;*/
    color: #c8cccf;
    /*border: none;*/
    padding: 0 4px 1px 3px;
    border-radius: 3px;
}

.close-button{
    transition: all 1s;
}

.button:hover{
    background: #E27575;
    color: #FFF;
}

.add-button{
    transition:all 1s;
    font-size: 20px;
    padding: 0 6px 1px 5px;
}

.change-button{
    position: absolute;
    transition:all 1s;
    font-size: 20px;
    padding: 0 6px 1px 5px;
}
.change-button:hover{
    background: #f9a825;
    color: #FFF;
}

#change-check{
    color: #f9a825;
}

#change-check:hover{
    color: #fff;
}

.add-button:hover{
    background: #B8DDFF;
    color: #FFF;
}

.submit{
    transition: all 1s;
    margin: 10px;
    padding: 5px 10px;
    font-size: 16px;
    border-radius: 4px;
    background: #B8DDFF;
    border: 1px solid #B8DDFF;
    color: #FFF;
}

.submit:hover{
    border: 1px solid #B8DDFF;
    background: #fff;
    color: #B8DDFF;
}

.fade{
    opacity: 0;
}

.add-wrong,.add-wrong:focus{
    border: #e91e63 1px solid;
    box-shadow:0 0 5px rgba(233,30,99,.3);
}

.lapi-tip{
    margin-top: 20px;
    border-top: 1px solid #c8cccf;
    padding-top: 8px;
    color: #c8cccf;
    text-align: center;
}

.lapi-key{
    color: #B8DDFF;

}
```


**重点关注：`chrome.storage`部分**

**popup.js**
```js
let sites = []
let mainPage = ''
const isMac = /Macintosh/.test(navigator.userAgent)
let $lapiKey = $('.lapi-key')

isMac? $lapiKey.text('Control+S'):$lapiKey.text('Alt+S')

// 从storage中取出site和mainPage字段，并设置在页面上。
chrome.storage.local.get(['sites','mainPage'], function (res) {
  if (res.sites) {
    sites = res.sites
    mainPage = res.mainPage
    sites.forEach(function (item) {
      let appendEl = '<li><span class="site">' + item + '</span>\n' +
        '<button class="button close-button">&times</button>\n' +
        '</li>'
      $('ul.lapi-content').append(appendEl)
    })

  }
  $('#main-page').val(mainPage)
  $('#main-page-inactive').html(mainPage)
})


$('#save').on('click', function () {
  alert()
})

$('#change-content').delegate('#main-page-inactive','click',function(){
  let mainPageUrl = $(this).html()
  if(/^http:\/\/|^https:\/\//.test(mainPageUrl)){
    chrome.tabs.create({"url": mainPageUrl, "selected": true})
  }else{
    chrome.tabs.create({"url": 'http://'+mainPageUrl, "selected": true})
  }
})


let addEl = $('#add')
addEl.focus()
let lapiCon = $('ul.lapi-content')

lapiCon.delegate('.close-button', 'click', function () {
  let $this = $(this)
  let siteValue = $this.siblings().html()
  sites = sites.filter(function (item) {
    return item !== siteValue
  })
  chrome.storage.local.set({sites: sites})
  $this.parent().addClass('fade')
  setTimeout(function () {
    $this.parent().remove()
  }, 800)
})


$('.add-button').on('click',addEvent)
addEl.bind('keypress',function(event){
  if(event.keyCode === 13) addEvent()
})


function addEvent(){
  if(!validate(addEl.val())){
    addEl.addClass('add-wrong')
  }else{
    let appendEl = '<li><span class="site">' + addEl.val() + '</span>\n' +
      '<button class="button close-button">&times</button>\n' +
      '</li>'
    $('ul.lapi-content').append(appendEl)
    sites.push(addEl.val())
    chrome.storage.local.set({sites:sites})
    addEl.removeClass('add-wrong')
    addEl.focus().val('')
  }
}

function validate(value){
  value = value.trim()
  if(value.length ===0){
    return false
  }
  return /^([\w_-]+\.)*[\w_-]+$/.test(value)
}

lapiCon.delegate('.site','click',function(){
  let siteUrl = $(this).html()
  chrome.tabs.create({"url": 'http://'+siteUrl, "selected": true})
})

$('#change-content').delegate('#change','click',function(){
  changeMainPage($(this))
}).delegate('#change-check','click',function(){
  changeCheck($('#change-check'))
}).delegate('#main-page','blur',function(){
  changeCheck($('#change-check'))
})


function changeMainPage($this){
  $this.siblings().remove()
  $this.parent().prepend('<label><textarea id="main-page" class="main-page"></textarea></label>')
  $this.parent().append('<button class="button change-button " id="change-check">✓</button>')
  $('#main-page').val(mainPage).focus()
  $this.remove()
}


function changeCheck($this){
  let mainPageVal = $('#main-page').val()
  $this.siblings().remove()
  $this.parent().prepend('<span class="main-page-inactive" id="main-page-inactive"></span>')
  $('#main-page-inactive').text(mainPageVal)
  chrome.storage.local.set({mainPage:mainPageVal})
  $this.parent().append('<button class="button change-button " id="change">✎</button>')
}
```

好了，一个优雅的赖皮插件就做好了
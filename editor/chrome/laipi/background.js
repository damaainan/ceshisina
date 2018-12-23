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



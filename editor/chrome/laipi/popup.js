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
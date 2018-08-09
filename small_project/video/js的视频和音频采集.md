## js的视频和音频采集

来源：[http://www.cnblogs.com/suyuanli/p/9446275.html](http://www.cnblogs.com/suyuanli/p/9446275.html)

时间 2018-08-08 22:56:00

 
## js的视频和音频采集
 
 
* 今天要写的，不是大家平时会用到的东西。因为兼容性实在不行，只是为了说明下前端原来还能干这些事。
大家能想象前端是能将摄像头和麦克风的视频流和音频流提取出来，再为所欲为的么。或者说我想把我canvas画板的内容录制成一个视频，这些看似js应该做不到的事情，其实都是可以做到的，不过兼容性不好。我在这里都是以chrome浏览器举的例子。
  
 
 
这里先把用到的api列一下：
 
 
* getUserMedia：打开摄像头和麦克风的接口（ [文档链接][3] ）  
* MediaRecorder：采集音视频流（ [文档链接][4] ）  
* srcObject：video标签可直接播放视频流,这是一个大家应该很少用到其实兼容性很好的属性，推荐大家了解（ [文档链接][5] ）  
* captureStream：可以将canvas输出流，其实不单单是canvas这里只是举有这个功能，具体的可以看文档（ [文档链接][6] ）
## 1、从摄像头展示视频
#### 一、打开摄像头
  
 
 
```js
// 这里是打开摄像头和麦克设备（会返回一个Promise对象）
navigator.mediaDevices.getUserMedia({
  audio: true,
  video: true
}).then(stream => {
  console.log(stream) // 放回音视频流
}).catch(err => {
  console.log(err)  // 错误回调
})
```
 
上面我们成功打开了摄像头和麦克风，并获取到视频流。那接下来就是要把流呈现到交互界面中了。
 
#### 二、展示视频
 
```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>
<body>
  <video id="video" width="500" height="500" autoplay></video>
</body>
<script>
  var video = document.getElementById('video')
  navigator.mediaDevices.getUserMedia({
    audio: true,
    video: true
  }).then(stream => {
    // 这里就要用到srcObject属性了，可以直接播放流资源
    video.srcObject = stream
  }).catch(err => {
    console.log(err)  // 错误回调
  })
</script>
```
 
  
效果如下图：
 
  
![][0]
 
到这里为止我们已经成功的将我们的摄像头在页面展示了。下一步就是如何将采集视频，并下载视频文件。
 
 
 
## 2、从摄像头采集视频
 
这里用到的是MediaRecorder对象：
 
 
* 创建一个新的MediaRecorder对象,返回一个MediaStream 对象用来进行录制操作,支持配置项配置容器的MIME type (例如"video/webm" or "video/mp4")或者音频的码率视频码率 
 
 
MediaRecorder接收两个参数第一个是stream音视频流，第二个是option配置参数。下面我们可以把上面摄像头获取的流加入MediaRecorder中。
 
```js
var video = document.getElementById('video')
navigator.mediaDevices.getUserMedia({
  audio: true,
  video: true
}).then(stream => {
  // 这里就要用到srcObject属性了，可以直接播放流资源
  video.srcObject = stream
  var mediaRecorder = new MediaRecorder(stream, {
    audioBitsPerSecond : 128000,  // 音频码率
    videoBitsPerSecond : 100000,  // 视频码率
    mimeType : 'video/webm;codecs=h264' // 编码格式
  })
}).catch(err => {
  console.log(err)  // 错误回调
})
```
 
在上面我们创建了MediaRecorder的实例mediaRecorder。接下来就是控制mediaRecorder的开始采集和停止采集的方法了。
 
MediaRecorder提供了一些方法和事件供我们使用：
 
 
* MediaRecorder.start(): 开始录制媒体,这个方法调用时可以通过给timeslice参数设置一个毫秒值,如果设置这个毫秒值,那么录制的媒体会按照你设置的值进行分割成一个个单独的区块, 而不是以默认的方式录制一个非常大的整块内容. 
* MediaRecorder.stop(): 停止录制. 同时触发dataavailable事件,返回一个存储Blob内容的录制数据.之后不再记录 
* ondataavailable事件： MediaRecorder.stop触发该事件，该事件可用于获取记录的媒体（Blob在事件的data属性中可用作对象） 
 
 
```js
// 这里我们增加两个按钮控制采集的开始和结束
var start = document.getElementById('start')
var stop = document.getElementById('stop')
var video = document.getElementById('video')
navigator.mediaDevices.getUserMedia({
  audio: true,
  video: true
}).then(stream => {
  // 这里就要用到srcObject属性了，可以直接播放流资源
  video.srcObject = stream
  var mediaRecorder = new MediaRecorder(stream, {
    audioBitsPerSecond : 128000,  // 音频码率
    videoBitsPerSecond : 100000,  // 视频码率
    mimeType : 'video/webm;codecs=h264' // 编码格式
  })
  // 开始采集
  start.onclick = function () {
    mediaRecorder.start()
    console.log('开始采集')
  }
  // 停止采集
  stop.onclick = function () {
    mediaRecorder.stop()
    console.log('停止采集')
  }
  // 事件
  mediaRecorder.ondataavailable = function (e) {
    console.log(e)
    // 下载视频
    var blob = new Blob([e.data], { 'type' : 'video/mp4' })
    let a = document.createElement('a')
    a.href = URL.createObjectURL(blob)
    a.download = `test.mp4`
    a.click()
  }
}).catch(err => {
  console.log(err)  // 错误回调
})
```
 
ok，现在执行一波操作；
 
![][1]
 
上图可以看到结束采集后ondataavailable事件返回的数据中有一个Blob对象，这就是视频资源了，再接下来我们就可以通过URL.createObjectURL()方法将Blob为url下载到本地了。视频的采集到下载就结束了，很简单粗暴。
 
上面是视频采集下载的例子，如果只要音频采集的，同样道理的设置“mimeType”就好了。这里我就不举例了。下面我在介绍将canvas录制为一个视频文件
 
## 2、canvas输出视频流
 
 
* 这里用到的是captureStream方法，将canvas输出流，再用video展现，或者用MediaRecorder采集资源也是可以的。 
 
 
```html
// 这里就闲话少说直接上重点了因为和上面视频采集的是一样的道理的。
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>
<body>
  <canvas width="500" height="500" id="canvas"></canvas>
  <video id="video" width="500" height="500" autoplay></video>
</body>
<script>
  var video = document.getElementById('video')
  var canvas = document.getElementById('canvas')
  var stream = $canvas.captureStream(); // 这里获取canvas流对象
  // 接下来你先为所欲为都可以了，可以参考上面的我就不写了。
</script>
```
 
下面我再贴一个gif(这是结合我上次写的canvas事件的demo结合这次视频采集的结合)传送门（Canvas事件绑定）
 
 
* 希望大家可以实现下面的效果，其实还可以在canvas视频里插入背景音乐什么的，这些都比较简单。 
 
 
![][2]
 


[3]: https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia%20
[4]: https://developer.mozilla.org/zh-CN/docs/Web/API/MediaRecorder
[5]: https://developer.mozilla.org/en-US/docs/Web/API/HTMLMediaElement/srcObject
[6]: https://developer.mozilla.org/en-US/docs/Web/API/HTMLMediaElement/captureStream
[0]: https://img1.tuicool.com/FrQNziV.gif
[1]: https://img1.tuicool.com/BRbmU3R.jpg
[2]: https://img0.tuicool.com/6FNr6vA.gif
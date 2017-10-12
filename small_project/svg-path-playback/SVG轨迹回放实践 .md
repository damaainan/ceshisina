# SVG轨迹回放实践 

最近做了埋点方案XTracker的轨迹回放功能，大致效果就是，在指定几个顺序的点之间形成轨迹，来模拟用户在页面上的先后行为（比如一个用户先点了啥，后点了啥）。效果图如下：

[![](https://wx1.sinaimg.cn/mw690/83900b4egy1fk1jn3atyng204h04ead8.gif)](https://wx1.sinaimg.cn/mw690/83900b4egy1fk1jn3atyng204h04ead8.gif "")

在这篇文章中，我们来聊聊轨迹回放的一些技术细节。

> 注意，本文只关注轨迹的绘制，并不讨论轨迹的各种生成算法。

## 绘制红点坐标

在绘制轨迹前，需要先绘制轨迹经过的红点坐标。使用SVG绘制红点非常简单：

```
<svg width="500" height="500">

  <circle r="5" cx="50" cy="55" fill="red"></circle>

</svg>
```
[![](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy4xjj4aij203403g0py.jpg)](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy4xjj4aij203403g0py.jpg "")

然后根据需要多画几个红点就可以了，也可以通过js批量生成：


```
function createCircles() {

  var r = "5",

    fill = "red",

    // circleGroup是红点的容器

    circleGroup = document.querySelector("#circle-group");

  // pointList是红点的坐标集合

  pointList.forEach(function(point) {

    var circle = document.createElementNS(

      "http://www.w3.org/2000/svg",

      "circle"

    );

    circle.setAttribute("r", r);

    circle.setAttribute("cx", point[0]);

    circle.setAttribute("cy", point[1]);

    circle.setAttribute("fill", fill);

    circleGroup.appendChild(circle);

  });

}
```
[![](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy50l2qlrj20dw0d8wei.jpg)](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy50l2qlrj20dw0d8wei.jpg "")

## 两点之间的轨迹

红点坐标画完了，我们来画轨迹。在画多点的轨迹之前，我们先来学习两点之间的轨迹，也就是两点之间曲线的画法。

### 二次贝塞尔曲线、三次贝塞尔曲线还是圆弧？

SVG通过path可以画多种曲线主要包括：

* 二次贝塞尔曲线：需要一个控制点，用来确定起点和终点的曲线斜率。  
[![](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy6fw4aeuj205a04g3yd.jpg)](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy6fw4aeuj205a04g3yd.jpg "")
* 三次贝塞尔曲线：需要两个控制点，用来确定起点和终点的曲线斜率。  
[![](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy6fi0z8pj205a04gglj.jpg)](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy6fi0z8pj205a04gglj.jpg "")
* 圆弧：需要两个半径、旋转角度、逆时针还是顺时针、大圆弧还是小圆弧等多个属性。  
[![](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy6hbu8zlj205k05kt8o.jpg)](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy6hbu8zlj205k05kt8o.jpg "")

显然，二次贝塞尔曲线最为简单，所以我们决定用二次贝塞尔曲线来画两点之间的弧线。在SVG的path中，二次贝塞曲线的参数是：

    M x1 y1 Q x2 y2 x3 y3

其中x1 y1是起点，x2 y2是控制点，x3 y3是终点。来个demo吧！

```
<svg width="320px" height="320px">

  <path id="line1" stroke="black" fill="none" d="M 0 50 Q 25 10 50 50"></path>

</svg>
```
效果：

[![](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy6s1oe7uj203c02gq2p.jpg)](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy6s1oe7uj203c02gq2p.jpg "")

### 确定控制点

确定了使用二次贝塞尔曲线，那么问题又来了，如何确定控制点呢？控制点决定了曲线的斜率和方向，我们期望曲线：

* 对称。
* 接近直线，稍微弯曲即可，太弯可能会超出画布范围。
* 曲线永远顺时针，这样可以保证，A点到B点的曲线和B点到A点的曲线不重合。

要想做到这三点，我们只需要让控制点：

* 在两点的中垂线上。
* 距离两点的中点等于某个较小的固定值。
* 在起点和终点的顺时针区域。

画个图吧！

[![](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy7sndvwjj20hy0cgjrr.jpg)](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy7sndvwjj20hy0cgjrr.jpg "")

* 在顺时针区域画中垂线。中垂线和垂直线的角度为angle
* 规定offset为某个定值（比如40，或者其他比较小的定值）。
* 那么控制点相对于中点的偏移值就确定了：
  * offsetX = Math.sin(angle) * offset;
  * offsetY = -Math.cos(angle) * offset;

完整算法：

    
```
function getCtlPoint(startX, startY, endX, endY, offset) {

  var offset = offset || 40;

  var angle = Math.atan2(endY - startY, endX - startX);

  var offsetX = Math.sin(angle) * offset;

  var offsetY = -Math.cos(angle) * offset;

  var ctlX = (startX + endX) / 2 + offsetX;

  var ctlY = (startY + endY) / 2 + offsetY;

  return [ctlX, ctlY];

}
```
### 起点终点相同的情况

如果起点终点相同，我们就不能使用二次贝塞尔曲线了，而是应该在该点右侧画一个小圆弧，就像这样：

[![](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy8a993prj203e032a9t.jpg)](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy8a993prj203e032a9t.jpg "")

在Path中圆弧的参数格式为：

    A rx ry x-axis-rotation large-arc-flag sweep-flag x y

* 弧形命令A的前两个参数分别是x轴半径和y轴半径。
* x-axis-rotation表示弧形的旋转情况。
* large-arc-flag决定弧线是大于还是小于180度，0表示小角度弧，1表示大角度弧。
* sweep-flag表示弧线的方向，0表示从起点到终点沿逆时针画弧，1表示从起点到终点沿顺时针画弧。
* 最后两个参数是指定弧形的终点。

> 弧形命令A的具体用法不属于本文范畴，请参考：[> https://developer.mozilla.org/zh-CN/docs/Web/SVG/Tutorial/Paths][0]>  。

因为我们要求：

* 圆弧接近于圆，不是椭圆。
* 圆弧在右侧。
* 大于180度。

所以，我们的圆弧参数为：

* x轴和y轴半径同为某个很小的定值（我们就设为10吧）
* x-axis-rotation为0，不需要旋转，既然是圆，转了也白转。
* large-arc-flag为1，显然大于180度。
* sweep-flag为1或0都行，不过要保证为1时，终点稍微比起点靠下一点，这样才能保证圆弧在右边。

示例代码：

```
<svg width="320px" height="320px">

  <path id="line1" stroke="black" fill="none" d="M 50 50 A 10 10 0 1 1 50 50.1"></path>

</svg>
```
效果截图：

[![](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy8a993prj203e032a9t.jpg)](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy8a993prj203e032a9t.jpg "")

将两种情况封装成获取d属性的函数：

```
function getD(startX, startY, endX, endY) {

  var ctlPoint = getCtlPoint(startX, startY, endX, endY, 40);

  var d = ["M", startX, startY].join(" ");

  if (startX !== endX || startY !== endY) {

    d += [" Q", ctlPoint[0], ctlPoint[1], endX, endY].join(" ");

  } else {

    d += [" A", 10, 10, 0, 1, 1, endX, endY + 0.1].join(" ");

  }

  return d;

}
```
完整demo：

[https://codepen.io/lewis617/pen/JrWMBy/][1]

## 多点之间的轨迹

两点之间弧线确定了，那么如何确定多点之间的轨迹呢？其实很简单，只需要在命令后面加上新的控制点和终点即可：

    M x1 y1 Q x2 y2 x3 y3 Q x4 y4 x5 y5

所以只需要简单更新一下之前封装的函数即可：

```

function getD(pointList){

  var offset = offset || 40;

  var d = (['M' ,pointList[0][0], pointList[0][1]]).join(' ');

  pointList.forEach(function(point, i){

    if(i>0){

      var startX = pointList[i-1][0],

          startY = pointList[i-1][1],

          endX = point[0], 

          endY = point[1];

      var ctlPoint = getCtlPoint(startX, startY, endX, endY, offset);

      if(startX !== endX || startY !== endY){

        d+=([' Q', ctlPoint[0], ctlPoint[1], endX, endY]).join(' ');

      }else{

        d+=([' A', 10, 10, 0, 1, 1, endX, endY + 0.1]).join(' ');

      }

    }

  })

  return d;

}
```
如果pointList为：
```
var pointList = [

  [0, 50],

  [0, 50],

  [50, 50],

  [100, 50],

  [0, 100],

  [50, 100],

  [100, 100],

];
```
那么效果图：

[![](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy8i3vmmij206w05s3yh.jpg)](https://wx3.sinaimg.cn/mw690/83900b4egy1fjy8i3vmmij206w05s3yh.jpg "")

完整demo：

[https://codepen.io/lewis617/pen/wrJpGY/][2]

## 让轨迹回放起来

轨迹画完了，如何让它回放呢？这里需要用到这两个属性：

[stroke-dasharray][3]：控制用来描边的点划线的图案范式。

[stroke-dashoffset][4]：指定了dash模式到路径开始的距离。

* 先设置stroke-dasharray为 "length length"，来让曲线颜色和空白的长度均为曲线长度。
* 然后设置stroke-dashoffset初始状态为曲线长度，来保证整个曲线”看起来”都是空白。
* 最后渐变stroke-dashoffset属性为0，来模拟画线。

如何渐变呢？使用[SVG SMIL animation][5]。

关键代码：

```
var length = path.getTotalLength();

path.setAttribute("stroke-dasharray", length + " " + length);

path.setAttribute("stroke-dashoffset", length);

path.innerHTML= '<animate attributeName="stroke-dashoffset" to="0"  dur="7s" begin="0s" fill="freeze" repeatCount="indefinite"></animate>';
```
完整demo：

[![](https://wx3.sinaimg.cn/mw690/83900b4egy1fk1jn3f98vg204h04edfz.gif)](https://wx3.sinaimg.cn/mw690/83900b4egy1fk1jn3f98vg204h04edfz.gif "")

[https://codepen.io/lewis617/pen/vexjyp/][6]

## 给轨迹加上“圆头”

马上就可以看见胜利的曙光了，最后我们来做轨迹的“圆头”：

* 圆头就是个圆点（circle）
* 圆点需要跟着轨迹一起移动

画一个圆点很简单，那么如何画一个按照轨迹移动的圆点呢？答案是：[animateMotion元素][7]。

关键代码：
```

function createPathHead(pathObj, d){

  var r = 3;

  var head = document.createElementNS("http://www.w3.org/2000/svg", "circle");

  head.setAttribute("id", pathObj.id + "-head");

  head.setAttribute("r", r);

  head.setAttribute("fill", pathObj.stroke);

  var animateMotion = document.createElementNS("http://www.w3.org/2000/svg", "animateMotion");

  animateMotion.setAttribute("path", d);

  animateMotion.setAttribute("begin", "indefinite");

  animateMotion.setAttribute("dur", "7s");

  animateMotion.setAttribute("fill", "freeze");

  animateMotion.setAttribute("rotate", "auto");

  head.appendChild(animateMotion);

  return head;

}
```
至此，轨迹回放的关键技术点就讲完了，再次欣赏下最终的效果：

[![](https://wx1.sinaimg.cn/mw690/83900b4egy1fk1jn3atyng204h04ead8.gif)](https://wx1.sinaimg.cn/mw690/83900b4egy1fk1jn3atyng204h04ead8.gif "")

完整的demo在这里：

[https://codepen.io/lewis617/pen/RLpxPj/][8]

**本文作者：**[刘一奇][9]

**本文出处：**[刘一奇的个人博客][10]

**本文链接：**[http://www.liuyiqi.cn/2017/09/27/svg-path-playback/][11]

**发布时间：** 2017年9月27日 - 19时09分 

**版权声明：** 本文由 刘一奇 原创，采用 [保留署名-非商业性使用-禁止演绎 4.0-国际许可协议][12]  
转载请保留以上声明信息！

[0]: https://developer.mozilla.org/zh-CN/docs/Web/SVG/Tutorial/Paths
[1]: https://codepen.io/lewis617/pen/JrWMBy/
[2]: https://codepen.io/lewis617/pen/wrJpGY/
[3]: https://developer.mozilla.org/zh-CN/docs/Web/SVG/Attribute/stroke-dasharray
[4]: https://developer.mozilla.org/zh-CN/docs/Web/SVG/Attribute/stroke-dashoffset
[5]: https://developer.mozilla.org/zh-CN/docs/Web/SVG/SVG_animation_with_SMIL
[6]: https://codepen.io/lewis617/pen/vexjyp/
[7]: https://developer.mozilla.org/zh-CN/docs/Web/SVG/Element/animateMotion
[8]: https://codepen.io/lewis617/pen/RLpxPj/
[9]: /about/
[10]: /
[11]: http://www.liuyiqi.cn/2017/09/27/svg-path-playback/
[12]: https://creativecommons.org/licenses/by-nc-nd/4.0/deed.zh
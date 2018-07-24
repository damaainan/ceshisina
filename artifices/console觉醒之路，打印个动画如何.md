## console觉醒之路，打印个动画如何？

来源：[https://juejin.im/post/5afafb0c6fb9a07ac65331fe](https://juejin.im/post/5afafb0c6fb9a07ac65331fe)

时间 2018-05-17 10:35:17

 
console作为前端调试中广泛使用的成员之一，忠实担任了明察秋毫的数据检阅师，又默默承受了万千bug的狂风骤雨，它log时云淡风轻，它debug时诚如明镜，它info时温柔细腻，它warn时憋黄了脸，它error时急红了眼，它咆哮，它又彷徨。
  
有人站出来了，说：“console，给你个兼职吧。”
 
于是它被安排在了首页广场上，在众人的注视下一动不动，高举着雇主的横幅：
 
 ![][0]
 
 ![][1]
 
放下横幅，一切又变得索然无味。
 
呐，不如，我来帮帮你吧。
 
### 小试
 
“console，让我给你摸摸骨。”
 
 ![][2]
 
“嗯，不错，自带点阵图基因。”
 
我摸起没有胡须的下巴说道。
 
```js
console.log('%c你%c说%c什么%c?', 'background: #000; color: #fff','color: blue','color: red; border-bottom: 1px solid red','background: blue; color: #fff; border-radius: 50%;');
```
 
 ![][3]
 
“哟呵，还蕴藏样式变化！”
 
“这是我前两天手写的××宝典，我看你骨骼清奇，不要998，只要9块8，包邮免费寄到家！”
 
cosole听完迟疑了，而我已经来不及收手了。
 
### 构建
 
【突然一本正经脸】
 
我们知道，二维图像微观上是由一个个精巧排布的不同颜色的像素点组成，而动画则是这些像素点不断按规律地变换样式颜色，加上人眼的视觉暂留现象，从而形成看上去连贯的动画。
 
console.log 很显然能满足拼凑出图像所需要的条件：
 
 
* 一个字符的打印位置便可以对应一个像素点的排列位置 
* 一个字符的css样式可以对应一个像素点的颜色样式 
 
 
而让这图像里的内容动起来，可以像canvas那样启用“渲染”，`每次“渲染”时先使用console.clear()清除掉上一次打印出的字符，然后计算场景中需移动的字符本次所在的位置，打印出字符到该位置，一定时间间隔后进行下一次“渲染”操作。`为了实现上述效果，需要构建出console眼中的二维世界观。
 
一个完整的二维图像，可以由若干个子图像组成，即元素（element），例如这个糊一脸井号的心:heartpulse:：
 
```js
##   ##
#### ####
 #######
  #####
    #
```
 
将它放入图像场景中（scene）中，它便拥有在该场景中的位置属性。
 
同时多个element也可以放入一个组合（group）中，组合再放入场景，组合里的元素便相对于该组合计算位置，即可以随着组合整体而移动位置，见下图：
 
 ![][4]
 `scene与group均是图像容器，只有element是携带了子图像信息的实体。`接着，要想把场景中的所有内容按照他们自己的位置与样式打印出来，就得需要渲染器（renderer）了，`renderer将场景中的element逐个取出，计算出其对应的绝对位置坐标后，将element包含的像素信息，一一映射到renderer所维护的二维数组canvas中；`该过程结束后，得到的canvas便是该场景包含的所有图像信息，打印它到屏幕上，显示本次图像的任务就完成了。
 
 ![][5]
 
社会主义核心价...哦不对，console版二维世界观便如上所述，接下来开始撸代码。
 
### 实现
 
先取个名字吧： **`ConsoleCanvas`** 
 
1. 实现场景类`Scene``Scene`本身作为容器，属性中的`elements`维护着该场景中包含的元素，场景的`add`方法用于向场景中添加元素，若添加的是组合，则会提取出组合里的元素放入场景。
 
```js
window.ConsoleCanvas = new function() {
    // 场景
    this.Scene = function(name = '', style) {
        // 场景元素集合
        this.elements = [];
        // 场景样式
        this.style = Object.prototype.toString.call(style) === '[object Array]' ? style : [];
        // 场景名称
        this.name = name.toString();
    };
    // 场景添加元素或组合
    this.Scene.prototype.add = function(ele) {
        if (!ele) {
            return;
        }
        ele.belong = this;
        // 添加的元素是组合元素
        if (ele.isGroup) {
            // 提出组合里的元素归入场景
            this.elements.push(...ele.elements);
            return;
        }
        this.elements.push(ele);
    };
    
    /* 后续代码块均承接此处 */
    
}
```
 
2. 实现元素类`Element` 

```js
//vals:元素字符内容，style：元素样式，z_index：层叠优先级，position：位置
this.Element = function(vals = [[]], style = [], z_index = 1, position) {
    // 元素随机id
    this.id = Number(Math.random().toString().substr(3, 1) + Date.now()).toString(36);
    this.vals = vals;
    this.style = style;
    this.z_index = z_index;
    // 元素缩放值
    this.scale_x = 1;
    this.scale_y = 1;
    this.position = {
        x: position && position.x ? position.x : 0,
        y: position && position.y ? position.y : 0
    },
    // 元素所属的组合
    this.group = null;
    // 元素所属的场景
    this.belong = null;
};
```
 
元素中的 **`vals`**  属性是一个二维数组，保存着该元素的点阵图，例如之前的心形会以如下形态保存在vals中：
 
```js
this.vals = [
    [' ','#','#',' ',' ',' ','#','#'],
    ['#','#','#','#',' ','#','#','#','#'],
    [' ','#','#','#','#','#','#','#'],
    [' ',' ','#','#','#','#','#'],
    [' ',' ',' ',' ','#']
];
```
 
给`Element`类添加操作方法：
 
 
* `clone`，复制元素，这里只是简单复制了`vals`、`style`、`z_index`、`position`等信息来生成新元素。  
 
 
```js
// 元素克隆
this.Element.prototype.clone = function() {
    return new this.constructor(JSON.parse(JSON.stringify(this.vals)), this.style.concat(), this.z_index, this.position);
};
```
 
 
* `remove`，从场景中删除元素自身：  
 
 
```js
// 元素删除
this.Element.prototype.remove = function() {
    // 获取元素所属场景
    let scene = this.group ? this.group.belong : this.belong;
    // 根据元素id从场景中查询到该元素index
    let index = scene.elements.findIndex((ele) => {
        return ele.id === this.id;
    });
    if (index >= 0) {
        // 从场景中去除该元素项
        scene.elements.splice(index, 1);
    }
};
```
 
 
* `width`，获取或者设置元素的最小包围盒的宽度：  
 
 
```js
// 元素获取宽度或者设置宽度（裁剪宽度）
this.Element.prototype.width = function(width) {
    width = parseInt(width);
    if (width && width > 0) {
        // 设置宽度，只用于裁剪，拓宽无效
        for (let j = 0; j < this.vals.length; j++) {
            this.vals[j].splice(width);
        }
        return width;
    } else {
        // 获取宽度
        return Math.max.apply(null, this.vals.map((v) => {
            return v.length;
        }));
    }
};
```
 
 
* `height`，获取或者设置元素的最小包围盒的高度：  
 
 
```js
// 元素获取高度或者设置高度（裁剪高度）
this.Element.prototype.height = function(height) {
    height = parseInt(height);
    if (height && height > 0) {
        // 设置高度，只用于裁剪，拓高无效
        this.vals.splice(height);
        return height;
    } else {
        // 获取高度
        return this.vals.length;
    }
};
```
 
 
* `scaleX`，每个像素都要根据缩放值迁移下位置，为了避免先缩小再放大会出现的失真情况，隐藏保留了元素字符图案的原始副本，每次缩放都根据原始图案来操作。  
 
 
```js
// 元素横坐标缩放
this.Element.prototype.scaleX = function(multiple, flag) {
    let i, j;
    let scaleY = this.scale_y;
    multiple = +multiple;
    if (this.valsCopy) {
        // 每次变换使用原始图案进行
        this.vals = JSON.parse(JSON.stringify(this.valsCopy));
    } else {
        // 首次使用时保存原图案副本
        this.valsCopy = JSON.parse(JSON.stringify(this.vals));
    }
    if (!flag) {
        // 使用原始图案重新缩放纵坐标（避免失真），flag用于避免循环嵌套
        this.scaleY(this.scale_y, true);
    }
    if (multiple < 1) {
        for (j = 0; j < this.vals.length; j++) {
            for (i = 0; i < this.vals[j].length; i++) {
                [this.vals[j][Math.ceil(i * multiple)], this.vals[j][i]] = [this.vals[j][i], ' '];
            }
        }
        // 裁去缩小后的多余部分
        for (j = 0; j < this.vals.length; j++) {
            this.vals[j].splice(Math.ceil(this.vals[j].length * multiple));
        }
        this.scale_x = multiple;
    } else if (multiple > 1) {
        for (j = 0; j < this.vals.length; j++) {
            for (i = this.vals[j].length - 1; i > 0; i--) {
                [this.vals[j][Math.ceil(i * multiple)], this.vals[j][i]] = [this.vals[j][i], ' '];
            }
        }
        // 填充放大后的未定义像素
        for (j = 0; j < this.vals.length; j++) {
            for (i = this.vals[j].length - 1; i > 0; i--) {
                if (this.vals[j][i] === undefined) {
                    this.vals[j][i] = ' ';
                }
            }
        }
        this.scale_x = multiple;
    } else {
        this.scale_x = 1;
        return;
    }
};
```
 
 
* `scaleY`，原理同scaleX，区别在于scaleX逐行遍历迁移像素，而scaleY是逐列遍历迁移像素。  
 
 
```js
// 元素纵坐标缩放
this.Element.prototype.scaleY = function(multiple, flag) {
    let i, j;
    multiple = +multiple;
    if (this.valsCopy) {
        // 每次变换使用原始图案
        this.vals = JSON.parse(JSON.stringify(this.valsCopy));
    } else {
        // 首次使用时保存原图案副本
        this.valsCopy = JSON.parse(JSON.stringify(this.vals));
    }
    if (!flag) {
        // 使用原始图案重新缩放横坐标（避免失真），flag用于避免循环嵌套
        this.scaleX(this.scale_x, true);
    }
    let length = this.width();
    if (multiple < 1) {
        for (i = 0; i < length; i++) {
            for (j = 0; j < this.vals.length; j++) {
                [this.vals[Math.floor(j * multiple)][i], this.vals[j][i]] = [this.vals[j][i], ' '];
            }
        }
        // 裁去缩小后的多余部分
        this.vals.splice(Math.ceil(this.vals.length * multiple));
        for (j = 0; j < this.vals.length; j++) {
            for (i = 0; i < this.vals[j].length; i++) {
                if (this.vals[j][i] === undefined) {
                    this.vals[j].splice(i);
                    break;
                }
            }
        }
        this.scale_y = multiple;
    } else if (multiple > 1) {
        let colLength = this.vals.length;
        for (i = 0; i < length; i++) {
            for (j = colLength - 1; j >= 0; j--) {
                if (!this.vals[Math.floor(j * multiple)]) {
                    // 开辟新数组空间
                    this.vals[Math.floor(j * multiple)] = [];
                }
                [this.vals[Math.floor(j * multiple)][i], this.vals[j][i]] = [this.vals[j][i], ' '];
            }
        }
        // 填充放大后的未定义像素
        for (j = 0; j < this.vals.length; j++) {
            if (this.vals[j]) {
                for (i = 0; i < this.vals[j].length; i++) {
                    if (this.vals[j][i] === undefined) {
                        this.vals[j].splice(i);
                        break;
                    }
                }
            } else {
                this.vals[j] = [' '];
            }
        }
        this.scale_y = multiple;
    } else {
        this.scale_y = 1;
        return;
    }
};
```
 
 
* `scale`，同时缩放元素横坐标与纵坐标：  
 
 
```js
// 元素缩放
this.Element.prototype.scale = function(x, y) {
    this.scaleX(+x);
    this.scaleY(+y);
};
```
 
3. 实现组合类`Group`
 
```js
// 元素组合
this.Group = function() {
    // 组合标志
    this.isGroup = true;
    // 存放的子元素
    this.elements = [];
    // 组合位置
    this.position = {
        x: 0,
        y: 0
    };
    // 组合层叠优先级
    this.z_index = 0;
};
```
 
为`Group`添加方法：
 
 
* `add`，往组合里添加元素：  
 
 
```js
// 组合添加子元素
this.Group.prototype.add = function(ele) {
    if (ele) {
    // 以数组形式添加多个子元素
    if (Object.prototype.toString.call(ele) === '[object Array]') {
        ele.forEach((item) => {
            this.elements.push(item);
            item.group = this;
        });
        return;
    }
    // 添加单个子元素
    this.elements.push(ele);
    ele.group = this;
    }
};
```
 
 
* `remove`，删除整个组合，即删除组合里包含的所有元素：、  
 
 
```js
// 删除组合
this.Group.prototype.remove = function() {
    this.elements.forEach((ele) => {
        ele.remove();
    })
};
```
 
4. 实现渲染器类`Renderer`
 
```js
// 渲染器
this.Renderer = function() {
   this.width = 10;
   this.height = 10;
   this.canvas = [];
};
```
 
为`Renderer`添加方法：
 
 
* `Pixel`，生成用于渲染的像素点：  
 
 
```js
// 生成用于渲染的像素点
this.Renderer.prototype.Pixel = function() {
   // 字符值
   this.val = ' ';
   // 样式数组值
   this.style = [];
   // 层叠优先级
   this.z_index = 0;
};
```
 
 
* `setSize`，设置渲染尺寸，即按尺寸大小开辟二维数组canvas的空间。  
 
 
```js
// 设置渲染画布的尺寸
this.Renderer.prototype.setSize = function(width, height) {
    this.width = parseInt(width);
    this.height = parseInt(height);
    this.canvas = [];
    for (let j = 0; j < height; j++) {
        this.canvas.push(new Array(width));
            for (let i = 0; i < width; i++) {
                this.canvas[j][i] = new this.Pixel();
            }
    }
};
```
 
 
* `clear`，清除画布：  
 
 
```js
// 清除画布
// x：开始清除的横坐标，y：开始清除的纵坐标，width：清除宽度，height：清除长度
this.Renderer.prototype.clear = function(x = 0, y = 0, width, height) {
    width = parseInt(width ? width : this.width);
    height = parseInt(height ? height : this.height);
    for (let j = y; j < y + height && j < this.height; j++) {
        for (let i = x; i < x + width && i < this.width; i++) {
            this.canvas[j][i].val = ' ';
            this.canvas[j][i].style = [];
            this.canvas[j][i].z_index = 0;
        }
    }
    // console清屏
    console.clear();
};
```
 
 
* `print`，带样式逐行打印canvas中的字符内容，在行数较多的情况下，逐行打印会引起很明显的屏幕闪烁。为什么不能一次性打印全部？尝试过了，在带%c带样式使用console.log时，换行符放其中并不能按想像中的那样换行，如下图：  
 
 
 ![][6]
 
```js
// 带样式打印字符，逐行打印呈现画布带样式的内容
// noBorder：不显示左右边框（默认显示）
this.Renderer.prototype.print = function(noBorder) {
    let row = '';
    let rowId = 0;
    let style = [];
    let borderRight = noBorder ? '' : 'border-left: 1px solid #ddd';
    let borderLeft = noBorder ? '' : 'border-right: 1px solid #ddd';
    for (let j = 0; j < this.canvas.length; j++) {
        row = noBorder ? '' : '%c ';
        // 每行的唯一id，避免console打印出同样的字符会堆叠显示
        rowId = '%c' + j;
        style = noBorder ? [] : [borderLeft];
        for (let i = 0; i < this.canvas[j].length; i++) {
            row += '%c' + this.canvas[j][i].val;
            style.push(this.canvas[j][i].style.join(';'));
        }
        style.push(`background: #fff; color: #fff;${borderRight}`);
        console.log(row + rowId, ...style);
    }
};
```
 
 
* `printNoStyle`，为优化之前的明显闪屏情况，提供一次性打印不带样式的字符内容的方法。  
 
 
```js
// 不带样式打印字符，一次打印呈现画布不带样式的内容
// noBorder：不显示左右边框（默认显示）
this.Renderer.prototype.printNoStyle = function(noBorder) {
    let row = '';
    let rows = '';
    let border = noBorder ? '' : '|';
    for (let j = 0; j < this.canvas.length; j++) {
        row = border;
        for (let i = 0; i < this.canvas[j].length; i++) {
            row += this.canvas[j][i].val;
        }
        rows += row + border + '\n';
    }
    console.log(rows);
};
```
 
 
* `render`，计算场景元素映射到canvas上后的像素情况，然后调用打印方法渲染出场景内容呈现于控制台上。  
 
 
```js
// 画布渲染
// scene：用于渲染的场景，noStyle：不带样式（默认带样式），noBorder：不带左右边框（默认带边框）
this.Renderer.prototype.render = function(scene, noStyle, noBorder) {
    // 先清屏
    this.clear();
    // 逐个取出场景中的元素，计算位置后取值替换画布的对应的像素点
    scene.elements.forEach((ele, i) => {
        let style = ele.style.concat();
        let z_index = ele.z_index;
        let positionY = Math.floor(ele.position.y);
        let positionX = Math.floor(ele.position.x);
        if (ele.group) {
            // 从组合里的相对坐标转换为画布上的绝对坐标
            positionY += ele.group.position.y;
            positionX += ele.group.position.x;
            // 叠加上组合的层叠优先级
            z_index += ele.group.z_index;
        }
        for (let y = positionY; y < positionY + ele.vals.length; y++) {
            if (y >= 0 && y < this.height) {
                for (let x = positionX; x < positionX + ele.vals[y - positionY].length && x < this.width; x++) {
                    if (x >= 0 && x < this.width) {
                        // 层叠优先级大的元素会覆盖优先级小的元素
                        if (z_index >= this.canvas[y][x].z_index && ele.vals[y - positionY][x - positionX] && ele.vals[y - positionY][x - positionX].toString().trim() != '') {
                            this.canvas[y][x].val = ele.vals[y - positionY][x - positionX];
                            this.canvas[y][x].style = style.concat();
                            this.canvas[y][x].z_index = z_index;
                        }
                    }
                }
            }
        }
    });
    // 打印样式或无样式判断
    noStyle ? this.printNoStyle(noBorder) : this.print(noBorder);
}
```
 
### 耍一波
 
有了上面的类库，写一个console版的弹球动画就容易多了：
 
```js
// 弹球动画
class PinBall {
    constructor(width = 30, height = 10) {
    // 创建场景
    this.scene = new ConsoleCanvas.Scene();
    // 创建渲染器
    this.renderer = new ConsoleCanvas.Renderer();
    // 设置尺寸
    this.renderer.setSize(width, height);
    // 场景元素添加
    this.elementAdd();
    // 开始动画循环
    this.loop();
    }
    elementAdd() {
        // 创建小球元素
        this.ball = new ConsoleCanvas.Element([['●']], ['background: blue', 'color: blue', 'border-radius: 50%']);
        // 在上半区域随机小球起始坐标
        this.ball.position.x = Math.floor(Math.random() * this.renderer.width);
        this.ball.position.y = Math.floor(Math.random() * this.renderer.height / 2);
        this.scene.add(this.ball);
    }
    animation() {
        let gap = 1;
        this.ball.kx = this.ball.kx ? this.ball.kx : 1;
        this.ball.ky = this.ball.ky ? this.ball.ky : 1;
        let x = this.ball.position.x + this.ball.kx * gap;
        let y = this.ball.position.y + this.ball.ky * gap;
        // 触碰边界时回弹
        if (x > this.renderer.width - this.ball.vals[0].length || x < 0) {
            this.ball.kx = -1 * this.ball.kx;
        }
        if (y > this.renderer.height - this.ball.vals.length || y < 0) {
            this.ball.ky = -1 * this.ball.ky;
        }
        this.ball.position.x = this.ball.position.x + (this.ball.kx * gap);
        this.ball.position.y = this.ball.position.y + (this.ball.ky * gap);
    }
    loop() {
        this.renderer.render(this.scene, true);
        this.animation();
        setTimeout(() => {
            this.loop();
        }, 300);
    }
}
let pinBall = new PinBall(30, 10);
```
 
带样式版本的弹球效果如下，可以发现，带样式的逐行打印，刷新频率的确捉膝见肘，时间间隔再调小点怕是会闪瞎了我的眼：
 
 ![][7]
 
那再看看去样式的版本，无样式整体单次打印起来，就不会那么闪屏了：
 
 ![][8]
 
要不，再加点键盘交互？
 
 ![][9]
 
将求解Hanoi塔问题的递归结果可视化，以下为3个圆盘时候的执行情况（4、5个的时候的也有，太长了不放了）：
 
 ![][10]
 
### 拓展
 
什么:question:你说还是有些闪？今晚的星星:sparkles:也有些闪？
 
我叹叹气，转身摸摸console的头：“请把宝典还我...”
 
于是乎，我修改了`Renderer`类的`print`函数，将渲染的内容输出到了html结构中：
 
```js
// 输出像素字符到指定dom
// target:目标dom, noStyle: 不显示样式, noBorder: 不显示左右边框
this.Renderer.prototype.print = function(target, noStyle, noBorder) {
    let row = '';
    let style = [];
    let rows = '';
    let border = noBorder ? '' : '| ';
    for (let j = 0; j < this.canvas.length; j++) {
        row = border;
        style = [];
        for (let i = 0; i < this.canvas[j].length; i++) {
            row += `<span style='${noStyle?"":this.canvas[j][i].style.join(";")}'>${this.canvas[j][i].val} `;
        }
        rows += row + border + '</br>';
    }
    if (target) {
        target.innerHTML = rows;
    }
};
```
 
修改的版本命名为 **`PixelCanvas`**  。
 
然后样式也可以轻松带了：
 
 ![][11]
 
加快速度渲染一口气上五楼也毫不费力了呢~
 
 ![][12]
 


[0]: ./img/buAjQn2.png 
[1]: ./img/NjYZBfy.png 
[2]: ./img/vaMbIvb.png 
[3]: ./img/IbAJjqQ.png 
[4]: ./img/ymEV3ie.png 
[5]: ./img/nIjqYru.png 
[6]: ./img/jqMVVnA.png 
[7]: ./img/rA7bIrI.gif 
[8]: ./img/ERV7FvV.gif 
[9]: ./img/ERnmUnZ.gif 
[10]: ./img/V3yuQ3f.gif 
[11]: ./img/MFVBJf2.gif 
[12]: ./img/MzeAJbz.gif 
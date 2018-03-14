## ES6霸气，一行代码搞定数组去重

来源：[https://xiaozhuanlan.com/topic/8914275630](https://xiaozhuanlan.com/topic/8914275630)

时间 2018-03-09 23:26:56

 
  
数组元素的去重是前端开发中常常遇到的问题，相信大家都有自己的解决方法。今天我来帮大家整理一下数组去重的各种方法。
 
方法一：循环遍历 
 
循环遍历也有两种方式，一种是内外两层的嵌套循环。外层循环数组获取元素，内存循环比较。类似于冒泡排序的方式。这种循环遍历我不太推荐给大家，我就不去完成这种循环的代码了。
 
另外一种是新建一个空数组，循环数组获取元素，然后用indexOf方法判断元素是否在新数组中。示例代码如下：
 
```js
let unique = (array) => {
  // 判断类型是不是数组且长度大于1
  if (Array.isArray(array) && array.length > 1){
    let result = [array[0]];
    for (let i = 1; i < array.length; i++) {
      if(result.indexOf(array[i]) === -1){
        result.push(array[i]);
      }
    }
    return result;
  }
}
```
 
   
测试结果如下：
 
   

![][0] 
  
 
方法二：利用对象键值去重 
 
这种方式主要利用了对象键值来检测元素是否重复。具体实现的方式如下：
 
```js
 
let unique = (array) => {
  // 判断类型是不是数组且长度大于1
  if (Array.isArray(array) && array.length > 1){
    let result = [];
    let temObj = {};
    for (let i = 0; i < array.length; i++) {
      if(!temObj[array[i]]){
        result.push(array[i]);
        temObj[array[i]] = 1;
      }
    }
    return result;
  }
}
```
 
  
数组元素的去重是前端开发中常常遇到的问题，相信大家都有自己的解决方法。今天我来帮大家整理一下数组去重的各种方法。
 
方法一：循环遍历 
 
循环遍历也有两种方式，一种是内外两层的嵌套循环。外层循环数组获取元素，内存循环比较。类似于冒泡排序的方式。这种循环遍历我不太推荐给大家，我就不去完成这种循环的代码了。
 
另外一种是新建一个空数组，循环数组获取元素，然后用indexOf方法判断元素是否在新数组中。示例代码如下：
 
```js
let unique = (array) => {
  // 判断类型是不是数组且长度大于1
  if (Array.isArray(array) && array.length > 1){
    let result = [array[0]];
    for (let i = 1; i < array.length; i++) {
      if(result.indexOf(array[i]) === -1){
        result.push(array[i]);
      }
    }
    return result;
  }
}
```
 
   
测试结果如下：
 
   

![][0] 
  
 
方法二：利用对象键值去重 
 
这种方式主要利用了对象键值来检测元素是否重复。具体实现的方式如下：
 
```js
let unique = (array) => {
  // 判断类型是不是数组且长度大于1
  if (Array.isArray(array) && array.length > 1){
    let result = [];
    let temObj = {};
    for (let i = 0; i < array.length; i++) {
      if(!temObj[array[i]]){
        result.push(array[i]);
        temObj[array[i]] = 1;
      }
    }
    return result;
  }
}
unique([1,1,1,2,2,2,3])
```
 
   
测试结果如下：
 
   

![][2] 
  
 
方法三：先排序再比较相邻元素是否有相同 
 
这种方式虽然比较方便，但是缺点是改变了原有数组的顺序。使用这种方式要考虑到实际场景。
 
简要实现代码如下：
 
```js
let unique = (array) => {
  // 判断类型是不是数组且长度大于1
  if (Array.isArray(array) && array.length > 1){
    let result = [array[0]];
    array = array.sort();
    for (let i = 1; i < array.length; i++) {
      if(array[i] !== result[result.length - 1]){
        result.push(array[i]);
      }
    }
    return result;
  }
}
unique([1,1,1,2,2,2,3])
```
 
方法四：使用ES6的Set数据结构 
 
Set是ES6新添加的数据结构，它类似于数组但是没有重复值。set是一个构造函数，可以接受一个数组作为参数。利用set的这个特性可以非常方便的对数组进行去重。示例如下：
 
```js
function unique (array) {
  return Array.from(new Set(array))
}
unique([1,1,1,2,2,2,3])
```
 
   
一行代码就结束了全部的工作，简洁高效。测试结果如下：
 
   

![][3] 
  
 
数组去重的方法还有很多，比如判断元素的索引和该元素在数组中首次出现的索引是否一致，等等。JavaScript语言的进步给前端开发工作带来了很多便利，感谢ES6。
 
ES6的Map和Set这两种数据结构很值得好好研究一下，稍后我会有一篇文章专门介绍Map和Set。
 
感谢每一位订阅专栏的朋友，我保证持续产出高质量的文章，不辜负你们的期望~
 
 
 
  
数组元素的去重是前端开发中常常遇到的问题，相信大家都有自己的解决方法。今天我来帮大家整理一下数组去重的各种方法。
 
方法一：循环遍历 
 
循环遍历也有两种方式，一种是内外两层的嵌套循环。外层循环数组获取元素，内存循环比较。类似于冒泡排序的方式。这种循环遍历我不太推荐给大家，我就不去完成这种循环的代码了。
 
另外一种是新建一个空数组，循环数组获取元素，然后用indexOf方法判断元素是否在新数组中。示例代码如下：
 
```js
let unique = (array) => {
  // 判断类型是不是数组且长度大于1
  if (Array.isArray(array) && array.length > 1){
    let result = [array[0]];
    for (let i = 1; i < array.length; i++) {
      if(result.indexOf(array[i]) === -1){
        result.push(array[i]);
      }
    }
    return result;
  }
}
```
 
   
测试结果如下：
 
   

![][0] 
  
 
方法二：利用对象键值去重 
 
这种方式主要利用了对象键值来检测元素是否重复。具体实现的方式如下：
 
```js
let unique = (array) => {
  // 判断类型是不是数组且长度大于1
  if (Array.isArray(array) && array.length > 1){
    let result = [];
    let temObj = {};
    for (let i = 0; i < array.length; i++) {
      if(!temObj[array[i]]){
        result.push(array[i]);
        temObj[array[i]] = 1;
      }
    }
    return result;
  }
}
unique([1,1,1,2,2,2,3])
```
 
   
测试结果如下：
 
   

![][2] 
  
 
方法三：先排序再比较相邻元素是否有相同 
 
这种方式虽然比较方便，但是缺点是改变了原有数组的顺序。使用这种方式要考虑到实际场景。
 
简要实现代码如下：
 
```js
let unique = (array) => {
  // 判断类型是不是数组且长度大于1
  if (Array.isArray(array) && array.length > 1){
    let result = [array[0]];
    array = array.sort();
    for (let i = 1; i < array.length; i++) {
      if(array[i] !== result[result.length - 1]){
        result.push(array[i]);
      }
    }
    return result;
  }
}
unique([1,1,1,2,2,2,3])
```
 
方法四：使用ES6的Set数据结构 
 
Set是ES6新添加的数据结构，它类似于数组但是没有重复值。set是一个构造函数，可以接受一个数组作为参数。利用set的这个特性可以非常方便的对数组进行去重。示例如下：
 
```js
function unique (array) {
  return Array.from(new Set(array))
}
unique([1,1,1,2,2,2,3])
```
 
   
一行代码就结束了全部的工作，简洁高效。测试结果如下：
 
   

![][3] 
  
 
数组去重的方法还有很多，比如判断元素的索引和该元素在数组中首次出现的索引是否一致，等等。JavaScript语言的进步给前端开发工作带来了很多便利，感谢ES6。
 
ES6的Map和Set这两种数据结构很值得好好研究一下，稍后我会有一篇文章专门介绍Map和Set。
 
感谢每一位订阅专栏的朋友，我保证持续产出高质量的文章，不辜负你们的期望~
 
 
 


[0]: https://img1.tuicool.com/MbARziB.png 
[1]: https://img1.tuicool.com/MbARziB.png 
[2]: https://img2.tuicool.com/vQnyaiN.png 
[3]: https://img2.tuicool.com/rMZZRv2.png 
[4]: https://img1.tuicool.com/MbARziB.png 
[5]: https://img2.tuicool.com/vQnyaiN.png 
[6]: https://img2.tuicool.com/rMZZRv2.png 
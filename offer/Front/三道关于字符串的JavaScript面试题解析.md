## 三道关于字符串的JavaScript面试题解析

来源：[https://segmentfault.com/a/1190000013958857](https://segmentfault.com/a/1190000013958857)


分享几道js面试题，自己感觉还是挺重要的，当看到题目的时候希望大家先花几秒钟考虑一下，
然后在看答案。如果有比较好的解法，欢迎大家留言指正，谢谢大家！
## 第一题


题目： **` 写一个字符串转换成驼峰的方法？ `** 


例如：`border-bottom-color`->`borderBottomColor`
### 方法一

```js
let str = "border-bottom-color";

function change(val){
     // 用‘-’切分成一个数组
    let arr = val.split('-');  
   
   // 首字符大写
   for(let i = 1; i < arr.length; i++){
     arr[i] = arr[i].charAt(0).toUpperCase() + arr[i].substring(1);
   } 
   
   // 将字符串拼接后返回
    return arr.join('');
}
```

 **` 点评：`**  这种方法虽然可以实现，但还是太LOW了,一点都不简洁。
### 方法二

```js
let str = "border-bottom-color";

function change(val){
     return str.split('-').reduce((prev,cur,index) => {
         return prev + cur.charAt(0).toUpperCase()+cur.substring(1);
    });
}
```

 **` 点评：`**  这种方法使用了数组的 **` reduce() `**  方法，稍微简洁了一点点，但还是不够好。
### 方法三

```js
let str = "border-bottom-color";

function change(val){
     // 定义正则表达式
     let re = /-(\w)/g;
      
     return str.replace(re,($0,$1) => {
        return $1.toUpperCase();
    });
}
```

 **` 点评：`**  第三种方法使用正则表达式，效果还是不错的。

 备注： 
replace()方法的第二个参数可以是一个函数：
参数一：$0   正则的整体`-b  -c`
参数二：$1   正则当中子项的第一个（用括号括起来的就叫做子项）`b  c`
## 第二题


题目： **` 查找字符串中出现最多的字符和个数？ `** 


例如：`abbcccddddd`->`字符最多的是d，出现了5次`
### 方法一

```js
let str = "abbcccddddd";

let obj = {};

// 生成obj对象
for(let i = 0;i < str.length; i++){
    let cur = str[i]; // 当前字符
    
    if(!obj[ cur ]){
        obj[ cur ] = [];            
    }

    obj[ cur ].push(cur);
} 

// 统计次数
let num = 0;
let char = '';
for(item in obj){
    if(num < obj[item].length){
        num = obj[item].length;
        char = obj[item][0];
    }
}
console.log(`字符最多的是${char}，出现了${num}次`);

```

 **` 点评：`**  虽然能实现，但是太麻烦。
### 方法二

```js
let str = "abbcccddddd";

// 生成obj
let obj = str.split('').reduce((prev,cur) => {
    prev[cur] ? prev[cur]++ : prev[cur] = 1;
    return prev;
},{});

// {a: 1, b: 2, c: 3, d: 5}


let num = 0;
let char = '';

// 统计次数
for(item in obj){
    if(num < obj[item]){
        num = obj[item];
        char = item;
    }
}
console.log(`字符最多的是${char}，出现了${num}次`);
```

 **` 点评：`**  稍微好一点。仍然是使用 **` reduce() `**  这个方法。
哈哈，万能的 **` reduce `**  。
### 方法三

```js
let str = "abcabcabcbbccccc";
let num = 0;
let char = '';

 // 使其按照一定的次序排列
str = str.split('').sort().join('');
// "aaabbbbbcccccccc"

// 定义正则表达式
let re = /(\w)\1+/g;
str.replace(re,($0,$1) => {
    if(num < $0.length){
        num = $0.length;
        char = $1;        
    }
});
console.log(`字符最多的是${char}，出现了${num}次`);

```

 **` 点评：`**  ：使用正则表达式总是那么简单。
## 问题三


题目： **` 如何给字符串加千分符？ `** 


例如：`42342342342`->`42,342,342,342`
### 方法一

```js
let str = "12312345678988";

// 转换的方法
function change(str){
    // 转化为数组
    var arr = str.split('');
    var result = [];

    while(arr.length > 3){
      result.push(arr.splice(-3,3).join(''));
    }
    result.push(arr.join(''));

    // 最终的结果
    return result.reverse().join(',');
}

```

 **` 点评：`**  ：将字符串转化为数组，然后对其切分重组。
### 方法二

```js
let str = "12312345678988";

function change(str){
    // 仅仅对位置进行匹配
    let re = /(?=(?!\b)(\d{3})+$)/g; 
   return str.replace(re,','); 
}
```

 **` 点评：`**  ：这个正则表达式就有点屌了。


(?=) : 前向声明
(?!) : 反前向声明

 **` 举个小栗子 `** 

```js
    var str = 'abacad';
   
   var re = /a(?=b)/g;
   str.replace(re,'*');  // 结果：'*bacad'
   // 将a后边为‘b’的a替换为‘*’号
   
   var re = /a(?!b)/g;
   str.replace(re,'*');  // 结果：'ab*c*d'
   // 将a后边不为‘b’的a替换为‘*’号
```

// 翻转字符串

// 先把字符串转化成数组，再借助数组的reverse方法翻转数组顺序，最后把数组转化成字符串。

// 你的结果必须得是一个字符串

function reverseString(str) {
            var arr = [];
            arr = str.split("");
            arr.reverse();
            str = arr.join("");
            return str;

        }
        console.log(reverseString("hello"));
// 知识点：

// split() 把字符串分割为字符串数组。
// reverse() 反转数组的元素顺序。
// toString() 把数组转换为字符串，并返回结果。
// 阶乘算法挑战
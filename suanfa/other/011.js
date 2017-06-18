
// 回文算法挑战

// 如果一个字符串忽略标点符号、大小写和空格，正着读和反着读一模一样，那么这个字符串就是palindrome(回文)。

// 你需要去掉字符串多余的标点符号和空格

// 然后把字符串转化成小写来验证此字符串是否为回文。

function palindrome(str) {
            //传入字符串处理
            var newstr = str.replace(/[^0-9a-z]/gi, "");
            newstr = newstr.toLowerCase();
            // // 方法一
            // //转换成数组并进行反向排序
            // var arr = newstr.split("");
            // arr.reverse();
            // //把排序后的转换为字符串进行全等判断
            // arr = arr.join("");
            // if (newstr === arr) {
            //     return true;
            // } else {
            //     return false;
            // }
            //方法二 从字符串头部和尾部，逐次向中间检测
            for (var i = 0, j = newstr.length - 1; i < j; i++, j--) {
                //这里注意下，for循环内部只判断不符合要求的，
                //如果循环后都没返回false就在for外部返回true，不要在内部直接返回true或提前出来
                if (newstr.charAt(i) !== newstr.charAt(j)) {
                    return false;
                }
            }

            return true;
        }


        console.log(palindrome("assa"));
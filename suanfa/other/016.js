// 重复操作算法挑战

// 重复一个指定的字符串 num次，如果num是一个负数则返回一个空字符串。

function repeat(str, num) {
            var temp = str,
                i = 0;
            if (num < 0) {
                return "";
            } else {
                for (; i < num - 1; i++) {
                    str = str.concat(temp);
                }
            }
            return str;
        }
        console.log(repeat("abc", 3));
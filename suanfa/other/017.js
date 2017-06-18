// 字符串截取算法挑战

// 如果字符串的长度比指定的参数num长，则把多余的部分用...来表示。切记，插入到字符串尾部的三个点号也会计入字符串的长度。但是，如果指定的参数num小于或等于3，则添加的三个点号不会计入字符串的长度。

function truncate(str, num) {
            var more = "...";
            // Clear out that junk in your trunk
            if (str.length <= num) {
                if (num <= 3) {
                    var newStr = str.substr(0, num);
                    str = newStr.concat(more);
                } else
                    return str;
            } else {
                if (num <= 3) {
                    var newStr = str.substr(0, num);
                    str = newStr.concat(more);
                } else {
                    var newStr = str.substr(0, num - 3);
                    str = newStr.concat(more);
                }

            }
            return str;
        }
        console.log(truncate("Absolutely Longer", 2));
// 位移密码算法挑战

// 下面我们来介绍风靡全球的凯撒密码Caesar cipher，又叫移位密码。

// 移位密码也就是密码中的字母会按照指定的数量来做移位。

// 一个常见的案例就是ROT13密码，字母会移位13个位置。由'A' ↔ 'N', 'B' ↔'O'，以此类推。

// 写一个ROT13函数，实现输入加密字符串，输出解密字符串。

// 所有的字母都是大写，不要转化任何非字母形式的字符(例如：空格，标点符号)，遇到这些特殊字符，跳过它们。

function rot13(str) { // LBH QVQ VG!
            var arr = [];
            for (var index = 0; index < str.length; index++) {
                arr[index] = str.charCodeAt(index);
                if (arr[index] >= 65 && arr[index] <= 77) {
                    arr[index] += 13;
                }
                //字母为26个当在字母末13个位，右移13位不是字母，应该进行左移、保证在字母26位里。
                else if (arr[index] > 77 && arr[index] < 91)
                    arr[index] -= 13;
            }
            console.log(arr);
            for (var i = 0; i < arr.length; i++) {
                arr[i] = String.fromCharCode(arr[i]);
            }
            return arr.join("");
        }
        // Change the inputs below to test
        console.log(rot13("SERR PBQR PNZC"));
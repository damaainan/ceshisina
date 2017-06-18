// 数组查询算法挑战

// 蛤蟆可以吃队友，也可以吃对手。

// 如果数组第一个字符串元素包含了第二个字符串元素的所有字符，函数返回true。

// 举例，["hello", "Hello"]应该返回true，因为在忽略大小写的情况下，第二个字符串的所有字符都可以在第一个字符串找到。["hello", "hey"]应该返回false，因为字符串"hello"并不包含字符"y"。["Alien", "line"]应该返回true，因为"line"中所有字符都可以在"Alien"找到。

function mutation(arr) {
            var count = 0,
                newArr, i = 0;
            newArr = arr.join(" ");
            newArr = newArr.toLowerCase().split(" ");
            newArr[0] = newArr[0].split("");
            newArr[1] = newArr[1].split("");
            console.log(newArr);
            for (; i < newArr[1].length; i++) {
                var index = 0;
                for (; index < newArr[0].length; index++) {
                    if (newArr[1][i] == newArr[0][index]) {
                        count++;
                        index = newArr[0].length;
                    }
                }
                console.log(count);
            }
            return count === arr[1].length;
        }
        console.log(mutation(["floor", "for"]));
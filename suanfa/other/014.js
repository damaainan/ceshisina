// 寻找数组中的最大值算法挑战

// 右边大数组中包含了4个小数组，分别找到每个小数组中的最大值，然后把它们串联起来，形成一个新数组。

function largestOfFour(arr) {
            var i = 0,
                j = 0,
                maxArr = [];
            for (; i < arr.length; i++) {
                var max = 0; //内部循环后，比较值要清空一次。
                for (; j < arr[i].length; j++) {
                    if (arr[i][j] > max) {
                        max = arr[i][j];
                    }
                }
                maxArr.push(max);
            }
            console.log(maxArr);
            return maxArr;
        }
        largestOfFour([
            [13, 27, 18, 26],
            [4, 5, 1, 3],
            [32, 35, 37, 39],
            [1000, 1001, 857, 1]
        ]);
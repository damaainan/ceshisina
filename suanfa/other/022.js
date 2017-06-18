// 去除数组中任意多个值算法挑战

// 实现一个摧毁(destroyer)函数，第一个参数是待摧毁的数组，其余的参数是待摧毁的值。

function destroyer(arr, del1, del2, del3) {
            var newDel = [del1, del2, del3],
                index = 0;
            for (; index < newDel.length; index++) {
                var i = 0;
                for (; i < arr.length; i++) {
                    if (arr[i] == newDel[index]) {
                        arr.splice(i, 1);
                        i--;
                    }
                }
            }
            return arr;
        }
        console.log(destroyer([1, 2, 3, 1, 2, 3], 2, 3));
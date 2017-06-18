// 删除数组中特定值算法挑战

// 删除数组中的所有假值。

// 在JavaScript中，假值有false、null、0、""、undefined 和 NaN。

function bouncer(arr) {
            var index = 0;
            // Don't show a false ID to this bouncer.
            for (; index < arr.length; index++) {
                if (!arr[index]) {
                    arr.splice(index, 1);
                    //保证删除数组元素后，索引不跳过下一个
                    index--; 
                }
            }
            return arr;
        }
        console.log(bouncer([7, "ate", "", false, 9]));
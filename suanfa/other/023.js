// 数组排序并插入值算法挑战

// 先给数组排序，然后找到指定的值在数组的位置，最后返回位置对应的索引。

// 举例：where([1,2,3,4], 1.5) 应该返回 1。因为1.5插入到数组[1,2,3,4]后变成[1,1.5,2,3,4]，而1.5对应的索引值就是1。

// 同理，where([20,3,5], 19) 应该返回 2。因为数组会先排序为 [3,5,20]，19插入到数组[3,5,20]后变成[3,5,19,20]，而19对应的索引值就是2。

function where(arr, num) {
            arr.push(num);
            var i = 0,
                index = 0;
            for (; i < arr.length - 1; i++) {
                var j = i + 1;
                for (; j < arr.length; j++) {
                    if (arr[i] > arr[j]) {
                        var temp = arr[i];
                        arr[i] = arr[j];
                        arr[j] = temp;
                    }
                }
            }
            for (; index < arr.length; index++) {
                if (arr[index] == num) {
                    return index;
                }
            }
        }
        console.log(where([2, 20, 10], 19));
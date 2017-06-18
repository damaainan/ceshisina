// 数组截断算法挑战

// 返回一个数组被截断n个元素后还剩余的元素，截断从索引0开始。

function slasher(arr, howMany) {
            if (arr.length > howMany) {
                arr = arr.slice(-(arr.length - howMany));
                return arr;
            } else return [];

        }
        console.log(slasher([1, 2, 3], 4));
// 数组分割算法挑战

// 猴子吃香蕉可是掰成好几段来吃哦！

// 把一个数组arr按照指定的数组大小size分割成若干个数组块。

// 例如:chunk([1,2,3,4],2)=[[1,2],[3,4]];

chunk([1,2,3,4,5],2)=[[1,2],[3,4],[5]];

function chunk(arr, size) {
            // Break it up.
            var newArr = [];
            var index = 0,
                end = size,
                i = 0;
            var count = arr.length / size;
            console.log(count);
            for (; i < count; index += size) {
                newArr[i] = arr.slice(index, end);
                end = end + end;
                i++;
            }
            return newArr;
        }
         console.log(chunk([0, 1, 2, 3, 4, 5, 6], 3));
// 寻找最长的单词算法挑战

// 找到提供的句子中最长的单词，并计算它的长度。

// 函数的返回值应该是一个数字。

function findLongestWord(str) {
            var arr = str.split(" "),
                i = 0,
                lengthNum,
                maxLengthNum = 0;
            // var index = i + 1;
            //方法一：复杂化方法：冒泡排序解法（直接第一个想到就是这个，就顺着来做了，发现复杂化，
            //只需要把数组长度和最大值取出来做比较就行了）
            //     for (; i < arr.length - 1; i++) {
            //         for (; index < arr.length; index++) {
            //             if (arr[i].length < arr[index].length) {
            //                 var temp = arr[i];
            //                 arr[i] = arr[index];
            //                 arr[index] = temp;
            //             }
            //         }
            //     }
            //     return arr[0].length;

            //方法二：取出数组长度值与历史最大值比较，第一个默认为起始最大值
            for (; i < arr.length; i++) {
                lengthNum = arr[i].length;
                if (lengthNum > maxLengthNum) {
                    maxLengthNum = lengthNum;
                }
            }
            return maxLengthNum;
        }
        findLongestWord("The quick brown fox jumped over the lazy dog");
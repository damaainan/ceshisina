
// 设置首字母大写算法挑战

// 确保字符串的每个单词首字母都大写，其余部分小写。

//方法一：复杂化，没有使用charAt+replace、map() + slice()

        // function titleCase(str) {
        //     var arr = str.toLowerCase().split(" ");
        //     var newArr = new Array();
        //     var i = 0;
        //     for (; i < arr.length; i++) {
        //         newArr[i] = arr[i].split("");
        //         newArr[i][0] = newArr[i][0].toUpperCase();
        //         newArr[i] = newArr[i].join("");
        //     }
        //     arr = newArr.join(" ");
        //     console.log(arr);
        //     return arr;
        // }
        //方法二：简单化用replace替换首字母大写
        // function titleCase(str) {
        //     var arr = str.toLowerCase().split(" ");
        //     var i = 0;
        //     for (; i < arr.length; i++) {
        //         var Up = arr[i].charAt(0).toUpperCase();
        //         arr[i] = arr[i].replace(arr[i].charAt(0), Up);
        //     }
        //     return arr.join(" ");
        // }
        //方法三:map() + replace()
        function titleCase(str) {
            var arr = str.toLowerCase().split(" ").map(function(word) {
                return (word.charAt(0).toUpperCase() +
                    word.slice(1));
            }).join(" ");
            console.log(arr);
            return arr;
        }
        titleCase("I'm a little tea pot");
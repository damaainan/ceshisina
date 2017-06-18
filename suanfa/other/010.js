

// 计算一个整数的阶乘

// 如果用字母n来代表一个整数，阶乘代表着所有小于或等于n的整数的乘积。

// //for循环
        // function factorialize(num) {
        //     var i = 1
        //     var sum = 1;
        //     for (; i < num + 1; i++) {
        //         sum = sum * i;
        //     }
        //     return sum;
        // }
        // 递归实现  
        function factorialize(num) {
            if (num == 1 || num == 0)
                return 1;
            else {
                return factorialize(num - 1) * num;
            }

        }
        console.log(factorialize(0));
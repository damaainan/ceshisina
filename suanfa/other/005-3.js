// ## 位运算

// > 输入一个整数，求该整数的二进制表达中有多少个1。

// 1.乘除模拟位运算：

    (function(aInt){
        var intStr = [], count = 1;
        while(aInt > 1){
            if(aInt % 2 == 0){
                intStr.unshift(0);
            }else{
                intStr.unshift(1);
                count++;
            }
            aInt = Math.floor(aInt/2);
        }
        intStr.unshift(1);
    
        console.log('Binary String : ' + intStr.join(','));
        console.log('The count is  : ' + count);
    }(28));
// 2.真正位运算：

    (function(aInt){
        var intStr = [], count = 0;
        while(aInt > 0){
            var cur = aInt & 1;
            intStr.unshift(cur);
            if(cur == 1){
                count++;
            }
            aInt = aInt >> 1;
        }
    
        console.log('Binary String : ' + intStr.join(','));
        console.log('The count is  : ' + count);
    }(10));
// > 输入一个整数n，求从1到n这n个整数的十进制表示中1出现的次数。


    (function(aInt){
        var count = 0;
        for(var i = 1; i <= aInt; i++){
            var a = i;
            while(a >= 1){
                if(a % 10 == 1){
                    count++;
                }
                a= Math.floor(a / 10);
            }
        }
        console.log('The count is  : ' + count);
    }(13));

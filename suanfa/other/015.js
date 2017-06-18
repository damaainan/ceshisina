// 确认末尾字符算法挑战

// 检查一个字符串(str)是否以指定的字符串(target)结尾。

//方法一：使用lastIndexOf从最后开始查找，并返回对应的起始位置到其索引，再做比较，indexOf也可实现返回索引
        // function confirmEnding(str, target) {
        //     var index = str.lastIndexOf(target);
        //     return index == str.length - target.length;
        // }
        //方法二：使用substr抽取出字符，用负数来实现从末尾抽对应的查询字符的长度
        //如果查找的字符实在最后出现，则抽取出来的应该和需要查找的一样，同理slice也可以实现提取
        function confirmEnding(str, target) {
            var endingPart = str.substr(-(target.length));
            return target === endingPart;
        }
        console.log(confirmEnding("Walking on water and developing software from a are easy if both are frozen specification", "specification"));
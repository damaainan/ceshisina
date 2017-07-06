/*

> 对于 0~99 的数字，基数排序将数据集扫描两次。第一次按个位上的数字进行排序，第二 次按十位上的数字进行排序。每个数字根据对应位上的数值被分在不同的盒子里。  
> 用以上算法对数据进行排序。

**思路：**

我们需要九个队列，每个对应一个数字。将所有 队列保存在一个数组中，使用取余和除法操作决定个位和十位。算法的剩余部分将数字加 入相应的队列，根据个位数值对其重新排序，然后再根据十位上的数值进行排序，结果即 为排好序的数字。
 */

var Queue = require("./Queue.js");


    // nums 要排序的数字数组。 queues 0-9队列数组 n 数字的个数 digit 按个位还是十位排序
    function distribute(nums, queues, n, digit) {
        for (var i = 0; i < n - 1; i++) {
            if (digit == 1) {
                queues[nums[i] % 10].enqueue(nums[i]);
            } else {
                queues[Math.floor(nums[i] / 10)].enqueue(nums[i])
            }
        }
    }
    
    // 从队列中收集数字
    function collect(queues, nums) {
        var i = 0;
        for (var digit = 0; digit < 10; digit++) {
            while (!queues[digit].empty()) {
                nums[i++] = queues[digit].dequeue();
            }
        }
    }
    
    // 显示数组
    
    function dispArray(arr) {
        console.log(arr);
    }
    
    var queues = [];
    for (var i = 0; i < 10; i++) {
        queues[i] = new Queue();
    }
    
    var nums = [];
    //  随机生成10个数字
    for (var i = 0; i < 10; i++) {
        nums[i] = Math.floor(Math.random() * 101);
    }
    
    console.log("Before radix sort: ");
    dispArray(nums);
    distribute(nums, queues, 10, 1);
    collect(queues, nums);
    distribute(nums, queues, 10, 10);
    collect(queues, nums);
    console.log("\n\nAfter radix sort: ");
    dispArray(nums);
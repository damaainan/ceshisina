function getNum() {
    return parseInt(Math.random() * 100 + 1);
}

function getArr(size) {
    var arr = [];
    for (var i = 0; i < size; i++) {
        arr.push(getNum());
    }
    return arr;
}
var weight = getArr(10000);
var value = getArr(10000);
var V = 10000;


function aaa(wight, value, all) {
    var startTime = new Date().getTime();
    var returnList = [];
    for (var i = 0; i < wight.length; i++) {
        returnList[i] = [];
        for (var j = 0; j < all; j++) {
            var nowW = j + 1; //此时背包重量
            var nowW_ = wight[i]; //此时物品重量
            var nowV = value[i]; //此时的价值
            var lastW = nowW - nowW_; //此时背包重量减去此时要添加的物品后的重量

            var fV = lastW >= 0 ? nowV : 0;
            fV = fV + (i > 0 && returnList[i - 1][lastW - 1] ? returnList[i - 1][lastW - 1] : 0);
            var nV = i > 0 && returnList[i - 1][j] ? returnList[i - 1][j] : 0;
            returnList[i][j] = Math.max(fV, nV);
        }
    }
    var endTime = new Date().getTime();
    return returnList[wight.length - 1][all - 1] + "耗时：" + (endTime - startTime) + "ms";
}
console.log(aaa(weight, value, V));


function bbb(wight, value, all) {
    var startTime = new Date().getTime();
    var returnList = [];
    var returnList_prev = [];
    var flag = true;
    for (var i = 0; i < wight.length; i++) {
        for (var j = 0; j < all; j++) {
            var nowW = j + 1; //此时背包重量
            var nowW_ = wight[i]; //此时物品重量
            var nowV = value[i]; //此时的价值
            var lastW = nowW - nowW_; //此时背包重量减去此时要添加的物品后的重量
            //考虑过两个数组相互赋值，但是数组是引用类型，两个会干扰，如果深拷贝那就更影响速度，所以想到这种两个数组相互使用相互覆盖的方式来避免构建庞大的二维数组
            if (flag) {
                var fV = lastW >= 0 ? nowV : 0;
                fV = fV + (i > 0 && returnList_prev[lastW - 1] ? returnList_prev[lastW - 1] : 0);
                var nV = i > 0 && returnList_prev[j] ? returnList_prev[j] : 0;
                returnList[j] = Math.max(fV, nV);
            } else {
                var fV = lastW >= 0 ? nowV : 0;
                fV = fV + (i > 0 && returnList[lastW - 1] ? returnList[lastW - 1] : 0);
                var nV = i > 0 && returnList[j] ? returnList[j] : 0;
                returnList_prev[j] = Math.max(fV, nV);
            }

        }
        flag = !flag;
    }
    var endTime = new Date().getTime();
    return returnList[all - 1] + "耗时：" + (endTime - startTime) + "ms";
}
console.log(bbb(weight, value, V));
function max(a, b) {
    return (a > b) ? a : b;
}
function dKnapsack(capacity, size, value, n) {
    var K = [];
    for (var i = 0; i <= capacity + 1; i++) {
        K[i] = [];
    }
    for(var i = 0; i <= n; i++) {
        var str="";
        for (var w = 0; w <= capacity; w++) {
            if (i == 0 || w == 0) {
                K[i][w] = 0;
            }else if (size[i - 1] <= w) {
                K[i][w] = max(value[i - 1] + K[i - 1][w - size[i - 1]],
                    K[i - 1][w]);
            }else {
                K[i][w] = K[i - 1][w];
            }
            str+=K[i][w] + " ";
        }
        console.log(str);
    }
    return K[n][capacity];
}
var value = [4, 5, 10, 11, 13];
var size = [3, 4, 7, 8, 9];
var capacity = 16;
var n = 5;
console.log(dKnapsack(capacity, size, value, n));
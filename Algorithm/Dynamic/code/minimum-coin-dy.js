/**
 * 基于动态规划的算法
 * @param {Array} coins [description]
 */
module.exports = function MinCoinChange(coins) {
    var coins = coins || [];
    var cache = {};

    this.makeChange = (amount) => {
        if (amount < 1) {
            return [];
        }

        if (cache[amount]) {
            return cache[amount];
        }

        var min = [];
        var newMin;
        var newAmount;

        for (var i = 0; i < coins.length; i++) {
            var coin = coins[i];
            newAmount = amount - coin;

            if (newAmount >= 0) {
                newMin = this.makeChange(newAmount);
            }

            if (newAmount >= 0 &&
                (newMin.length < min.length - 1 || !min.length) &&
                (newMin.length || !newAmount)
            ) {
                min = [coin].concat(newMin);
            }
        }

        return (cache[amount] = min);
    };

    this.getCache = function() {
        return cache;
    };
}
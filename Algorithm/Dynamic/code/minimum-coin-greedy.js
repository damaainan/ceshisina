/**
 * 基于贪心算法思路的最少找零硬币解
 * @param  {Array} coins [description]
 */
module.exports = function MinCoinChange(coins) {
    var coins = coins || [];

    this.makeChange = (amout) => {
        var change = [];
        var total = 0;
        for (var i = coins.length - 1; i >= 0; i--) {
            var coin = coins[i];
            while (total + coin <= amout) {
                change.push(coin);
                total += coin;
            }
        }

        return change;
    };
}
<?php
/**
 *Best Time to Buy and Sell Stock III 买股票的最佳时间之三


Say you have an array for which the ith element is the price of a given stock on day i.

Design an algorithm to find the maximum profit. You may complete at most two transactions.

Note:
You may not engage in multiple transactions at the same time (ie, you must sell the stock before you buy again).



这道是买股票的最佳时间系列问题中最难最复杂的一道，前面两道Best Time to Buy and Sell Stock 买卖股票的最佳时间和Best Time to Buy and Sell Stock II 买股票的最佳时间之二的思路都非常的简洁明了，算法也很简单。而这道是要求最多交易两次，找到最大利润，还是需要用动态规划Dynamic Programming来解，而这里我们需要两个递推公式来分别更新两个变量local和global，参见网友Code Ganker的博客，我们其实可以求至少k次交易的最大利润，找到通解后可以设定 k = 2，即为本题的解答。我们定义local[i][j]为在到达第i天时最多可进行j次交易并且最后一次交易在最后一天卖出的最大利润，此为局部最优。然后我们定义global[i][j]为在到达第i天时最多可进行j次交易的最大利润，此为全局最优。它们的递推式为：

local[i][j]=max(global[i-1][j-1]+max(diff,0),local[i-1][j]+diff)

global[i][j]=max(local[i][j],global[i-1][j])，

其中局部最优值是比较前一天并少交易一次的全局最优加上大于0的差值，和前一天的局部最优加上差值中取较大值，而全局最优比较局部最优和前一天的全局最优。
 */
## 动态规划法（四）0-1背包问题（0-1 Knapsack Problem）

来源：[https://www.cnblogs.com/jclian91/p/9132730.html](https://www.cnblogs.com/jclian91/p/9132730.html)

2018-06-04 12:29

  继续讲故事~~

  转眼我们的主人公丁丁就要离开自己的家乡，去大城市见世面了。这天晚上，妈妈正在耐心地帮丁丁收拾行李。家里有个最大能承受20kg的袋子，可是妈妈却有很多东西想装袋子里，已知行李的编号、重要、价值如下表所示：

![][0]

妈妈想要在袋子所能承受的范围内，使得行李的价值最大，并且每件行李只能选择带或者不带。这下妈妈可犯难了，虽然收拾行李不在话下，但是想要解决这个问题，那就不是她的专长了。于是，她把这件事告诉了丁丁。

  丁丁听了，想起了几天前和小连一起解决的[子集和问题(subset sum problem)][100],他觉得这个背包问题（其实是0-1背包问题）和子集和问题有很多类似之处，应该也是用动态规划法来解决。有个这个想法，他就立马拿出稿纸开始推演起来：

  假设背包总的承受重要为W, 总的行李j件数为n，行李的重量列表为w, 价值的列表为v。 假设用dp(i,j)表示用前i个物体，总重要不超过j千克，且价值最大的情况。则有以下情况：


* 若第i件行李的重要w[i] > j, 则不考虑第i件行李，即dp(i,j)=dp(i-1,j).
* 若第i件行李的重要w[i] <= j, 则有两种情况： 一种不放入第i件行李，则dp(i,j)=dp(i-1,j)； 另一种情况，放入第i件行李，则dp(i,j)=d(i-1, j-w[i])+v[i]。 应该选取两者之间的最大值，即dp(i,j)=max{dp(i-1,j), dp(i-1, j-w[i])+v[i]}。


该问题的子结构有了。那么，接下来，只需要考虑初始值即可：


对于任意的i,j, 有dp(i,0)=dp(0,j)=0.


这样他就完整地描述了该背包问题的算法。于是，他在自己的电脑上迅速地写下了如下的Python代码：

```python
# dynamic programming in 0-1 Knapsack Problem
import numpy as np

# n: number of objects
# W: total weight
# w: list of weight of each object
# v: list of value of each object
# return: maximum value of 0-1 Knapsack Problem
def Knapsack_01(n, W, w, v):
    # create (n+1)*(W+1) table initialized with all 0
    dp = np.array([[0]*(W+1)]*(n+1))

    # using DP to solve 0-1 Knapsack Problem
    for i in range(1, n+1):
        for j in range(1, W+1):
            # if ith item's weight is bigger than j, then do nothing
            if w[i-1] > j:
                dp[i,j] = dp[i-1, j]
            else: # compare the two situations: putt ith item in or not
                dp[i,j] = max(dp[i-1, j], v[i-1] + dp[i-1, j-w[i-1]])

    return dp[n][W] # maximum value of 0-1 Knapsack Problem

# test
W = 20
w = (1, 2, 5, 6, 7, 9)
v = (1, 6, 18, 22, 28, 36)
n = len(w)

t = Knapsack_01(n, W, w, v)
print('max value : %s'%t)

```

输出结果如下：


max value : 76


  最大的价值是得到了，可是应该选取哪几件行李的？丁丁想到了子集和问题，选取行李即相当于选取价值集合的一个子集，使得它们的和为最大价值。于是，代码就变成了：

```python
# dynamic programming in 0-1 Knapsack Problem
import numpy as np

# n: number of objects
# W: total weight
# w: list of weight of each object
# v: list of value of each object
# return: maximum value of 0-1 Knapsack Problem
def Knapsack_01(n, W, w, v):
    # create (n+1)*(W+1) table initialized with all 0
    dp = np.array([[0]*(W+1)]*(n+1))

    # using DP to solve 0-1 Knapsack Problem
    for i in range(1, n+1):
        for j in range(1, W+1):
            # if ith item's weight is bigger than j, then do nothing
            if w[i-1] > j:
                dp[i,j] = dp[i-1, j]
            else: # compare the two situations: putt ith item in or not
                dp[i,j] = max(dp[i-1, j], v[i-1] + dp[i-1, j-w[i-1]])

    return dp[n][W] # maximum value of 0-1 Knapsack Problem

# using DP to solve subset sum problem
def isSubsetSum(v, n, max_value):
    # The value of subset[i, j] will be
    # true if there is a subset of
    # set[0..j-1] with sum equal to i
    subset = np.array([[True]*(max_value+1)]*(n+1))

    # If sum is 0, then answer is true
    for i in range(0, n+1):
        subset[i, 0] = True

    # If sum is not 0 and set is empty,
    # then answer is false
    for i in range(1, max_value+1):
        subset[0, i] = False

    # Fill the subset table in bottom-up manner
    for i in range(1, n+1):
        for j in range(1, max_value+1):
            if j < v[i-1]:
                subset[i, j] = subset[i-1, j]
            else:
                subset[i, j] = subset[i-1, j] or subset[i-1, j-v[i-1]]

    if subset[n, max_value]:
        sol = []
        # using backtracing to find the solution
        i = n
        while i >= 0:
            if subset[i, max_value] and not subset[i-1, max_value]:
                sol.append(v[i-1])
                max_value -= v[i-1]
            if max_value == 0:
                break
            i -= 1
        return sol
    else:
        return []

def main():
    # test
    W = 20
    w = (1, 2, 5, 6, 7, 9)
    v = (1, 6, 18, 22, 28, 36)
    n = len(w)

    max_value = Knapsack_01(n, W, w, v)
    sol = isSubsetSum(v, n, max_value)

    items = [v.index(i) for i in sol]

    print('Max value : %s'%max_value)
    print('Chosen items: %s'%items)

main()
```

输出结果如下：


Max value : 76

Chosen items: [5, 3, 2]


因此，在妈妈的这个问题中，能达到的最大价值为76, 应该选取第2，3，5件行李。

  解决该问题后，丁丁立马把结果和解答的过程告诉了妈妈。妈妈虽然没有听懂，但是确信这就是正确答案，同时也深深地为自己的儿子感到自豪，只是，心里总是有点不舍。她语重心长地对丁丁说道：“大城市不比我们乡下，要时刻注意自己的安全，同时，也不要过分炫耀自己的能力，要谦虚做人，谨慎行事。”丁丁点点了，其实，他也舍不得离开家，离开妈妈，但是，毕竟他想要去看看外面的世界~~

  未完待续~~
 **` 注意： `** 本人现已开通两个微信公众号： 用Python做数学（微信号为：python_math）以及轻松学会Python爬虫（微信号为：easy_web_scrape）， 欢迎大家关注哦~~

[0]: ./1764395840.png
[100]: https://mp.csdn.net/postedit/80513522
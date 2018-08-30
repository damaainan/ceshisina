## 动态规划法（三）子集和问题(Subset sum problem)

来源：[https://www.cnblogs.com/jclian91/p/9132663.html](https://www.cnblogs.com/jclian91/p/9132663.html)

2018-06-04 12:01

  继续讲故事~~

  上次讲到我们的主人公丁丁，用神奇的动态规划法解决了杂货店老板的两个找零钱问题，得到了老板的肯定。之后，他就决心去大城市闯荡了，看一看外面更大的世界。

  这天，丁丁刚回到家，他的弟弟小连就拦住了他，“老哥，有个问题想请教你。”对于一向数学见长的小连，这次竟然破天荒的来问自己问题，丁丁感到不可思议：他俩一个以计算机见长，一个以数学见长，各自心里都有点小骄傲，不会轻易地向对方问问题。丁丁迟疑了一会儿，慢慢说道：“有什么问题是我们数学小天才解决不了的？”

  原来小连刚上高一，正在学数学中的集合，这不，今天他从一本算法书上看到一道题，想了很久都没有想出来。他把题目给了丁丁看：


对于某个给定值M，如何从某个给定的正整数集合S中找个一个子集合s，使得该子集和为给定值M。如M=7，S={1,3,4,5}，则s={3,4}.


  看到这道题目，丁丁脑海中掠过“动态规划法”的念头，对于动态规划法，他已经是轻车熟路了，但是对于究竟能否用动态规划法解决这个问题，他一时也没主意。于是，他对小连说道：“这题也许可以用动态规划法解决，不过我得好好想一想。”小连点点头，他还是蛮相信他的哥哥的。

  丁丁走进自己的房间，拿出草稿纸，开始了思考的旅程：


对于S={a1,a2,...,an}，每个元素只有取与不取两种情况，再考虑它们的和是否等于M，但是这样的情况共有2^n中，这种算法的效率显然是不行的。


  换条思路，令subset(i,j)表示S中前i个元素的子集和等于j的情况，则


* 若S[i] > j，则S[i]不在子集s中。
* 若S[i] <= j, 则有以下两种情况：一种情况是S[i]不在子集s中，则subset(i, j) = subset(i-1, j); 一种情况是S[i]在子集s中，则subset(i, j)= subset(i-1, j-S[i]).


  这样就有了这个问题的子结构问题，因此，只需要确定初始情况即可：


对于i=0,1,2,...,n,有subset(i, 0)=True, 对于j=1,2,...,M, 有subset(0, j)=False.


因此，利用动态规划法，就能得到(n+1) * (M+1)的真值表了，而答案就是subset(n, M). 算法有了，Python代码自然也有了：

```python
import numpy as np

# A Dynamic Programming solution for subset sum problem
# Returns true if there is a subset of set with sum equal to given sum

def isSubsetSum(S, n, M):
    # The value of subset[i, j] will be
    # true if there is a subset of
    # set[0..j-1] with sum equal to i
    subset = np.array([[True]*(M+1)]*(n+1))

    # If sum is 0, then answer is true
    for i in range(0, n+1):
        subset[i, 0] = True

    # If sum is not 0 and set is empty,
    # then answer is false
    for i in range(1, M+1):
        subset[0, i] = False

    # Fill the subset table in bottom-up manner
    for i in range(1, n+1):
        for j in range(1, M+1):
            if j < S[i-1]:
                subset[i, j] = subset[i-1, j]
            else:
                subset[i, j] = subset[i-1, j] or subset[i-1, j-S[i-1]]

    # print the True-False table
    for i in range(0, n+1):
        for j in range(0, M+1):
            print('%-6s'%subset[i][j], end="  ")
        print(" ")

    if subset[n, M]:
        print("Found a subset with given sum")
    else:
        print("No subset with given sum")

# test
st = [1, 3, 4, 5]
n = len(st)
sm = 7
isSubsetSum(st, n, sm)
```

输出结果如下：


True False False False False False False False

True True False False False False False False

True True False True True False False False

True True False True True True False True

True True False True True True True True

Found a subset with given sum


  那么，怎样求解子集s中的元素呢？也许可以用回溯法（backtracing），他这样想到，不过，他还是决定把剩余部分交给弟弟小连。

  几分钟后，当小连看到丁丁的解法后，兴奋地直跳起来。对于计算机编程，他也是有相当大的兴趣的，不过当务之急是解决哥哥剩下来的问题，那就是找出s中的元素。他想试着从输出的真值表入手：


对于subset(i, j) = subset(i-1, j)=True,则元素S[i]不在子集s中。对于subset(i,j)=True而subset(i-1, j)=False，则元素S[i]必定在子集s中， 此时subset(i-1, j-S[i])=True，这样就能通过递归法找到s中的元素了。对于这个问题，只要从subset(n, M)开始即可。


他觉得自己的思路是可行的，于是就在哥哥的程序上修改了起来：

```python
import numpy as np

# A Dynamic Programming solution for subset sum problem
# Returns true if there is a subset of set with sum equal to given sum

def isSubsetSum(S, n, M):
    # The value of subset[i, j] will be
    # true if there is a subset of
    # set[0..j-1] with sum equal to i
    subset = np.array([[True]*(M+1)]*(n+1))

    # If sum is 0, then answer is true
    for i in range(0, n+1):
        subset[i, 0] = True

    # If sum is not 0 and set is empty,
    # then answer is false
    for i in range(1, M+1):
        subset[0, i] = False

    # Fill the subset table in bottom-up manner
    for i in range(1, n+1):
        for j in range(1, M+1):
            if j < S[i-1]:
                subset[i, j] = subset[i-1, j]
            else:
                subset[i, j] = subset[i-1, j] or subset[i-1, j-S[i-1]]

    # print the True-False table
    for i in range(0, n+1):
        for j in range(0, M+1):
            print('%-6s'%subset[i][j], end="  ")
        print(" ")

    if subset[n, M]:
        print("Found a subset with given sum")
        sol = []
        # using backtracing to find the solution
        i = n
        while i >= 0:
            if subset[i, M] and not subset[i-1, M]:
                sol.append(S[i-1])
                M -= st[i-1]
            if M == 0:
                break
            i -= 1
        print('The solution is %s.' % sol)
    else:
        print("No subset with given sum")

# test
st = [1, 3, 4, 5]
n = len(st)
sm = 7
isSubsetSum(st, n, sm)
```

输出结果如下：


True False False False False False False False

True True False False False False False False

True True False True True False False False

True True False True True True False True

True True False True True True True True

Found a subset with given sum

The solution is [4, 3].


  终于解决了这个问题，小连长舒一口气，而站在一旁的丁丁，看着弟弟的程序，也露出了满意的微笑~~

  晚饭后，哥俩正坐在门口的大树下乘凉，一旁的大雄急匆匆地跑过来来他俩帮忙。原来，他也碰到了一道难题，题目是这样的：


对于一个由若干个正整数组成的集合S，如何将S划分成两部分，使得两部分的和一样？


丁丁和小连看了题目，微微一笑，因为答案就在他们刚才解决的问题中。那么，亲爱的读者，你能尝试着解决这道问题吗？

注意：本人现已开通两个微信公众号： 用Python做数学（微信号为：python_math）以及轻松学会Python爬虫（微信号为：easy_web_scrape）， 欢迎大家关注哦~~

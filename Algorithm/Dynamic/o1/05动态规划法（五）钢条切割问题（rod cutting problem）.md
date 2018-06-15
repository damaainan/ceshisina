<script type="text/javascript" src="http://localhost/MathJax/latest.js?config=default"></script>

## 动态规划法（五）钢条切割问题（rod cutting problem）

来源：[https://www.cnblogs.com/jclian91/p/9146895.html](https://www.cnblogs.com/jclian91/p/9146895.html)

2018-06-06 19:29

  继续讲故事~~

  我们的主人公现在已经告别了生于斯，长于斯的故乡，来到了全国最大的城市S市。这座S市，位于国家的东南部，是全国的经济中心，工商业极为发达，是这个国家的人民所向往的城市。这个到处都留着奶与蜜的城市，让丁丁充满了好奇感和新鲜感，他多想好好触摸这个城市的脉搏啊！

  这不，他此刻正走在城市的某高新园区，不远处传来钢条切割的声音。他好奇地走上前去，看着工人们正在熟练地切割钢条，并打包完成包装。此时，一位工人看到了丁丁，一看，竟是自己的同乡。他热情地上去打了招呼，并询问了乡下的情况，他俩约了中午一起吃饭。

  午饭时，老乡热情地请丁丁在园区最好的饭店吃饭，他俩聊得也很开心。突然，老乡想到了丁丁学过计算机方面的理论，于是他准备把自己最近遇到的问题告诉丁丁，看看他能不能解决。

 **`钢条切割问题`** ： 给定一段长度为n英寸的钢条和一个价格表\(p_i(i=1,2,...,n)\) ,求切割钢条方案，使得销售收益\(R_n\) 最大。注意，如果长度为n英寸的钢条的价格\(p_n\) 足够大，最优解可能就是完全不需要切割。


已知钢条价格表如下：

![][0]

  听到老乡正在为整个问题发愁，丁丁内心也想着尝试解决这个问题。毫无疑问，这个问题也是可以用动态规划法解决的，于是，他拿出稿纸推演起来：


将钢条从左边切割下长度为i的一段，只对右边剩下的长度为n-i的一段继续进行切割（递归求解），对左边的一段则不再进行切割。这样，不做任何切割的方案可以描述为：第一段长度为n，收益为pn，剩余部分长度为0，对应的收益为\\(R_0\\) =0。于是，我们就得到该问题的求解公式：
\\[R_n=\min\limits_{1\leq i \leq n}(p_n+R_n-1)\\]


采用自底向上法（bottom-up method）来求解该问题，需要用一个列表来记录收益\\(r_n\\) ,一个列表来记录切割方案，其Python代码如下：

```python
import time
# 使用动态规划法(dynamic programming)解决钢条切割问题

# 钢条长度与对应的收益
length = (1, 2, 3, 4, 5, 6, 7, 8, 9, 10)
profit = (1, 5, 8, 9, 10, 17, 17, 20, 24, 30)

# 动态归纳法，自底向上的CUT-ROD过程，加入备忘机制
# 运行时间: 多项式
# 参数：profit: 收益列表, n: 钢条总长度
# 返回参数: q: 最大收益
def bottom_up_cut_rod(profit, n):
    r = [0] # 收益列表
    s = [0]*(n+1) # 切割方案列表

    for j in range(1, n+1):
        q = float('-inf')
        for i in range(1, j+1):
            if max(q, profit[length.index(i)]+r[j-i]) == profit[length.index(i)]+r[j-i]:
                s[j] = i
            q = max(q, profit[length.index(i)]+r[j-i])

        r.append(q)
    return r[n], s[n]

# method of how to cut the rod
def rod_cut_method(profit, n):
    how = []
    while n != 0:
        t,s = bottom_up_cut_rod(profit, n)
        how.append(s)
        n -= s

    return how

for i in range(1, 11):
    t1 = time.time()
    money,s = bottom_up_cut_rod(profit, i)
    how =  rod_cut_method(profit, i)
    t2 = time.time()
    print('profit of %d is %d. Cost time is %ss.'%(i, money, t2-t1))
    print('Cut rod method:%s\n'%how)
```

输出结果：

```
profit of 1 is 1. Cost time is 0.0s.
Cut rod method:[1]

profit of 2 is 5. Cost time is 0.0s.
Cut rod method:[2]

profit of 3 is 8. Cost time is 0.0s.
Cut rod method:[3]

profit of 4 is 10. Cost time is 0.0s.
Cut rod method:[2, 2]

profit of 5 is 13. Cost time is 0.0s.
Cut rod method:[3, 2]

profit of 6 is 17. Cost time is 0.0s.
Cut rod method:[6]

profit of 7 is 18. Cost time is 0.0s.
Cut rod method:[6, 1]

profit of 8 is 22. Cost time is 0.0005009174346923828s.
Cut rod method:[6, 2]

profit of 9 is 25. Cost time is 0.0s.
Cut rod method:[6, 3]

profit of 10 is 30. Cost time is 0.0s.
Cut rod method:[10]
```

不一会儿他就搞定了这个问题，他将不同长度的钢条所能获得最大收益和对应的切割方案告诉了老乡。老乡听后大喜，他为丁丁解决了这个困扰它们公司长久的问题而感到由衷高兴，有了以上的结果，那么，钢条的长度再长也不是问题了。

  午饭后，老乡将刚才吃饭时丁丁的解决方法告诉了老板，老板也是喜出望外，他决定高薪聘请这个年轻人。而我们的丁丁，他早已离开高新区，向着下一个目的地出发了~~
 **` 注意： `** 本人现已开通两个微信公众号： 用Python做数学（微信号为：python_math）以及轻松学会Python爬虫（微信号为：easy_web_scrape）， 欢迎大家关注哦~~

[0]: https://images2018.cnblogs.com/blog/1219272/201806/1219272-20180606193252315-921836335.png
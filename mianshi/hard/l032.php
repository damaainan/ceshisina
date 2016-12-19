<?php
/**
 *Word Ladder II 词语阶梯之二


Given two words (start and end), and a dictionary, find all shortest transformation sequence(s) from start to end, such that:

Only one letter can be changed at a time
Each intermediate word must exist in the dictionary
For example,

Given:
start = "hit"
end = "cog"
dict = ["hot","dot","dog","lot","log"]
Return
[
["hit","hot","dot","dog","cog"],
["hit","hot","lot","log","cog"]
]
Note:
All words have the same length.
All words contain only lowercase alphabetic characters.


个人感觉这道题是相当有难度的一道题，它比之前那道Word Ladder 词语阶梯要复杂很多，全场第四低的通过率12.9%正说明了这道题的难度，我也是研究了网上别人的解法很久才看懂，然后照葫芦画瓢的写了出来，下面这种解法的核心思想是BFS，大概思路如下：首先需要把结束词也放入字典，我们的目的是找出所有的路径，我们建立一个路径集paths，用以保存所有路径，然后是起始路径p，在p中先把起始单词放进去。然后定义两个整型变量level，和minLevel，其中level是记录循环中当前路径的长度，minLevel是记录最短路径的长度，这样的好处是，如果某条路径的长度超过了已有的最短路径的长度，那么舍弃，这样会提高运行速度，相当于一种剪枝。还要定义一个set变量words，用来记录已经循环过的路径中的词，然后就是BFS的核心了，循环路径集paths里的内容，取出队首路径，如果该路径长度大于level，说明字典中的有些词已经存入路径了，如果在路径中重复出现，则肯定不是最短路径，所以我们需要在字典中将这些词删去，然后将words清空，对循环对剪枝处理。然后我们取出当前路径的最后一个词，对每个字母进行替换并在字典中查找是否存在替换后的新词，这个过程在之前那道Word Ladder 词语阶梯里面也有。如果替换后的新词在字典中存在，将其加入words中，并在原有路径的基础上加上这个新词生成一条新路径，如果这个新词就是结束词，则此新路径为一条完整的路径，加入结果中，并更新minLevel，若不是结束词，解将新路径加入路径集中继续循环。
 */
<?php 
/**
 * 【POJ 2492】 A Bug's Life (条件并查集/bfs)

A Bug's Life

Time Limit: 10000MS     Memory Limit: 65536K
Total Submissions: 31920        Accepted: 10471
Description

Background 

Professor Hopper is researching the sexual behavior of a rare species of bugs. He assumes that they feature two different genders and that they only interact with bugs of the opposite gender. In his experiment, individual bugs and their interactions were easy
to identify, because numbers were printed on their backs. 

Problem 

Given a list of bug interactions, decide whether the experiment supports his assumption of two genders with no homosexual bugs or if it contains some bug interactions that falsify it.
Input

The first line of the input contains the number of scenarios. Each scenario starts with one line giving the number of bugs (at least one, and up to 2000) and the number of interactions (up to 1000000) separated by a single space. In the following lines, each
interaction is given in the form of two distinct bug numbers separated by a single space. Bugs are numbered consecutively starting from one.
Output

The output for every scenario is a line containing "Scenario #i:", where i is the number of the scenario starting at 1, followed by one line saying either "No suspicious bugs found!" if the experiment is consistent with his assumption about the bugs' sexual
behavior, or "Suspicious bugs found!" if Professor Hopper's assumption is definitely wrong.
Sample Input
2
3 3
1 2
2 3
1 3
4 2
1 2
3 4

Sample Output
Scenario #1:
Suspicious bugs found!

Scenario #2:
No suspicious bugs found!





目大意就是n个人 m对 每对表示a与b相恋 然后问存不存在同性恋

起初没想到并查集 用搜的方法 对于每对关系都建双向边 枚举每个人 如果还没搜过 就从这个人开始搜 把他所在的块搜完 既然是独立的块 就可以给该点一个初值0/1(男/女) 然后在搜的过程中如果出现0-0或1-1的情况就说明有同性恋 否则就一直搜下去

并查集就是在一般并查集上加个条件 表示该点与该集合根结点的关系0/1(同性/不同性) 如果出现新加的对a-b在一个集合里 且a、b与根的关系相同 则为同性恋

 */
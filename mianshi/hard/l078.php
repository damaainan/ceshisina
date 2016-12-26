<?php
/**
 *Word Squares 单词平方


Given a set of words (without duplicates), find all word squares you can build from them.

A sequence of words forms a valid word square if the kth row and column read the exact same string, where 0 ≤ k < max(numRows, numColumns).

For example, the word sequence ["ball","area","lead","lady"] forms a word square because each word reads the same both horizontally and vertically.

b a l l
a r e a
l e a d
l a d y
Note:

There are at least 1 and at most 1000 words.
All words will have the exact same length.
Word length is at least 1 and at most 5.
Each word contains only lowercase English alphabet a-z.


Example 1:

Input:
["area","lead","wall","lady","ball"]

Output:
[
[ "wall",
"area",
"lead",
"lady"
],
[ "ball",
"area",
"lead",
"lady"
]
]

Explanation:
The output consists of two word squares. The order of output does not matter (just the order of words in each word square matters).


Example 2:

Input:
["abat","baba","atan","atal"]

Output:
[
[ "baba",
"abat",
"baba",
"atan"
],
[ "baba",
"abat",
"baba",
"atal"
]
]

Explanation:
The output consists of two word squares. The order of output does not matter (just the order of words in each word square matters).


这道题是之前那道Valid Word Square的延伸，由于要求出所有满足要求的单词平方，所以难度大大的增加了，不要幻想着可以利用之前那题的解法来暴力破解，OJ不会答应的。那么根据以往的经验，对于这种要打印出所有情况的题的解法大多都是用递归来解，那么这题的关键是根据前缀来找单词，我们如果能利用合适的数据结构来建立前缀跟单词之间的映射，使得我们能快速的通过前缀来判断某个单词是否存在，这是解题的关键。对于建立这种映射，这里主要有两种方法，一种是利用哈希表来建立前缀和所有包含此前缀单词的集合之前的映射，第二种方法是建立前缀树Trie，顾名思义，前缀树专门就是为这种问题设计的。那么我们首先来看第一种方法，用哈希表来建立映射的方法，我们就是取出每个单词的所有前缀，然后将该单词加入该前缀对应的集合中去，然后我们建立一个空的nxn的char矩阵，其中n为单词的长度，我们的目标就是来把这个矩阵填满，我们从0开始遍历，我们先取出长度为0的前缀，即空字符串，由于我们在建立映射的时候，空字符串也和每个单词的集合建立了映射，然后我们遍历这个集合，用遍历到的单词的i位置字符，填充矩阵mat[i][i]，然后j从i+1出开始遍历，对应填充矩阵mat[i][j]和mat[j][i]，然后我们根据第j行填充得到的前缀，到哈希表中查看有没单词，如果没有，就break掉，如果有，则继续填充下一个位置。最后如果j==n了，说明第0行和第0列都被填好了，我们再调用递归函数，开始填充第一行和第一列，依次类推，直至填充完成
 */
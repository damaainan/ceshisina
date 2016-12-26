<?php
/**
 *Edit Distance 编辑距离


Given two words word1 and word2, find the minimum number of steps required to convert word1 to word2. (each operation is counted as 1 step.)

You have the following 3 operations permitted on a word:

a) Insert a character
b) Delete a character
c) Replace a character



这道题让求从一个字符串转变到另一个字符串需要的变换步骤，共有三种变换方式，插入一个字符，删除一个字符，和替换一个字符。根据以往的经验，对于字符串相关的题目十有八九都是用动态规划Dynamic Programming来解，这道题也不例外。这道题我们需要维护一个二维的数组dp，其中dp[i][j]表示从word1的前i个字符转换到word2的前j个字符所需要的步骤。那我们可以先给这个二维数组dp的第一行第一列赋值，这个很简单，因为第一行和第一列对应的总有一个字符串是空串，于是转换步骤完全是另一个字符串的长度。跟以往的DP题目类似，难点还是在于找出递推式，我们可以举个例子来看，比如word1是“bbc"，word2是”abcd“，那么我们可以得到dp数组如下：



Ø a b c d
Ø 0 1 2 3 4
b 1 1 1 2 3
b 2 2 1 2 3
c 3 3 2 1 2


我们通过观察可以发现，当word1[i] == word2[j]时，dp[i][j] = dp[i - 1][j - 1]，其他情况时，dp[i][j]是其左，左上，上的三个值中的最小值加1，那么可以得到递推式为：

dp[i][j] =      /    dp[i - 1][j - 1]                                                                   if word1[i - 1] == word2[j - 1]

\    min(dp[i - 1][j - 1], min(dp[i - 1][j], dp[i][j - 1])) + 1            else
 */
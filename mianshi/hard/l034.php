<?php
/**
 *Palindrome Partitioning II 拆分回文串之二


Given a string s, partition s such that every substring of the partition is a palindrome.

Return the minimum cuts needed for a palindrome partitioning of s.

For example, given s = "aab",
Return 1 since the palindrome partitioning ["aa","b"] could be produced using 1 cut.



这道题是让找到把原字符串拆分成回文串的最小切割数，需要用动态规划Dynamic Programming来做，使用DP的核心是在于找出递推公式，之前有道地牢游戏Dungeon Game的题也是需要用DP来做，而那道题是二维DP来解，这道题由于只是拆分一个字符串，需要一个一维的递推公式，我们还是从后往前推，递推公式为：dp[i] = min(dp[i], 1+dp[j+1] )    i<=j <n，那么还有个问题，是否对于i到j之间的子字符串s[i][j]每次都判断一下是否是回文串，其实这个也可以用DP来简化，其DP递推公式为P[i][j] = s[i] == s[j] && P[i+1][j-1]，其中P[i][j] = true if [i,j]为回文。
 */
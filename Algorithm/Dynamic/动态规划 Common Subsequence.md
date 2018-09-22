## 动态规划 Common Subsequence

来源：[http://www.cnblogs.com/baobao2201128470/p/8999935.html](http://www.cnblogs.com/baobao2201128470/p/8999935.html)

时间 2018-05-06 22:00:00



#### 描述

A subsequence of a given sequence is the given sequence with some elements (possible none) left out. Given a sequence X = <x1, x2, ..., xm> another sequence Z = <z1, z2, ..., zk> is a subsequence of X if there exists a strictly increasing sequence <i1, i2, ..., ik> of indices of X such that for all j = 1,2,...,k, xij = zj. For example, Z = <a, b, f, c> is a subsequence of X = <a, b, c, f, b, c> with index sequence <1, 2, 4, 6>. Given two sequences X and Y the problem is to find the length of the maximum-length common subsequence of X and Y.     



#### 输入

The program input is from a text file. Each data set in the file contains two strings representing the given sequences. The sequences are separated by any number of white spaces. The input data are correct.


#### 输出

For each set of data the program prints on the standard output the length of the maximum-length common subsequence from the beginning of a separate line.


#### 样例输入


abcfbc abfcab

programming contest

abcd mnp

  
#### 样例输出


4

2

0

一道非常简单的动态规划题，菜鸡小白正在研究之中

```c
#include <iostream>
#include <algorithm>
#include <string>
#define N 1001
#include <cstring>
using namespace std;
int a[N][N];
int main()
{
    int n, m, j, k, i;
    string   x, y;
    while (cin >> x >> y)
    {
        n = x.length();
        m = y.length();
        for (i = 0; i < n; i++)
        {
            for (j = 0; j < m; j++)
            {
                a[i][j] = 0;
            }
        }
        for (i = 1; i <= n; i++)
        {
            for (j = 1; j <= m; j++)
            {
                if (x[i-1] == y[j-1])
                    a[i][j] = a[i - 1][j - 1] + 1;
                else if (a[i - 1][j] > a[i][j - 1])
                    a[i][j] = a[i - 1][j];
                else a[i][j] = a[i][j - 1];
            }
        }
        cout << a[n][m] << endl;
    }    
}
```



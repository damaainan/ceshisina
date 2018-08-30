<script type="text/javascript" src="http://localhost/MathJax/latest.js?config=default"></script>

## 动态规划法（十）最长公共子序列（LCS）问题

来源：[https://www.cnblogs.com/jclian91/p/9173076.html](https://www.cnblogs.com/jclian91/p/9173076.html)

2018-06-12 15:04


## 问题介绍

给定一个序列 \\(X=&lt;x_1,x_2,....,x_m&gt;\\) ，另一个序列 \\(Z=&lt;z_1,z_2,....,z_k&gt;\\) 满足如下条件时称为X的 **`子序列`** ：存在一个严格递增的X的下标序列\\(&lt;i_1,i_2,...,i_k&gt;\\) ，对所有的\\(j=1,2,...,k\\) 满足\\(x_{i_j}=z_j.\\) 

给定两个序列\\(X\\) 和\\(Y\\) ,如果\\(Z\\) 同时是\\(X\\) 和\\(Y\\) 的子序列，则称\\(Z\\) 是\\(X\\) 和\\(Y\\) 的 **`公共子序列`** 。 **`最长公共子序列（LCS）问题`** 指的是：求解两个序列\\(X\\) 和\\(Y\\) 的长度最长的公共子序列。 **`例如`** ，序列\\(X=&lt;A,B,C,B,D,A,B&gt;\\) 和\\(Y=&lt;B,D,C,A,B,A&gt;\\) 的最长公共子序列为\\(&lt;B,C,B,A&gt;\\) ，长度为4。

  本文将具体阐释如何用动态规划法（Dynamic Programming）来求解最长公共子序列（LCS）问题。
## 算法分析
### 1. LCS的子结构

  给定一个序列\\(X=&lt;x_1,x_2,....,x_m&gt;\\) ，对\\(i=0,1,...,m\\) ，定义\\(X\\) 的第i前缀为\\(X_i=&lt;x_1,x_2,....,x_i&gt;\\) ,其中\\(X_0\\) 为空序列。

  （ **`LCS的子结构`** ）令\\(X=&lt;x_1,x_2,....,x_m&gt;\\) 和\\(Y=&lt;y_1,y_2,....,y_n&gt;\\) 为两个序列，\\(Z=&lt;z_1,z_2,....,z_k&gt;\\) 为\\(X\\) 和\\(Y\\) 的任意LCS，则：


* 如果\\(x_m=y_n,\\) 则\\(z_k=x_m=y_n\\) 且\\(Z_{k-1}\\) 是\\(X_{m-1}\\) 和\\(Y_{n-1}\\) 的一个LCS。
* 如果\\(x_m\neq y_n,\\) 则\\(z_k \neq x_m\\) 意味着\\(Z_{k-1}\\) 是\\(X_{m-1}\\) 和\\(Y\\) 的一个LCS。
* 如果\\(x_m\neq y_n,\\) 则\\(z_k\neq y_n\\) 且\\(Z_{k-1}\\) 是\\(X\\) 和\\(Y_{n-1}\\) 的一个LCS。


### 2. 构造递归解

  在求\\(X=&lt;x_1,x_2,....,x_m&gt;\\) 和\\(Y=&lt;y_1,y_2,....,y_n&gt;\\) 的一个LCS时，需要求解一个或两个子问题：如果\\(x_m=y_n\\) ，应求解\\(X_{m-1}\\) 和\\(Y_{n-1}\\) 的一个LCS，再将\\(x_m=y_n\\) 追加到这个LCS的末尾，就得到\\(X\\) 和\\(Y\\) 的一个LCS；如果\\(x_m\neq y_n\\) ，需求解\\(X_{m-1}\\) 和\\(Y\\) 的一个LCS与\\(X\\) 和\\(Y_{n-1}\\) 的一个LCS，两个LCS较长者即为\\(X\\) 和\\(Y\\) 的一个LCS。当然，可以看出，LCS问题容易出现重叠子问题，这时候，就需要用动态规划法来解决。

  定义\\(c[i,j]\\) 表示\\(X_i\\) 和\\(Y_j\\) 的LCS的长度。如果\\(i=0\\) 或\\(j=0\\) ，则\\(c[i,j]=0.\\) 利用LCS的子结构，可以得到如下公式：

$$
c[i,j]=\left\\\{
     \begin{array}{lr}
     0,\qquad 若i=0或j=0\\\
     c[i-1, j-1]+1,\qquad 若i,j>0且x_i=y_j\\\
    \max(c[i, j-1], c[i-1, j]),\qquad 若i,j>0且x_i\neq y_j
     \end{array}
\right.
$$

### 3. 计算LCS的长度

  计算LCS长度的伪代码为LCS-LENGTH. 过程LCS-LENGTH接受两个子序列\\(X=&lt;x_1,x_2,....,x_m&gt;\\) 和\\(Y=&lt;y_1,y_2,....,y_n&gt;\\) 为输入。它将\\(c[i, j]\\) 的值保存在表\\(c\\) 中，同时，维护一个表\\(b\\) ，帮助构造最优解。过程LCS-LENGTH的伪代码如下：

```python
LCS-LENGTH(X, Y):
m = X.length
n = Y.length
let b[1...m, 1...n] and c[0...m, 0...n] be new table

for i = 1 to m
    c[i, 0] = 0
for j = 1 to n
    c[0, j] = 0

for i = 1 to m
    for j = 1 to n
        if x[i] == y[j]
           c[i,j] = c[i-1, j-1]+1
           b[i,j] = 'diag'
           
        elseif c[i-1, j] >= c[i, j-1]
            c[i,j] = c[i-1, j]
            b[i,j] = 'up'
            
        else
            c[i,j] = c[i, j-1]
            b[i,j] = 'left'
            
return c and b
```
### 4. 寻找LCS

  为了寻找\\(X\\) 和\\(Y\\) 的一个LCS， 我们需要用到LCS-LENGTH过程中的表\\(b\\) ，只需要简单地从\\(b[m, n]\\) 开始，并按箭头方向追踪下去即可。当在表项\\(b[i,j]\\) 中遇到一个'diag'时，意味着\\(x_i=y_j\\) 是LCS的一个元素。按照这种方法，我们可以按逆序依次构造出LCS的所有元素。伪代码PRINT-LCS如下：

```python
PRINT-LCS(b, X, i, j):
    if i == 0 or j == 0
        return
    if b[i,j] == 'diag'
        PRINT-LCS(b, X, i-1, j-1)
        print x[i]
    elseif b[i,j] == 'up':
        PRINT-LCS(b, X, i-1, j)
    else
        PRINT-LCS(b, X, i, j-1)
```
## 程序实现

  有了以上对LCS问题的算法分析，我们不难写出具体的程序来实现它。下面将会给出Python代码和Java代码，供读者参考。

  完整的Python代码如下：

```python
import numpy as np

# using dynamic programming to solve LCS problem
# parameters: X,Y -> list
def LCS_LENGTH(X, Y):
    m = len(X) # length of X
    n = len(Y) # length of Y

    # create two tables, b for directions, c for solution of sub-problem
    b = np.array([[None]*(n+1)]*(m+1))
    c = np.array([[0]*(n+1)]*(m+1))

    # use DP to sole LCS problem
    for i in range(1, m+1):
        for j in range(1, n+1):
            if X[i-1] == Y[j-1]:
                c[i,j] = c[i-1,j-1]+1
                b[i,j] = 'diag'
            elif c[i-1,j] >= c[i, j-1]:
                c[i,j] = c[i-1,j]
                b[i,j] = 'up'
            else:
                c[i,j] = c[i,j-1]
                b[i,j] = 'left'
    #print(b)
    #print(c)
    return b,c

# print longest common subsequence of X and Y
def print_LCS(b, X, i, j):

    if i == 0 or j == 0:
        return None
    if b[i,j] == 'diag':
        print_LCS(b, X, i-1, j-1)
        print(X[i-1], end=' ')
    elif b[i,j] == 'up':
        print_LCS(b, X, i-1, j)
    else:
        print_LCS(b, X, i, j-1)

X = 'conservatives'
Y = 'breather'

b,c = LCS_LENGTH(X,Y)
print_LCS(b, X, len(X), len(Y))
```

输出结果如下：

```
e a t e 
```

  完整的Java代码如下：

```java
package DP_example;

import java.util.Arrays;
import java.util.List;

public class LCS {
    // 主函数
    public static void main(String[] args) {
        // 两个序列X和Y
        List<String> X = Arrays.asList("A","B","C","B","D","A","B");
        List<String> Y = Arrays.asList("B","D","C","A","B","A");

        int m = X.size(); //X的长度
        int n = Y.size(); // Y的长度
        String[][] b = LCS_length(X, Y); //获取维护表b的值

        print_LCS(b, X, m, n); // 输出LCS
    }

    /*
    函数LCS_length：获取维护表b的值
    传入参数： 两个序列X和Y
    返回值： 维护表b
     */
    public static String[][] LCS_length(List X, List Y){
        int m = X.size(); //X的长度
        int n = Y.size(); // Y的长度
        int[][] c = new int[m+1][n+1];
        String[][] b = new String[m+1][n+1];

        // 对表b和表c进行初始化
        for(int i=1; i<m+1; i++){
            for(int j=1; j<n+1; j++){
                c[i][j] = 0;
                b[i][j] = "";
            }
        }
        
        // 利用自底向上的动态规划法获取b和c的值
        for(int i=1; i<m+1; i++){
            for(int j=1; j<n+1; j++){
                if(X.get(i-1) == Y.get(j-1)){
                    c[i][j] = c[i-1][j-1]+1;
                    b[i][j] = "diag";
                }
                else if(c[i-1][j] >= c[i][j-1]){
                    c[i][j] = c[i-1][j];
                    b[i][j] = "up";
                }
                else{
                    c[i][j] = c[i][j-1];
                    b[i][j] = "left";
                }
            }
        }

        return b;
    }

    // 输出最长公共子序列
    public static int print_LCS(String[][] b, List X, int i, int j){

        if(i == 0 || j == 0)
            return 0;

        if(b[i][j].equals("diag")){
            print_LCS(b, X, i-1, j-1);
            System.out.print(X.get(i-1)+" ");
        }
        else if(b[i][j].equals("up"))
            print_LCS(b, X, i-1, j);
        else
            print_LCS(b, X, i, j-1);

        return 1;
    }
}
```

输出结果如下：

```
B C B A 
```
## 参考文献


* 算法导论（第三版） 机械工业出版社
* [https://www.geeksforgeeks.org/longest-common-subsequence/][100]

 **` 注意： `** 本人现已开通两个微信公众号： 因为Python（微信号为：python_math）以及轻松学会Python爬虫（微信号为：easy_web_scrape）， 欢迎大家关注哦~~

[100]: https://www.geeksforgeeks.org/longest-common-subsequence/
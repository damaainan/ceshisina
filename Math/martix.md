


<script type="text/javascript" src="http://localhost/MathJax/latest.js?config=default"></script>

测试矩阵写法

$\bigl( \begin{matrix} a & b \\ c & d \end{matrix} \bigr)$

$ \begin{bmatrix} a & b \\ c & d \end{bmatrix} $


$$
 \left[
 \begin{matrix}
   1 & 2 & 3 \\\
   4 & 5 & 6 \\\
   7 & 8 & 9
  \end{matrix}
  \right] 
$$

$$
	\begin{bmatrix}
        1 & x & x^2 \\\
        1 & y & y^2 \\\
        1 & z & z^2 \\\
        \end{bmatrix}
$$



### 简单矩阵表示

使用
```
$$\begin{matrix}
…
\end{matrix}$$
```
来生成矩阵，其中… 表示的是LaTeX 的矩阵命令，矩阵命令中每一行以 \ 结束，矩阵的元素之间用&来分隔开。
```
$$
  \begin{matrix}
   1 & 2 & 3 \\
   4 & 5 & 6 \\
   7 & 8 & 9
  \end{matrix} \tag{1}
$$
```

$$
  \begin{matrix}
   1 & 2 & 3 \\\
   4 & 5 & 6 \\\
   7 & 8 & 9
  \end{matrix} \tag{1}
$$


上述显示的矩阵不是很美观，可以给矩阵加上括号，加括号的方式有很多，大致可分为两种：使用\left … \right 或者把公式命令中的matrix 改成 pmatrix、bmatrix、Bmatrix、vmatrix、Vmatrix等。

#### 3.1 带括号的矩阵 \left … \right
```
$$
\left \{
\begin{matrix}
1&2&3\\
4&5&6\\
7&8&9
\end{matrix}
\right \} \tag{2}
$$
```


$$
\left \{
\begin{matrix}
1&2&3\\\
4&5&6\\\
7&8&9
\end{matrix}
\right \} \tag{2}
$$

或者使用
```
$$
 \left[
 \begin{matrix}
   1 & 2 & 3 \\
   4 & 5 & 6 \\
   7 & 8 & 9
  \end{matrix}
  \right] \tag{2}
$$
```

$$
 \left[
 \begin{matrix}
   1 & 2 & 3 \\\
   4 & 5 & 6 \\\
   7 & 8 & 9
  \end{matrix}
  \right] \tag{2}
$$

#### 3.2 带括号的矩阵 bmatrix、Bmatrix
```
$$
 \begin{bmatrix}
   1 & 2 & 3 \\
   4 & 5 & 6 \\
   7 & 8 & 9
  \end{bmatrix} \tag{4}
$$
```

$$
 \begin{bmatrix}
   1 & 2 & 3 \\\
   4 & 5 & 6 \\\
   7 & 8 & 9
  \end{bmatrix} \tag{4}
$$

```
$$
 \begin{Bmatrix}
   1 & 2 & 3 \\
   4 & 5 & 6 \\
   7 & 8 & 9
  \end{Bmatrix} \tag{5}
$$
```

$$
 \begin{Bmatrix}
   1 & 2 & 3 \\\
   4 & 5 & 6 \\\
   7 & 8 & 9
  \end{Bmatrix} \tag{5}
$$

#### 3.3 带括号的矩阵vmatrix、Vmatrix
```
$$
 \begin{vmatrix}
   1 & 2 & 3 \\
   4 & 5 & 6 \\
   7 & 8 & 9
  \end{vmatrix} \tag{5}
$$
```

$$
 \begin{vmatrix}
   1 & 2 & 3 \\\
   4 & 5 & 6 \\\
   7 & 8 & 9
  \end{vmatrix} \tag{5}
$$

```
$$
 \begin{Vmatrix}
   1 & 2 & 3 \\
   4 & 5 & 6 \\
   7 & 8 & 9
  \end{Vmatrix} \tag{5}
$$
```

$$
 \begin{Vmatrix}
   1 & 2 & 3 \\\
   4 & 5 & 6 \\\
   7 & 8 & 9
  \end{Vmatrix} \tag{5}
$$

#### 3.4 带省略号的矩阵

如果矩阵元素太多，可以使用\cdots ⋯ \ddots ⋱ \vdots ⋮ 等省略符号来定义矩阵。
```
$$
\left[
\begin{matrix}
 1      & 2      & \cdots & 4      \\
 7      & 6      & \cdots & 5      \\
 \vdots & \vdots & \ddots & \vdots \\
 8      & 9      & \cdots & 0      \\
\end{matrix}
\right]
$$
```

$$
\left[
\begin{matrix}
 1      & 2      & \cdots & 4      \\\
 7      & 6      & \cdots & 5      \\\
 \vdots & \vdots & \ddots & \vdots \\\
 8      & 9      & \cdots & 0      \\\
\end{matrix}
\right]
$$


#### 3.5 带参数的矩阵

写增广矩阵，可能需要最右边一列单独考虑。可以用array命令来处理：
```
$$ 
 \left[ \begin{array}{cc|c} 
1 & 2 & 3 \\
4 & 5 & 6 
\end{array} \right] \tag{7} 
$$
```


$$ 
 \left[ \begin{array}{cc|c} 
1 & 2 & 3 \\\
4 & 5 & 6 
\end{array} \right] \tag{7} 
$$


#### 3.6 行间矩阵

可以使用 `\bigl(...\bigr)` 定义行间矩阵。
    
    我们使用矩阵 $\bigl( \begin{smallmatrix} a & b \\ c & d \end{smallmatrix} \bigr)$ 作为因子矩阵，将其...

我们使用矩阵 \bigl( \begin{matrix} a & b \\\ c & d \end{matrix} \bigr) 作为因子矩阵，然后…
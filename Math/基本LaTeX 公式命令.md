<script type="text/javascript" src="http://localhost/MathJax/latest.js?config=default"></script>

## 基本LaTeX 公式命令

#### 常用希腊字母

小写命令  |  小写显示
-|-
`\alpha`  | α
`\beta`   | β
`\gamma`  | γ
`\delta`  | δ
`\zeta`   | ζ
`\eta`    | η
`\iota`   | ι
`\kappa`  | κ
`\lambda` | λ
`\mu`     | μ
`\rho`    | ρ
`\sigma`  | σ
`\tau`    | τ
`\omega`  | ω

> Tips 

如果使用大写的希腊字母，把命令的首字母变成大写即可，例如 `\Gamma` 输出的是 `Γ`。

如果使用斜体大写希腊字母，再在大写希腊字母的LaTeX命令前加上`var`，例如`\varGamma` 生成 `Γ`。

举例：
```
$$
 \varGamma(x) = \frac{\int_{\alpha}^{\beta} g(t)(x-t)^2\text{ d}t }{\phi(x)\sum_{i=0}^{N-1} \omega_i} \tag{2}
$$
```
生成的结果如下： 

$$
 \Gamma(x) = \frac{\int_{\alpha}^{\beta} g(t)(x-t)^2\text{ d}t }{\phi(x)\sum_{i=0}^{N-1} \omega_i} \tag{2}
$$

####  常用求和符号和积分号

命令 | 显示结果
-|-
\sum   |  ∑
\int   |  ∫
\sum_{i=1}^{N}  | ∑Ni=1
\int_{a}^{b}   |  ∫ba
\prod  |  ∏
\iint   | ∬
\prod_{i=1}^{N} | ∏Ni=1
\iint_{a}^{b}   | ∬ba
\bigcup | ⋃
\bigcap  |⋂
\bigcup_{i=1}^{N}  |  ⋃Ni=1

####  其他常用符号

命令 | 显示结果
-|-
`\sqrt[3]{2}` | 2√3
`\sqrt{2}`    | √2
`x_{3}`  |  x<sub>3</sub>
`\lim_{x \to 0}` |  limx→0
`\frac{1}{2}` | 12
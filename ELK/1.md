<script type="text/javascript" src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=default"></script>
公式显示有问题是因为远程 js  不稳定
$ $公式$ $表示行间公式，本来Tex中使用**`\(`**公式**`\)`**表示行内公式，但因为Markdown中\是转义字符，所以在Markdown中输入行内公式使用**`\\(`**公式**`\\)`**，如下代码：  

```
$$x=\frac{-b\pm\sqrt{b^2-4ac}}{2a}$$
\\(x=\frac{-b\pm\sqrt{b^2-4ac}}{2a}\\)
```

$$x=\frac{-b\pm\sqrt{b^2-4ac}}{2a}$$

$$log_b(x_1*x_2)=log_bx_1+log_bx_2$$

$$log_b(\frac{x_1}{x_2})=log_bx_1-log_bx_2$$

$$log_b(x^c)=c*log_bx$$

\\((Big-\Omega)\\)

\\((Big-\theta)\\)

\\(17n^2\in O(n^2)\\)
\\(17n^2\in O(n^37)\\)
\\(17n^2\in O(2^n)\\)

\\(f(n)=n\\)
\\(f(n)\in O(n^2)\\)
\\(n^2\in \Omega(n)\\)

\\((Big\theta)\\)


$$\theta(f)=O(f)\cap\Omega(f)$$
\\(f(n)=4n\\)

$$f(n)\in O(n)$$
$$f(n)\in \Omega(n)$$
$$f(n)\in \theta(n)$$


\\(O(Cf(n))=O(f(n))\\)  
\\(T1(n)=O(f(n))\\)  
\\(T2(n)=O(g(n))\\)  
\\(T1(n)+T2(n)=O(max(f(n),g(n))) \\)  
\\(T1(m)=O(f(m)) \\)  
\\(T2(n)=O(g(n)) \\)  
\\(T1(m)+T2(n)=O(f(m)+g(n)) \\)  

\\(T1(n)=O(f(n)) \\)  
\\(T2(n)=O(g(n)) \\)  
\\(T1×T2=O(f(n)×g(n)) \\)

\\(T(n)=T(\frac{n}{3})+T(\frac{2n}{3})+n) \\)

\\(n\rightarrow\frac{2}{3}n\rightarrow(\frac{2}{3})^2n\rightarrow ... \rightarrow 1 \\)

\\((\frac{2}{3})^kn=1 \\)  
\\(k=log_{\frac{3}{2}}n \\)  

\\(T(n)\leq\sum_{i=0}^kn =(k+1)n=n(log_{\frac{3}{2}}n + 1 ) \\)  


\\(T(n)=O(nlog n) \\)  

$$f(n)=af(\frac{n}{b})+d(n)$$
\\(T(n)=T(\frac{n}{3}+T(\frac{2n}{3})+n) \\)  
\\(O(nlog n)\\)  

\\(\sqrt n\\)

$$ $$
\\( \\)


行内公式行内公式行内公式行 \\(\sqrt n\\)内公式行内公式行内公式行内公式行 \\(T(n)=O(nlog n) \\)内公式行内公\\((\frac{2}{3})^kn=1 \\) 式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式行内公式

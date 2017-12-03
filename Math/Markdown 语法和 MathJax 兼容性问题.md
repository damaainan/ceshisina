# Markdown 语法和 MathJax 兼容性问题

 时间 2017-11-27 14:47:08 

原文[http://mengkang.net/1087.html][1]


## 问题

例如有这样一段文字

    $$net_{h1} = w_1 * i_1 + w_2 * i_2 + b_1 * 1$$

公式里面的 `*` 和 `_` 都可能被 markdown 语法解析。导致最后公式无法正常解析。 

## 解决方案

我使用的是 [https://github.com/SegmentFault/HyperDown][3] 解析器，下面对其进行一些扩展改造 

## 单行解析处理

HyperDown 中对单行里面的代码片段的解析思路是：

把匹配到的代码碎片使用的是先用一个数组 `$this->_holders` 来存放，最后其他内容解析完毕之后再替换回来。 

因此我对公式行内的解析也同样的方式，在其 `parseInline` 里面增加一个匹配规则 

    $text = preg_replace_callback(
        "/(\\\${1,2})(.+)\\1/",
        function ($matches) use ($self) {
            // 表达式中间的字符应该都是单字节
            if (strlen($matches[2]) > mb_strlen($matches[2])) {
                return $matches[1] . $matches[2] . $matches[1];
            }
            return  $matches[1] . $self->makeHolder($matches[2]) . $matches[1];
        },
        $text
    );

## 多行的解析

参考 HyperDown 中对代码块的解析规则，我们可以把 `$$` 作为开始匹配符和结束匹配服，知道公式占的行是多少，然后对这个块的代码做自定义的处理。 

首先，在 parseBlock 里增加公式块的匹配。 
```
    // 对 TeX 或 LaTeX 公式块的支持
    if ($this->_mathJax) {
        if (preg_match("/^(\s*)\\$\\$([^\\$]*)$/i", $line, $matches)) {
            if ($this->isBlock('mathJax')) {
                $this->setBlock($key)->endBlock();
            } else {
                $this->startBlock('mathJax', $key);
            }
    
            continue;
        } else if ($this->isBlock('mathJax')) {
            $this->setBlock($key);
            continue;
        }
    }
```
然后是对应的解析规则
```
    private function parseMathJax(array $lines){
        $str = '<p>'.implode("\n", $lines).'</p>';
        return $str;
    }
```
就这么简单，解决了。最后地址： [https://github.com/zhoumengkang/HyperDown][4]


[1]: http://mengkang.net/1087.html

[3]: https://github.com/SegmentFault/HyperDown
[4]: https://github.com/zhoumengkang/HyperDown
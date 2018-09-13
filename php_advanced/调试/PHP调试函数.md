# [PHP调试函数](http://www.cnblogs.com/grimm/p/6589144.html)

## 基础版

我见过封装几次这样封装的函数：

    function debug($data){
        echo '';
    }
    

挺好，挺好！其实未见到这个函数之前我自己也做过这样的函数

只是我比他多想了一步：需要停止运行时，能不能增加一个参数来控制？于是我的版本最初是这样的：

```php
    function debug($data, $isStop = false){
        echo '';
    
        $isStop && exit;
    }
    
    //调用示例
    debug('abc'); // 输出数据
    debug('abc', 1); // 输出数据并停止
```

- - -

## 进阶版

后来我发现这个虽然能控制停止和有排版输出了，可是有时候忘记了调用调试输出的代码是写在哪个函数里容易造成调试代码的遗留呢，于是我就改进成这样:

```php
    function debug($data, $isStop = false){
        $trace = (new \Exception())->getTrace()[0];
        echo '文件行号:' . $trace['file'] . ':' . $trace['line'];
        echo '';
    
        $isStop && exit;
    }
```

- - -

## 高级版

我在想，ajax调试的时候如果这样输出了一堆HTML出去，前端的回调通常会不正常工作，因为前端一般要的是从json读取属性嘛

所以想了想，我就决定判断是否ajax请求，是的话我就返回一个json，不然就输出HTML，于是进一步的版本变成了这样:

```php
<?php
function debug($data, $isStop = false){
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' || isset($_GET['_isAjax']) || isset($_POST['_isAjax']);
    
    $trace = (new \Exception())->getTrace()[0];
    if($isAjax){
        header('Content-type:application/json;charset=utf-8');
        exit(json_encode(array(
            'file' => $trace['file'],
            'line' => $trace['line'],
            'dataStr' => var_export($data, true),
            'data' => $data,
        )));
    }else{
        echo '文件行号:' . $trace['file'] . ':' . $trace['line'];
        echo '';
    }

    $isStop && exit;
}
```

从此，我只要暂时在前端回调里将代码改成alert(result.dataStr)或者看浏览器开发者控工具的网络选项卡的请求响应报文就行了

其实上面这个函数已经足够实用了

- - -

## 最终版

最后再来一个比较强大的版本，支持ajax返回json查看结构以及支持输出运行回溯，这是我实际工作中使用的版本

```php
<?php
/**
 * 输出调试信息
 * @author KK
 * @param mixed $data 要输出的调试数据
 * @param int $mode 调试模式
 * 解释：11=输出调试数据并停止运行，111=附加运行回溯输出并停止运行
 * 110=附加运行回溯输出但不停止运行
 * 
 * @example
 * 
 * ```php
 * debug(123, 110);
 * debug([1,2,3], 111);
 * debug([1, 2, 3, 'a' => 'b'], 11);    
 * ```
 */
function debug($data, $mode = 0){
    static $debugCount = 0;
    $debugCount++;
        
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' || isset($_GET['_isAjax']) || isset($_POST['_isAjax']);
        
    $exception = new \Exception();
    $lastTrace = $exception->getTrace()[0];
    $file = $lastTrace['file'];
    $line = $lastTrace['line'];

    $fileCodes = is_file($file) ? file($file) : '读取失败';
    $code = '(无法获取脚本内容)';
    if($fileCodes != ''){
        $matchedCodes = [];
        $lineScript = $fileCodes[$line - 1];
        if(preg_match('/debug.*\(.*\)(?= *;)/i', $lineScript, $matchedCodes)){
            $code = $matchedCodes[0];
        }
    }
        
    $showData = var_export($data, true);
        
    if($isAjax){
        header('Content-type:application/json;charset=utf-8');
        exit(json_encode(array(
            'file' => $file,
            'line' => $line,
            'dataStr' => $showData,
            'data' => $data,
        )));
    }else{
        $dataType = gettype($data);
        $backLink = '';
        if(isset($_SERVER['HTTP_REFERER'])){
            $backLink = '<a href="' . $_SERVER['HTTP_REFERER'] . '">返回(清空表单)</a>'
                        . '<a href="javascript:history.back()">返回(保留表单状态)</a>';
        }else{
            $backLink = '<a href="javascript:history.back()">返回</a>';
        }
            
        $length = 'no';
        if(is_string($data)){
            $length = strlen($data);
        }
        
        if(PHP_SAPI !== 'cli'){
            $traceHtml = '';
            if($mode == 111 || $mode == 110){
                $traceHtml = '<div><p>运行轨迹:</p><pre>' . $exception->getTraceAsString() . '</pre></div>';
            }
            echo <<<EOL
<style>
._wrapDebug{min-width:590px; margin:20px; padding:10px; font-size:14px; border:1px solid #000;}
._wrapDebug span{color:#121E31; font-size:14px;}
._wrapDebug font:first{color:green; font-size:14px;}
._wrapDebug font:last{color:red; font-size:14px;}
._wrapDebug pre{font-size:14px;}
._wrapDebug p{background:#92E287;}
._wrapDebug a{margin-left:20px;}
</style>
<div class="_wrapDebug">================= 新的调试点： 
    <span>$debugCount</span> ========================<br />
    <font>$file</font> 第 $line 行<br />
    <font>$code</font><br />
    调试输出内容:<br />
    类型：$dataType<br />
    字符串长度：$length<br />
    值:<br />
    <pre><p>$showData</p></pre>
    $backLink
    <a href="javascript:location.reload">重新请求本页</a>
    $traceHtml
</div>
EOL;
        }else{
            $traceContent = '';
            if($mode == 111 || $mode == 110){
                $traceContent = $exception->getTraceAsString();
            }
            $debugContent = <<<EOL
============ 新的调试点：$debugCount ============<br />
$file:$line
$code
data type: $dataType
string length: $length
value:
$showData

$traceContent
EOL;
            echo $debugContent;
        }
    }

    ($mode == 11 || $mode == 111) && exit;
}
```

最后我还记得有些情况是不能只靠输出来调试的，而是需要把内容写到文件中来看文件内容

我想过要不要把调试函数改造成支持写入文件内容的呢？

最后想了想还是算了，真有那种情况就慢慢写一下文件读写代码吧，毕竟也不是很经常的事情


## [PHP国产全文搜索引擎讯搜](https://fddcn.cn/php-xunsearch.html)

最近要做全文搜索，以前并没有这方面的经验，于是摸索着用了讯搜。

安装：

1. 运行下面指令下载、解压安装包  
```
wget http://www.xunsearch.com/download/xunsearch-full-latest.tar.bz2
tar -xjf xunsearch-full-latest.tar.bz2
```

1. 执行安装脚本，根据提示进行操作，主要是输入 xunsearch 软件包的安装目录，强烈建议单独 规划一个目录，而不是混到别的软件目录中。  

```
    cd xunsearch-full-1.3.0/
    sh setup.sh
```
  
第一次安装的话，过程可能会稍显漫长，请不必着急，您大可泡杯茶一边喝一边等待即可。
1. 待命令运行结束后，如果没有出错中断，则表示顺利安装完成，然后就可以启动/重新启动 xunsearch 的后台服务，下面命令中的 $prefix 请务必替换为您的安装目录，而不是照抄。  

    cd $prefix ; bin/xs-ctl.sh restart

  
强烈建议您将此命令添加到开机启动脚本中，以便每次服务器重启后能自动启动搜索服务程序， 在 Linux 系统中您可以将脚本指令写进 /etc/rc.local 即可。
1. 有必要指出的是，关于搜索项目的数据目录规划。搜索系统将所有数据保存在 $prefix/data 目录中。 如果您希望数据目录另行安排或转移至其它分区，请将 $prefix/data 作为软链接指向真实目录

2. 安装 PHP-SDK

PHP-SDK 的代码不需要另行下载，已经包含在 xunsearch 的安装结果中了，在此假设您将 xunsearch 安装在$prefix 目录，那么 $prefix/sdk/php 即是 PHP-SDK 的代码目录。目录结构及功能逻列如下：

```
_
|- doc/                    离线 HTML 版相关文档
|- app/                    用于存放搜索项目的 ini 文件
|- lib/XS.php              入口文件，所有搜索功能必须且只需包含此文件    
\- util/                   辅助工具程序目录
    |- RequireCheck.php    用于检测您的 PHP 环境是否符合运行条件
    |- IniWizzaard.php     用于帮助您编写 xunsearch 项目配置文件
    |- Quest.php           搜索测试工具
    \- Indexer.php         索引管理工具
```

如果您的搜索应用程序和 xunsearch 在同一台服务器，则无需复制任何代码，在开发的时候直接包含 入口文件$prefix/sdk/php/lib/XS.php 即可。代码如下：

``` 
<span class="php-hl-reserved">require_once</span> <span class="php-hl-quotes">'</span><span class="php-hl-string">$prefix/sdk/php/lib/XS.php</span><span class="php-hl-quotes">'</span><span class="php-hl-code">;</span>
```

如果您在其它服务器部署前端搜索代码，请将 SDK 代码整个目录复制到相应的服务器上，但并不要求放到 web 可访问目录，考虑安全性也不推荐这么做。

3. 检测 PHP-SDK 的运行条件从现在开始的文档及示范代码中我们都假定您将 xunsearch 安装在 $prefix 目录中，而不再另行说明。

基础运行条件要求 PHP 最低版本为 5.2.0，随着功能需求的不同可能还会用到一些其它扩展，具体请在 命令行环境里运行我们提供的检测脚本。如果您的 php 可执行文件不在默认搜索路径中，假设是安装在 /path/to/bin/php请使用第二种方式运行。运行方式如下：

```
1. $prefix/sdk/php/util/RequiredCheck.php
2. /path/to/bin/php $prefix/sdk/php/util/RequiredCheck.php
3. $prefix/sdk/php/util/RequiredCheck.php -c gbk
```
运行结果输出的中文编码默认为 UTF-8 ，如果您使用 GBK 环境请在运行命令最后加上 -c GBK 。 运行结果会给出一个可视化的表格说明检测结果，并在最终给出检测结论，告诉您是否符合运行的基础要求。

至此，安装和准备工作已经完成了，您可以开始使用 _Xunsearch PHP-SDK_ 开发自己的搜索应用了。

示例搜索代码：

```php
<?php
/**
 * search.php 
 * 搜索项目入口文件
 * 创建时间：2015-08-04 09:51:30
 * 默认编码：UTF-8
 */
// 加载 XS 入口文件
require_once '/home/wang/xunsearch/sdk/php/lib/XS.php';
error_reporting(E_ALL ^ E_NOTICE);
 
//
// 支持的 GET 参数列表
// q: 查询语句
// m: 开启模糊搜索，其值为 yes/no
// f: 只搜索某个字段，其值为字段名称，要求该字段的索引方式为 self/both
// s: 排序字段名称及方式，其值形式为：xxx_ASC 或 xxx_DESC
// p: 显示第几页，每页数量为 XSSearch::PAGE_SIZE 即 10 条
// ie: 查询语句编码，默认为 UTF-8
// oe: 输出编码，默认为 UTF-8
//
// variables
$eu = '';
$__ = array('q', 'm', 'f', 's', 'p', 'ie', 'oe', 'syn');
foreach ($__ as $_) {
    $$_ = isset($_GET[$_]) ? $_GET[$_] : '';
}
// input encoding
if (!empty($ie) && !empty($q) && strcasecmp($ie, 'UTF-8')) {
    $q = XS::convert($q, $cs, $ie);
    $eu .= '&ie=' . $ie;
}
 
// output encoding
if (!empty($oe) && strcasecmp($oe, 'UTF-8')) {
 
    function xs_output_encoding($buf)
    {
        return XS::convert($buf, $GLOBALS['oe'], 'UTF-8');
    }
    ob_start('xs_output_encoding');
    $eu .= '&oe=' . $oe;
} else {
    $oe = 'UTF-8';
}
 
// recheck request parameters
$q = get_magic_quotes_gpc() ? stripslashes($q) : $q;
$f = empty($f) ? '_all' : $f;
${'m_check'} = ($m == 'yes' ? ' checked' : '');
${'syn_check'} = ($syn == 'yes' ? ' checked' : '');
${'f_' . $f} = ' checked';
${'s_' . $s} = ' selected';
 
// base url
$bu = $_SERVER['SCRIPT_NAME'] . '?q=' . urlencode($q) . '&m=' . $m . '&f=' . $f . '&s=' . $s . $eu;
 
// other variable maybe used in tpl
$count = $total = $search_cost = 0;
$docs = $related = $corrected = $hot = array();
$error = $pager = '';
$total_begin = microtime(true);
 
// perform the search
try {
    $xs = new XS('yeb');
    $search = $xs->search;
    $search->setCharset('UTF-8');
 
    if (empty($q)) {
        // just show hot query
        $hot = $search->getHotQuery();
    } else {
        // fuzzy search
        $search->setFuzzy($m === 'yes');
 
        // synonym search
        $search->setAutoSynonyms($syn === 'yes');
 
        // set query
        if (!empty($f) && $f != '_all') {
            $search->setQuery($f . ':(' . $q . ')');
        } else {
            $search->setQuery($q);
        }
 
        // set sort
        if (($pos = strrpos($s, '_')) !== false) {
            $sf = substr($s, 0, $pos);
            $st = substr($s, $pos + 1);
            $search->setSort($sf, $st === 'ASC');
        }
 
        // set offset, limit
        $p = max(1, intval($p));
        $n = XSSearch::PAGE_SIZE;
        //echo $n;
        $search->setLimit($n, ($p - 1) * $n);
 
        // get the result
        $search_begin = microtime(true);
        $docs = $search->search();
        $search_cost = microtime(true) - $search_begin;
 
        // get other result
         $count = $search->getLastCount();
         $total = $search->getDbTotal();
 
        if ($xml !== 'yes') {
            // try to corrected, if resul too few
            if ($count < 1 || $count < ceil(0.001 * $total)) {
                $corrected = $search->getCorrectedQuery();
            }
            // get related query
            $related = $search->getRelatedQuery();
        }
 
        // gen pager
        if ($count > $n) {
            $pb = max($p - 5, 1);
            $pe = min($pb + 10, ceil($count / $n) + 1);
            $pager = '';
            do {
                $pager .= ($pb == $p) ? '<li class="disabled"><a>' . $p . '</a></li>' : '<li><a href="' . $bu . '&p=' . $pb . '">' . $pb . '</a></li>';
            } while (++$pb < $pe);
        }
    }
} catch (XSException $e) {
    $error = strval($e);
}
 
// calculate total time cost
$total_cost = microtime(true) - $total_begin;
 
//var_dump($docs);
$ii=0;
foreach ($docs as $key ) {
    
    $ans[$ii]['pid'] = $key->pid;
    $ans[$ii]['message'] = $key->message;
    $ans[$ii++]['subject'] = $key->subject;
    $ans['num'] = $ii;
}
echo json_encode($ans);
// output the data
//include dirname(__FILE__) . '/search.tpl';
```
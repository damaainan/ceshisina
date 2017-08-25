<?php

/**
 * @author jiang kejun <jinhua_k9@163.com>
 * @name KMP (Knuth-Morris-Pratt算法) for PHP5
 * @since 2013.5.8
 * @link http://developer.51cto.com/art/201305/392555.htm
 * @version $Id: kmp.php 155 2013.5.8 jkj $
 */
class Kmp
{
    /**
     * 需查找字符串
     * @var string
     */
    public $_n;
    /**
     * 在这里面找
     * @var string
     */
    public $_w;

    public function __construct($n, $w)
    {
        $this->_n = $n;
        $this->_w = $w;
    }

    /**
     * 输出匹配成功信息
     * @return void output
     */
    public function result()
    {
        $int = $this->firstMatchPoi();
        while ($int > 0) {
            $index = $int;
            $int   = $this->nextMatchPoi($int);
            if ($int == 0) {
                echo "找到了，起始索引为$index!";
                break;
            }
            if ($int == -1) {
                echo "未找到!";
                break;
            }
        }
    }

    /**
     * 第一个匹配点
     *
     * @return int
     */
    public function firstMatchPoi()
    {
        return strpos($this->_w, substr($this->_n, 0, 1));
    }

    /**
     * 返回下个匹配点的索引值
     *
     * @param int $int 当前匹配点
     * @return int $error
     */
    public function nextMatchPoi($int)
    {
        $tmp   = '';
        $error = 0;
        for ($i = 0; $i < strlen($this->_n); $i++) {
            if (substr($this->_w, ($int + $i), 1) == $this->_n{$i}) {
                $tmp .= $this->_n{$i};
                //echo "匹配成功，现在为$tmp<br/>";
            } else {
                // 最后一个匹配字符对应的"部分匹配值"
                $ws    = strlen($tmp) - $this->getMatchTab($tmp);
                $error = $int + $ws;
                if (substr($this->_w, $error, 1) != substr($this->_n, 0, 1)) {
                    $error += 1;
                }
                break;
            }
        }
        // 及时结束
        if ($error > (strlen($this->_w) - strlen($this->_n))) {
            $error = -1;
        }
        return $error;
    }

    /**
     * 部分匹配表 (Partial Match Table)
     *
     * example:
     *      A B C D A B D
     *      0 0 0 0 1 2 0
     * @param string $ss 临时字符
     * @param bool $last 是否返回最后匹配表数字
     * @return array
     */
    public function getMatchTab($ss, $last = true)
    {
        $p     = $this->_prefix($ss);
        $s     = $this->_suffix($ss);
        $match = array();
        foreach ($p as $pkey => $pval) {
            $match[$pkey] = 0;
            foreach ($pval as $key => $val) {
                if (in_array($val, $s[$pkey])) {
                    $match[$pkey] = strlen($val);
                }
            }
        }
        return ($last == true ? end($match) : $match);
    }

    /**
     * 前缀表
     *
     * @param string $s
     * @return array
     */
    final private function _prefix($s)
    {
        $ss     = '';
        $prefix = array();
        $tmp    = '';
        for ($i = 0; $i < strlen($s); $i++) {
            $ss .= $s{$i};
            for ($j = 0; $j < strlen($ss); $j++) {
                $prefix[$i][$j] = $tmp;
                $tmp .= $ss{$j};
            }
            $tmp = '';
        }
        return $prefix;
    }

    /**
     * 后缀表
     *
     * @param string $s
     * @return array
     */
    final private function _suffix($s)
    {
        $ss           = '';
        $suffix       = array();
        $suffix[0][0] = '';
        for ($i = 1; $i < strlen($s); $i++) {
            $ss .= substr($s, $i, 1);
            for ($j = 0; $j < strlen($ss); $j++) {
                $suffix[$i][$j] = substr($ss, $j);
            }
        }
        return $suffix;
    }
}

$sstr = "BBC ABCDAB ABCDABCDABDE";
$rstr = "ABCDABD";
$kmp  = new Kmp($rstr, $sstr);
$kmp->result();

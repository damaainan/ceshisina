## php 获取中文拼音首字母

来源：[http://blog.collin2.xyz/index.php/archives/26/](http://blog.collin2.xyz/index.php/archives/26/)

时间 2018-05-23 09:21:02


昨天看同时代码的时候，发现有一段获取中文拼音首字母的代码，发现里面有好几十个if，然后在百度搜索了下，发现全部都是这种写法：

```php
<?php
 
class pingyin {
    
    /**
     * 中文转换首字母
     * @param string $str 需要转换的字符串
     * @return null|string
     */
    public function getFirstChar($str = '')
    {
        if (!$str) {
            return null;
        }
        $fchar = ord($str{0});
        if ($fchar >= ord("A") and $fchar <= ord("z")) {
            return strtoupper($str{0});
        }
        $s = $this->safe_encoding($str);
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 and $asc <= -20284) {
            return "A";
        }
        if ($asc >= -20283 and $asc <= -19776) {
            return "B";
        }
        if ($asc >= -19775 and $asc <= -19219) {
            return "C";
        }
        if ($asc >= -19218 and $asc <= -18711) {
            return "D";
        }
        if ($asc >= -18710 and $asc <= -18527) {
            return "E";
        }
        if ($asc >= -18526 and $asc <= -18240) {
            return "F";
        }
        if ($asc >= -18239 and $asc <= -17923) {
            return "G";
        }
        if ($asc >= -17922 and $asc <= -17418) {
            return "H";
        }
        if ($asc >= -17417 and $asc <= -16475) {
            return "J";
        }
        if ($asc >= -16474 and $asc <= -16213) {
            return "K";
        }
        if ($asc >= -16212 and $asc <= -15641) {
            return "L";
        }
        if ($asc >= -15640 and $asc <= -15166) {
            return "M";
        }
        if ($asc >= -15165 and $asc <= -14923) {
            return "N";
        }
        if ($asc >= -14922 and $asc <= -14915) {
            return "O";
        }
        if ($asc >= -14914 and $asc <= -14631) {
            return "P";
        }
        if ($asc >= -14630 and $asc <= -14150) {
            return "Q";
        }
        if ($asc >= -14149 and $asc <= -14091) {
            return "R";
        }
        if ($asc >= -14090 and $asc <= -13319) {
            return "S";
        }
        if ($asc >= -13318 and $asc <= -12839) {
            return "T";
        }
        if ($asc >= -12838 and $asc <= -12557) {
            return "W";
        }
        if ($asc >= -12556 and $asc <= -11848) {
            return "X";
        }
        if ($asc >= -11847 and $asc <= -11056) {
            return "Y";
        }
        if ($asc >= -11055 and $asc <= -10247) {
            return "Z";
        }

        return null;
    }

    /**
     * 编码判断
     * @param string $string 需要验证的字符串
     * @return string
     */
    public function safe_encoding($string)
    {
        $encoding = "UTF-8";
        for ($i = 0; $i < strlen($string); $i++) {
            if (ord($string{$i}) < 128) {
                continue;
            }
            if ((ord($string{$i}) & 224) == 224) {
                // 第一个字节判断通过
                $char = $string{++$i};
                if ((ord($char) & 128) == 128) {
                    // 第二个字节判断通过
                    $char = $string{++$i};
                    if ((ord($char) & 128) == 128) {
                        $encoding = "UTF-8";
                        break;
                    }
                }
            }
            if ((ord($string{$i}) & 192) == 192) {
                // 第一个字节判断通过
                $char = $string{++$i};
                if ((ord($char) & 128) == 128) {
                    // 第二个字节判断通过
                    $encoding = "GB2312";
                    break;
                }
            }
        }
        if (strtoupper($encoding) == strtoupper($this->_outEncoding)) {
            return $string;
        } else {
            return iconv($encoding, $this->_outEncoding, $string);
        }
    }
}
```

而且每篇博客内容都一摸一样，这不得不吐槽下，一直去转载别人文章有啥意思。。。。上面那段代码整的我的强迫症犯了，于是我用二分查找写了个简单方式：

```php
<?php

class pingyin
{
    const ASCII_CODE = [
        176161 => 'A',
        176197 => 'B',
        178193 => 'C',
        180238 => 'D',
        182234 => 'E',
        183162 => 'F',
        184193 => 'G',
        185254 => 'H',
        187247 => 'J',
        191166 => 'K',
        192172 => 'L',
        194232 => 'M',
        196195 => 'N',
        197182 => 'O',
        197190 => 'P',
        198218 => 'Q',
        200187 => 'R',
        200246 => 'S',
        203250 => 'T',
        205218 => 'W',
        206244 => 'X',
        209185 => 'Y',
        212209 => 'Z',
    ];


    public function getFirstChar(string $str) {
        if (empty($str)) {
            return '';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;

        $code = ord($s{0}) * 1000 + ord($s{1});

        $keys = array_keys(self::ASCII_CODE);
        if ($code < $keys[0]) return '';

        $start = 0;
        $end = count($keys)-1;

        while (true) {
            $center = (int) round(($start + $end) / 2);
            if ($start > $end) return self::ASCII_CODE[$keys[$start-1]];
            if ($keys[$center] == $code) return self::ASCII_CODE[$keys[$center]];
            
            if ($keys[$center] < $code) 
                $start = $center + 1;
            else
                $end = (int) $center -1;
        }
    }
}


$a = new pingyin();
$str = $argv[1];
$str_array =  preg_split('/(?<!^)(?!$)/u', $str);
for ($i = 0; $i < count($str_array); $i++) {
    echo $str_array[$i],$a->getFirstChar($str_array[$i]), PHP_EOL, PHP_EOL;
}


// echo $a->getFirstChar($argv[1]), PHP_EOL;
```



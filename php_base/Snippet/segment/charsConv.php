<?php
/** 字符编码转换类, ANSI、Unicode、Unicode big endian、UTF-8、UTF-8+Bom互相转换

 * Func:
 * public convert 转换
 * private convToUtf8 把编码转为UTF-8编码
 * private convFromUtf8 把UTF-8编码转换为输出编码
 */
class CharsetConv
{
    // class start
    private $_in_charset    = null; // 源编码
    private $_out_charset   = null; // 输出编码
    private $_allow_charset = array('utf-8', 'utf-8bom', 'ansi', 'unicode', 'unicodebe');
    /** 初始化
     * @param String $in_charset 源编码
     * @param String $out_charset 输出编码
     */
    public function __construct($in_charset, $out_charset)
    {
        $in_charset  = strtolower($in_charset);
        $out_charset = strtolower($out_charset);
        // 检查源编码
        if (in_array($in_charset, $this->_allow_charset)) {
            $this->_in_charset = $in_charset;
        }
        // 检查输出编码
        if (in_array($out_charset, $this->_allow_charset)) {
            $this->_out_charset = $out_charset;
        }

    }

    /** 转换
     * @param String $str 要转换的字符串
     * @return String 转换后的字符串
     */
    public function convert($str)
    {
        $str = $this->convToUtf8($str); // 先转为utf8
        $str = $this->convFromUtf8($str); // 从utf8转为对应的编码
        return $str;
    }
    /** 把编码转为UTF-8编码
     * @param String $str
     * @return String
     */
    private function convToUtf8($str)
    {
        if ($this->_in_charset == 'utf-8') {
            // 编码已经是utf-8，不用转
            return $str;
        }
        switch ($this->_in_charset) {
            case 'utf-8bom':
                $str = substr($str, 3);
                break;
            case 'ansi':
                $str = iconv('GBK', 'UTF-8//IGNORE', $str);
                break;
            case 'unicode':
                $str = iconv('UTF-16le', 'UTF-8//IGNORE', substr($str, 2));
                break;
            case 'unicodebe':
                $str = iconv('UTF-16be', 'UTF-8//IGNORE', substr($str, 2));
                break;
            default:
                break;
        }
        return $str;
    }
    /** 把UTF-8编码转换为输出编码
     * @param String $str
     * @return String
     */
    private function convFromUtf8($str)
    {

        if ($this->_out_charset == 'utf-8') {
            // 输出编码已经是utf-8，不用转
            return $str;
        }

        switch ($this->_out_charset) {
            case 'utf-8bom':
                $str = "\xef\xbb\xbf" . $str;
                break;

            case 'ansi':
                $str = iconv('UTF-8', 'GBK//IGNORE', $str);
                break;

            case 'unicode':
                $str = "\xff\xfe" . iconv('UTF-8', 'UTF-16le//IGNORE', $str);
                break;

            case 'unicodebe':
                $str = "\xfe\xff" . iconv('UTF-8', 'UTF-16be//IGNORE', $str);
                break;

            default:
                break;
        }
        return $str;

    }

} // class end


// $str = file_get_contents('source/unicodebe.txt');
$str = "";

$obj = new CharsetConv('unicodebe', 'utf-8bom');//将Unicode big endian 转化为utf-8带bom
$response = $obj->convert($str);

file_put_contents('response/utf-8bom.txt', $response, true);
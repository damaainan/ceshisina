<?php

header("Content-type:text/html; Charset=utf-8");


// Java Des加密 php版

class DesClass
{
    public $key;
    public function desClass($key)
    {
        $this->key = $key;
    }
    public function encrypt($input)
    {
        $size  = mcrypt_get_block_size('des', 'ecb');
        $input = $this->pkcs5Pad($input, $size);
        $key   = $this->key;
        $td    = mcrypt_module_open('des', '', 'ecb', '');
        $iv    = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = $this->byteArr2HexStr($this->getBytes($data));
        return $data;
    }
    public function decrypt($encrypted)
    {
        $encrypted = $this->array2str($this->hexStr2ByteArr($encrypted));
        $key       = $this->key;
        $td        = mcrypt_module_open('des', '', 'ecb', '');
        //使用MCRYPT_DES算法,cbc模式
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        //初始处理
        $decrypted = mdecrypt_generic($td, $encrypted);
        //解密
        mcrypt_generic_deinit($td);
        //结束
        mcrypt_module_close($td);
        $y = $this->pkcs5Unpad($decrypted);
        return $y;
    }
    public function pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    public function pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }

        return substr($text, 0, -1 * $pad);
    }

/**
 * 将字符串转换为ASCII码值数组，和array2str 互为可逆的转换过程
 *
 * @param string需要转换的字符串
 * @return 转换后的ASCII码值数组
 */
    public function getBytes($string)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($string); $i++) {
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }
/**
 * 将ASCII码值数组转换为字符串，和getBytes 互为可逆的转换过程
 *
 * @param $array需要转换的ASCII码值数组
 * @return 转换后的字符串
 */
    public function array2str($array)
    {
        $string = '';
        foreach ($array as $key => $value) {
            $string .= chr($value);
        }
        return $string;

    }

/**
 * 将数组转换为表示16进制值的字符串，和hexStr2ByteArr(String strIn) 互为可逆的转换过程
 *
 *
 * @param array需要转换的byte数组
 * @return 转换后的字符串
 */
    public function byteArr2HexStr($array)
    {
        $iLen       = count($array);
        $return_str = '';
        for ($i = 0; $i < $iLen; $i++) {
            $intTmp = $array[$i];
            // 把负数转换为正数
            while ($intTmp < 0) {
                $intTmp = $intTmp + 256;
            }

            $intTmp = dechex($intTmp);
            // 小于0F的数需要在前面补0
            if (hexdec($intTmp) < 16) {
                $intTmp = '0' . $intTmp;
            }
            $return_str = $return_str . $intTmp;
        }

        return $return_str;
    }

/**
 * 将表示16进制值的字符串转换为数组， 和byteArr2HexStr互为可逆的转换过程
 *
 * @param $string 需要转换的字符串
 * @return 转换后的数组
 */
    public function hexStr2ByteArr($string)
    {
        $len = strlen($string);

        $return = array();
        for ($i = 0; $i < $len; $i = $i + 2) {
            $return[] = hexdec(substr($string, $i, 2));
        }

        return $return;
    }
}

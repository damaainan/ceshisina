<?php

/**
 * User: rudy
 * Date: 2016/02/15 15:45
 *
 *  处理图片相关类
 *
 */
class Image{
    /**
     * 根据url获取远程图片的的基本信息
     * @param $path_url String 需要获取的图片的url地址
     * @return array|bool false-出错，否则返回基本信息数字
     */
    public static function getOnlineImageInfo($path_url){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$path_url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_AUTOREFERER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($ch,CURLOPT_ENCODING,'gzip,deflate');
        if(preg_match('/^https.*/',$path_url)){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
        }
        $content=curl_exec($ch);
        curl_close($ch);

        if($content == false){
            return false;
        }
        $info = array();
        $info['img_url'] = $path_url;
        $info['size'] = strlen($content);
        $info['md5'] = md5($content);
        $size = @getimagesizefromstring($content);
        if ($size) {
            $info['width'] = $size[0];
            $info['height'] = $size[1];
            $info['type'] = $size[2];
            $info['mime'] = $size['mime'];
        } else {
            return false;
        }
        return $info;
    }
}


//测试
if(strtolower(PHP_SAPI) == 'cli' && basename(__FILE__) == basename($argv[0])){
    $img_info = Image::getOnlineImageInfo('http://www.phpernote.com/images/logo.gif');
    print_r($img_info);
}
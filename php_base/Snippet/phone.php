<?php 

header("Content-type:text/html; Charset=utf-8");
//一种 PHP 判断设备是否是手机/平板的方法

//在做WEB开发的时候经常会需要用到对移动设备的页面匹配，当然可以直接把网站做成响应式的，但如果不想这么做的话，可以使用PHP对设备类型进行判断，然后显示相应的界面和内容。今天分享一种使用 PHP 判断设备是否是手机/平板的方法，方法来源于WordPress(wp-includes/vars.php:125)，适用于大部分类型的手机/平板判断：

/**
 * Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
 *
 * @staticvar bool $is_mobile
 *
 * @return bool
 */
function wp_is_mobile() {
	static $is_mobile = null;
 
	if ( isset( $is_mobile ) ) {
		return $is_mobile;
	}
 
	if ( empty($_SERVER['HTTP_USER_AGENT']) ) {
		$is_mobile = false;
	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false ) {
			$is_mobile = true;
	} else {
		$is_mobile = false;
	}
 
	return $is_mobile;
}
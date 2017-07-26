<?php 

header("Content-type:text/html; Charset=utf-8");

/**
 *
 *
 *
 *
 * 
 */

//代理抽象接口
interface shop{
    public function buy($title);
}
//原来的CD商店，被代理对象
class CDshop implements shop{
    public function buy($title){
        echo "购买成功，这是你的《{$title}》唱片".PHP_EOL;
    }
}
//CD代理
class Proxy implements shop{
    public function buy($title){
        $this->go();
        $CDshop = new CDshop;
        $CDshop->buy($title);
    }
    public function go(){
        echo "跑去香港代购".PHP_EOL;
    }
}

//你93年买了张 吻别
$CDshop = new CDshop;
$CDshop->buy("吻别");
//14年你想买张 醒着做梦 找不到CD商店了，和做梦似的，不得不找了个代理去香港帮你代购。
$proxy = new Proxy;
$proxy->buy("醒着做梦");
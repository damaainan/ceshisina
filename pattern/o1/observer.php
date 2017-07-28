<?php
 
//LoginSubject
 
class LoginSubject implements SplSubject{
 
    //观察者列表
 
    public $observers,$value,$hobby,$address;
 
    //初始化变量
 
    public function __construct(){
        //sqlObjectStorage是一个类，专门用来存储内容，观察者列表就是存在此类
        $this->observers = new SplObjectStorage();
    }
 
    //登录
 
    public function login(){
        //登录过程,省略
        $this->notify();
 
    }
 
    //添加观察者
 
    public function attach(SplObserver $observer){
 
        $this->observers->attach($observer);
 
    }
 
    //剔除观察者
 
    public function detach(SplObserver $observer){
        $this->observers->detach($observer);
    }
 
    //登陆后通知notify
 
    public function notify(){
 
        $observers = $this->observers;
 
        //这段rewind不可或缺... 将节点指针指向第一位节点
 
        $observers->rewind();
 
        //当前节点存在
 
            while($observers->valid()){
                $observer = $observers->current();//获取当前节点（即观察者）
                $observer->update($this);//进行update犯法操作
                $observers->next();//next 节点
            }
 
    }
 
}
 
//observer User1Observers
 
class User1Observers implements SplObserver {
 
    public function update(SplSubject $subject){
        echo '我是一级用户，请给我对应的一级服务';
    }
 
}
 
//observer User2Observers
 
class User2Observers implements SplObserver {
 
    public function update(SplSubject $subject){
        echo '我是二级用户，请给我对应的二级服务';
    }
 
}
 
//observer CommenUserObservers
 
class CommenUserObservers implements SplObserver {
 
    public function update(SplSubject $subject){
        echo '我是普通用户，请给我对应的普通服务';
    }
 
}
 
//如果需要的话可以继续增加或者减少用户等级，丝毫不会影响原本的等级用户
 
$subject = new LoginSubject();
$CommenUserObservers = new CommenUserObservers;//普通用户
$subject->attach(new User1Observers);//增加观察者：一级用户
$subject->attach(new User2Observers);//增加观察者：二级用户
$subject->attach($CommenUserObservers);//增加观察者：普通用户
$subject->login();//登录，触发notify

//output:我是一级用户，请给我对应的一级服务我是二级用户，请给我对应的二级服务我是普通用户，请给我对应的普通服务

echo '<br/>';

//如果有一天普通用户压根没有对应的服务了，那么我们就可以剔除它了
//$subject->detach(new CommenUserObservers); 无效

$subject->detach($CommenUserObservers);//删除观察者：普通用户
$subject->login();//登录，触发notify，普通用户就不会被通知啦

//output：我是一级用户，请给我对应的一级服务我是二级用户，请给我对应的二级服务
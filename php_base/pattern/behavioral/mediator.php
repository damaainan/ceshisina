<?php 
/**
 * 中介模式
========

现实例子
> 一个普遍的例子是当你用手机和别人谈话，你和别人中间隔了一个电信网，你的声音穿过它而不是直接发出去。在这里，电信网就是一个中介。

白话
> 中介模式增加了一个第三方对象（叫做中介）来控制两个对象（叫做同事）间的交互。它帮助减少类彼此之间交流的耦合度。因为它们现在不需要知道彼此的实现。
 */



// 中介
class ChatRoom implements ChatRoomMediator {   //ChatRoomMediator  接口不存在
    public function showMessage(User $user, string $message) {
        $time = date('M d, y H:i');
        $sender = $user->getName();

        echo $time . '[' . $sender . ']:' . $message;
    }
}


class User {
    protected $name;
    protected $chatMediator;

    public function __construct(string $name, ChatRoomMediator $chatMediator) {
        $this->name = $name;
        $this->chatMediator = $chatMediator;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function send($message) {
        $this->chatMediator->showMessage($this, $message);
    }
}


$mediator = new ChatRoom();

$john = new User('John Doe', $mediator);
$jane = new User('Jane Doe', $mediator);

$john->send('Hi there!');
$jane->send('Hey!');

// 输出将会是
// Feb 14, 10:58 [John]: Hi there!
// Feb 14, 10:58 [Jane]: Hey!
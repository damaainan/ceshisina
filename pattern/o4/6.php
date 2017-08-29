<?php
class Info
{

    public $lev; //发送等级：普通,重要,特级

    public $target; //通过什么方式发送

    //实际发送方法

    public function Sending($to, $content)
    {

        //先把消息等级确定了

        $content = $this->lev->msg($content);

        $target = $this->target->send($to);

        return $target . $content;

    }

}

//普通消息

class CommonInfo
{

    public function msg($content)
    {

        return '普通消息:' . $content;

    }

}

//重要消息

class ImportInfo
{

    public function msg($content)
    {

        return '重要消息:' . $content;

    }

}

//特别消息

class SpecialInfo
{

    public function msg($content)
    {

        return '特别消息:' . $content;

    }

}

//站内发送方式

class ZnSend
{

    public function send($to)
    {

        return '站内发给' . $to;

    }

}

//QQ发送方式

class QQSend
{

    public function end($to)
    {

        return 'QQ发给' . $to;

    }

}

//Email发送方式

class EmailSend
{

    public function send($to)
    {

        return '邮箱发给' . $to;

    }

}

$info = new Info(); //实例化桥接类

$info->target = new ZnSend(); //实例化发送方式

$info->lev = new CommonInfo(); //实例化消息等级

print_r($info->Sending('小明', '回家吃饭')); //调用桥接类方法Sending，让ZnSend类和CommonInfo类结合

//output：站内发给小明普通消息:回家吃饭

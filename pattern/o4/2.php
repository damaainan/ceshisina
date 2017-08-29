<?php

//设定银行工作接口

interface Bankwork
{

    public function work();

}

//存款职员

class Depositer implements Bankwork
{

    public function work()
    {

        return '开始存款';

    }

}

//销售职员

class Marketer implements bankwork
{

    public function work()
    {

        return '开始销售';

    }

}

//接待职员

class Receiver implements Bankwork
{

    public function work()
    {

        return '开始接待';

    }

}

//客户端调用接口类

class Client
{

    public function working($type)
    {

        switch ($type) {

            case '存款职员':

                $man = new Depositer;

                break;

            case '销售':

                $man = new Marketer;

                break;

            case '接待':

                $man = new Receiver;

                break;

            default:

                echo '传输参数有误，不属于任何一个职位';

                break;

        }

        return $man->work();

    }

}

$bankstaff = new Client();

print_r($bankstaff->working('接待')); // output :开始接待

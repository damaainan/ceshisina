<?php

class JsonData
{

    public function show()
    {

        $array = array('风扇', '书', '台灯');

        return json_encode($array);

    }

}

//序列化数据

class SerializeData extends JsonData
{

    public function show()
    {

        $data = parent::show();

        $data = json_decode($data);

        return serialize($data);

    }

}

//对数据进行base64编码

class Base64Data extends JsonData
{

    public function show()
    {

        $data = parent::show();

        return base64_encode($data);

    }

}

//XML extends OriginalData ...

$data = new SerializeData();

print_r($data->show()); //输出:a:3:{i:0;s:6:"风扇";i:1;s:3:"书";i:2;s:6:"台灯";}

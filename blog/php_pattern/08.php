<?php 

header("Content-type:text/html; Charset=utf-8");

/**
 *
 *
 *
 *
 * 
 */

//医院医生员工系统
class DoctorSystem{

    //通知就诊医生
    static public function getDoctor($name){
        echo __CLASS__.":".$name."医生，挂你号".PHP_EOL;
        return new Doctor($name);
    }
}
//医生类
class Doctor{
    public $name;
    public function __construct($name){
        $this->name = $name;
    }
    public function prescribe($data){
        echo __CLASS__.":"."开个处方给你".PHP_EOL;
        return "祖传秘方，药到必死";
    }
}
//患者系统
class SufferSystem {
    static function getData($suffer){
        $data = $suffer."资料";
        echo  __CLASS__.":".$suffer."的资料是这些".PHP_EOL ;
        return  $data;
    }
}
//医药系统
class MedicineSystem {
    static function register($prescribe){
        echo __CLASS__.":"."拿到处方：".$prescribe."------------通知药房发药了".PHP_EOL;
        Shop::setMedicine("砒霜5千克");
    }
}
//药房
class shop{
    static public $medicine;
    static function setMedicine($medicine){
        self::$medicine = $medicine;
    }
    static function getMedicine(){
        echo __CLASS__.":".self::$medicine.PHP_EOL;
    }
}

//如果没有挂号系统，我们就诊的第一步
//通知就诊医生
$doct = DoctorSystem::getDoctor("顾夕衣");
//患者系统拿病历资料
$data = SufferSystem::getData("何在");
//医生看病历资料，开处方
$prscirbe = $doct->prescribe($data);
//医药系统登记处方
MedicineSystem::register($prscirbe);
//药房拿药
Shop::getMedicine();

echo PHP_EOL.PHP_EOL."--------有了挂号系统以后--------".PHP_EOL.PHP_EOL;

//挂号系统
class Facade{
    static public function regist($suffer,$doct){
        $doct = DoctorSystem::getDoctor($doct);
        //患者系统拿病历资料
        $data = SufferSystem::getData($suffer);
        //医生看病历资料，开处方
        $prscirbe = $doct->prescribe($data);
        //医药系统登记处方
        MedicineSystem::register($prscirbe);
        //药房拿药
        Shop::getMedicine();
    }
}
//患者只需要挂一个号，其他的就让挂号系统去做吧。
Facade::regist("叶好龙","贾中一");
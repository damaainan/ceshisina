<?php 
// https://www.mengzhidu.com/online/item/4/49

//定义用户类
interface User{
    public function  getName();
}
//定义学生类
class Student implements User{
    public function getName(){
        echo "我是学生\r\n";
    }
}
//定义老师类
class  Teacher  implements  User{
    public function getName(){
        echo "我是老师\r\n";
    }
}

//定义工厂类
class  SimpleFactory{
    //获取对象
    static public function  get($name){
        if($name == 'student'){
            $obj = new Student();
        }elseif($name == 'teacher'){
            $obj = new Teacher();
        }else{
            $obj = null;
        }
        return $obj;
    }
}

//客户端类
class Test {
    public function run(){
        //获取一个老师对象
        $obj = SimpleFactory::get('student');
        $obj->getName();
    }
}


$t = new Test();
$t->run();
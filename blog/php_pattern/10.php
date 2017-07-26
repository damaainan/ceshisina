<?php 

header("Content-type:text/html; Charset=utf-8");

/**
 *
 *
 *
 *
 * 
 */

class Manager{
    public $name;
    protected $c_nodes = array();//存放子节点，部门经理，普通员工等
    public function __construct($name){
        $this->name = $name;
    }
    //添加部门经理
    public function addGm(GM $gm){
        $this->c_nodes[] = $gm;
    }
    //添加普通员工
    public function addStaff(Staff $staff){
        $this->c_nodes[] = $staff;
    }
    //获取全部子节点
    public function get_C_nodes(){
        return $this->c_nodes;
    }
}

//部门经理 就用general manager 简写 GM
Interface Gm{
    public function add(Staff $staff);
    public function get_c_nodes();
}
//销售经理
class Sgm implements Gm{
    public $name;
    protected $c_nodes = array();
    public function __construct($name){
        $this->name = $name;
    }
    //添加员工
    public function add(Staff $staff){
        $this->c_nodes = $staff;
    }
    //获取子节点
    public function get_C_nodes(){
        return $this->c_nodes;
    }
    //区别于其他经理，销售经理有一个销售方法
    public function sell(){
        echo "安利一下我司的产品";
    }
}
//员工接口
Interface staff{
    public function work();
}
//销售部员工
class Sstaff implements staff{
    public $name;
    public function work(){
        echo '在销售经理带领下，安利全世界';
    }
}
//实例化
$manager = new Manager("总经理");
$sgm = new Sgm("销售经理");
$staff = new Sstaff("何在");
//组装成树
$manager->addGm($sgm);
$sgm->add($staff);
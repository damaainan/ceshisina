Uopz 的全称是 User Operations For Zend ，能够在运行时改变PHP的行为，下面是它提供的主要方法：


uopz_function //备份一个方法  
uopz_compose //构建一个类  
uopz_flags //改变类或者方法的Flag定义  
uopz_function //创建一个function  
uopz_overload //覆盖一个VM的操作码（这个后来并没有使用，存在问题，实际使用时通过PHPUnit的基境来实现了这个方法的功能）  
uopz_redefine //创建或者重定义一个常量  
uopz_restore //恢复方法到之前备份的状态   


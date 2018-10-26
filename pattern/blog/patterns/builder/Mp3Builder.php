<?php
namespace builder;

use builder\BuilderInterface;

/**
 * Mp3构建器
 */
class Mp3Builder implements BuilderInterface
{
  /**
   * 名称
   * @var string
   */
  private $_name = '';

  /**
   * 处理器
   * @var string
   */
  private $_cpu = '';

  /**
   * 内存
   * @var string
   */
  private $_ram = '';

  /**
   * 储存
   * @var string
   */
  private $_storage = '';

  /**
   * 系统
   * @var string
   */
  private $_os = '';

  /**
   * 构造函数
   *
   * @param string $name     名称
   * @param array  $hardware 构建硬件
   * @param array  $software 构建软件
   */
  public function __construct($name='', $hardware=array(), $software=array())
  {
    // 名称
    $this->_name = $name;
    echo $this->_name . " 配置如下：\n";
    // 构建硬件
    $this->hardware($hardware);
    // 构建软件
    $this->software($software);
  }

  /**
   * 构建硬件
   *
   * @param  array  $hardware 硬件参数
   * @return void
   */
  public function hardware($hardware=array())
  {
    // 创建cpu
    $hardwareCpu     = new HardwareCpu();
    $this->_cpu      = $hardwareCpu->produce($hardware['cpu']);
    // 创建内存
    $hardwareRam     = new HardwareRam();
    $this->_ram      = $hardwareRam->produce($hardware['ram']);
    // 创建储存
    $hardwareStorage = new HardwareStorage();
    $this->_storage  = $hardwareStorage->produce($hardware['storage']);
  }

  /**
   * 构建软件
   * 
   * @param  array  $software 软件参数
   * @return void
   */
  public function software($software=array())
  {
    // 创建操作系统
    $softwareOs     = new SoftwareOs();
    $this->_os      = $softwareOs->produce($software['os']);
  }
}

<?php
namespace builder;

use builder\BuilderInterface;

/**
 * 手机构建器
 */
class PhoneBuilder implements BuilderInterface
{
  /**
   * 名称
   * @var string
   */
    private $name = '';

  /**
   * 屏幕
   * @var string
   */
    private $screen = '';

  /**
   * 处理器
   * @var string
   */
    private $cpu = '';

  /**
   * 内存
   * @var string
   */
    private $ram = '';

  /**
   * 储存
   * @var string
   */
    private $storage = '';

  /**
   * 相机
   * @var string
   */
    private $camera = '';

  /**
   * 系统
   * @var string
   */
    private $os = '';

  /**
   * 构造函数
   * @param string $name     名称
   * @param array  $hardware 构建硬件
   * @param array  $software 构建软件
   */
    public function __construct($name = '', $hardware = array(), $software = array())
    {
      // 名称
        $this->name = $name;
        echo $this->name . " 配置如下：\n";
      // 构建硬件
        $this->hardware($hardware);
      // 构建软件
        $this->software($software);
    }

  /**
   * 构建硬件
   * @param  array  $hardware 硬件参数
   * @return void
   */
    public function hardware($hardware = array())
    {
      // 创建屏幕
        $hardwareScreen  = new HardwareScreen();
        $this->screen   = $hardwareScreen->produce($hardware['screen']);
      // 创建cpu
        $hardwareCpu     = new HardwareCpu();
        $this->cpu      = $hardwareCpu->produce($hardware['cpu']);
      // 创建内存
        $hardwareRam     = new HardwareRam();
        $this->ram      = $hardwareRam->produce($hardware['ram']);
      // 创建储存
        $hardwareStorage = new HardwareStorage();
        $this->storage  = $hardwareStorage->produce($hardware['storage']);
      // 创建摄像头
        $hardwareCamera  = new HardwareCamera();
        $this->camera   = $hardwareCamera->produce($hardware['camera']);
    }

  /**
   * 构建软件
   * @param  array  $software 软件参数
   * @return void
   */
    public function software($software = array())
    {
      // 创建操作系统
        $softwareOs     = new SoftwareOs();
        $this->os      = $softwareOs->produce($software['os']);
    }
}

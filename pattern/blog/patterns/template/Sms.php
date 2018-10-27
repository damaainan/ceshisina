<?php
namespace template;

use Exception;

/**
 * 短信抽象类
 *
 * 定义算法结构
 * 明确算法执行顺序
 */
abstract class Sms
{
  /**
   * 运营商配置
   * @var string
   */
    protected $config = [];

  /**
   * 短信文本
   * @var string
   */
    protected $text = '[xx公司]你好，你的验证码是';

  /**
   * 构造函数
   *
   * @param array $config 运营商配置
   */
    final public function __construct($config = [])
    {
      // 初始化配置
        $this->initialize($config);
    }

  /**
   * 初始化运营商配置
   *
   * 每个厂商的配置不一定相同，所以子类复写这个方法即可
   *
   * @param  array $config 运营商配置
   * @return void
   */
    abstract public function initialize($config = []);

  /**
   * 生成短信文本
   *
   * 短信模板和厂商无关
   *
   * @return void
   */
    private function makeText()
    {
        $this->text .= rand(000000, 999999);
    }

  /**
   * 厂商发送短信方法
   *
   * 每个厂商复写这个方法即可
   *
   * @param  integer $mobile 手机号
   * @return void
   */
    abstract public function sendSms($mobile = 0);

  /**
   * 发送短信
   *
   * 最终调用的方法，明确了算法顺序
   *
   * @param  integer $mobile 手机号
   * @return void
   */
    final public function send($mobile = 0)
    {
      // 生成文本
        $this->makeText();
      // 发送短信
        $this->sendSms($mobile);
    }
}

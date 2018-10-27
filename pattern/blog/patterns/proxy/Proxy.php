<?php
namespace proxy;

use Exception;

/**
 * 代理工厂
 */
class Proxy
{
  /**
   * 产品生产线对象
   */
    private $shoes;

  /**
   * 产品生产线类型
   */
    private $shoesType;

  /**
   * 构造函数.
   */
    public function __construct($shoesType)
    {
        $this->shoesType = $shoesType;
    }

  /**
   * 生产.
   */
    public function product()
    {
        switch ($this->shoesType) {
            case 'sport':
                echo "我可以偷点工减点料";
                $this->shoes = new ShoesSport();
                break;
            case 'skateboard':
                echo "我可以偷点工减点料";
                $this->shoes = new ShoesSkateboard();
                break;

            default:
                throw new Exception("shoes type is not available", 404);
            break;
        }
        $this->shoes->product();
    }
}

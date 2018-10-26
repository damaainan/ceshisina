<?php
namespace factory;

/**
 * 动物园
 *
 * 生产动物
 */
class Zoo implements Factory
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        echo "初始化了一个动物园~ \n";
    }

    /**
     * 生产方法
     *
     * 生产动物
     * @param  string $type 动物类型
     * @return mixed
     */
    public function produce($type='')
    {
        switch ($type) {
            case 'chicken':
                return new Chicken();
                break;

            case 'pig':
                return new Pig();
                break;

            default:
                echo "该农场不支持生产该农物~ \n";
                break;
        }
    }
}

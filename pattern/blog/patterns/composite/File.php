<?php
namespace composite;

/**
 * 文件实体.
 */
class File implements CompositeInterface
{
    /**
   * 文件名称.
   *
   * @var string
   */
    private $name = '';

  /**
   * 文件内容.
   *
   * @var string
   */
    private $content = '';

  /**
   * 构造函数.
   *
   * @param string $name
   */
    public function __construct($name = '')
    {
        $this->name = $name;
    }

  /**
   * 魔法函数
   * @param  string $name  属性名称
   * @return mixed
   */
    public function __get($name = '')
    {
        // $name = '_' . $name;
        return $this->$name;
    }

  /**
   * 增加一个节点对象
   *
   * @return mixed
   */
    public function add(CompositeInterface $composite)
    {
        throw new Exception('not support', 500);
    }

  /**
   * 删除节点一个对象
   *
   * @return mixed
   */
    public function delete(CompositeInterface $composite)
    {
        throw new Exception('not support', 500);
    }

  /**
   * 打印对象组合.
   *
   * @return mixed
   */
    public function printComposite()
    {
        throw new Exception('not support', 500);
    }

  /**
   * 实体类要实现的方法.
   *
   * @return mixed
   */
    public function operation($operation = '', $content = '')
    {
        switch ($operation) {
            case 'write':
                $this->content .= $content;
                echo 'write success';
                break;
            case 'read':
                echo $this->content;
                break;

            default:
                throw new \Exception("not support", 400);
            break;
        }
    }
}

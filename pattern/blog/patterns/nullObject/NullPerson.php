<?php
namespace nullObject;

/**
 * 鬼
 */
class NullPerson extends Person
{
  /**
   * 空方法
   *
   * @return mixed
   */
    public function doSomthing($person)
    {
        echo "难道这是个鬼吗............ \n";
    }
}

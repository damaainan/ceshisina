<?php
class Person {
    private $name;
    private $age;
    private $address;
    public function __construct($name, $age, $address) {
        $this->name = $name;
        $this->age = $age;
        $this->address = $address;
    }
    public function setter($key, $value) {
        exec ( "{$this}->" . $key . "={$value}" );
    }

    /**
     * 通配型的getter方法不好用。
     * <br />
     * 原因： Object Person can not be converted to string.
     * 
     * @param unknown $key          
     * @return string
     */
    public function getter($key) {
        return exec ( "$this" . "->{$key}" );
    }

    /**
     * 模拟Java语言实现的getter方法。<br />
     *
     * 缺点： 需要为每一个private属性提供单独的getter方法，使得代码略显臃肿。
     */
    public function getName() {
        return $this->name;
    }
}

class Grade {
    private $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }
}
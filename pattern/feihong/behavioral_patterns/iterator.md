### 迭代器模式
迭代器模式（Iterator），提供一种方法顺序访问一个聚合对象中的各种元素，而又不暴露该对象的内部表示。

迭代器模式涉及到的角色比较多。详见：[《JAVA与模式》之迭代子模式](http://www.cnblogs.com/java-my-life/archive/2012/05/22/2511506.html)。


PHP里我们可以使用 `Iterator`接口来实现：
``` php
namespace Yjc\Iterator;

class UserList implements \Iterator
{
    private $index = 0;
    private $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function current()
    {
        return $this->data[$this->index];
    }

    public function next()
    {
        $this->index ++;
    }

    public function key()
    {
        return $this->index;
    }

    public function valid()
    {
        return $this->index < count($this->data);
    }

    public function rewind()
    {
        $this->index = 0;
    }
}
```

测试：
``` php 
//实际应该从数据库查询
$users = new UserList([
    ['id' => 1, 'name' => 'yjc'],
    ['id' => 2, 'name' => 'hhh'],
    ['id' => 3, 'name' => 'fkkf'],
]);

foreach ($users as $user){
    print_r($user);
}
```
<?php 
/**
 * 享元模式
---------

现实例子
> 你在小店里喝过茶吗？他们经常比你要的多做几杯，把剩下的留给别的客人，以此来省资源，比如煤气。享元模式就是以上的体现，即分享。

白话
> 通过尽可能分享相似的对象，来将内存使用或计算开销降到最低。
 */



// 任何被缓存的东西都被叫做享元。 
// 这里茶的类型就是享元。
class KarakTea {
}

// 像工厂一样工作，保存茶
class TeaMaker {
    protected $availableTea = [];

    public function make($preference) {
        if (empty($this->availableTea[$preference])) {
            $this->availableTea[$preference] = new KarakTea();
        }

        return $this->availableTea[$preference];
    }
}



class TeaShop {
    
    protected $orders;
    protected $teaMaker;

    public function __construct(TeaMaker $teaMaker) {
        $this->teaMaker = $teaMaker;
    }

    public function takeOrder(string $teaType, int $table) {
        $this->orders[$table] = $this->teaMaker->make($teaType);
    }

    public function serve() {
        foreach($this->orders as $table => $tea) {
            echo "Serving tea to table# " . $table;
        }
    }
}

$teaMaker = new TeaMaker();
$shop = new TeaShop($teaMaker);

$shop->takeOrder('less sugar', 1);
$shop->takeOrder('more milk', 2);
$shop->takeOrder('without sugar', 5);

$shop->serve();
// Serving tea to table# 1
// Serving tea to table# 2
// Serving tea to table# 5
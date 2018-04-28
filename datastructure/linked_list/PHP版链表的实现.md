## PHP版链表的实现

来源：[https://segmentfault.com/a/1190000014565337](https://segmentfault.com/a/1190000014565337)

本文首发于[我的博客][0]
引用：[Java数据结构与算法——链表][1]

该文章提供的JAVA版的链表的实现。

现在我也贴一下PHP版的链表的实现：

```php
class Node {
    private $data;
    private $next;

    public function getData() {
        return $this->data;
    }

    public function setData($data) {
        $this->data = $data;
        return true;
    }

    public function getNext() {
        return $this->next;
    }

    public function setNext($next) {
        $this->next = $next;
        return true;
    }
}

/**
 * 链表类
 */
class Link {
    private $size = 0;
    private $first;
    private $last;

    /**
     * 获取链表长度
     */
    public function getLength() {
        return $this->size;
    }

    /**
     * 链表中插入第一个元素的时候，头和尾部都是同一个元素
     */
    public function oneNode(string $element) {
        $this->first = new Node;
        $this->first->setData($element);
        $this->last = $this->first;
    }

    /**
     * 当只有一个元素的时候，删除$fist和$last
     */
    public function clear() {
        $this->first = $this->last = null;
    }
    
    /**
     * 头节点插入
     */
    public function addHead(string $element) {
        if ($this->size == 0) {
            $this->oneNode($element);
        } else {
            $node = new Node;
            $node->setData($element);
            $node->setNext($this->first);
            $this->first = $node;
        }
        $this->size++;
        return true;
    }
    
    /**
     * 尾节点插入
     */
    public function addTail(string $element) {
        if ($this->size == 0) {
            $this->oneNode($element);
        } else {
            $node = new Node();
            $node->setData($element);
            $this->last->setNext($node);
            $this->last = $node;
        }

        $this->size++;
    }

    /**
     * 中间节点插入
     */
    public function add(int $index, string $element) {
        if ($index <= $this->size) {
            if ($this->size == 0) {
                oneNode($element);
            } elseif ($index == 0) {
                $this->addHead($element);
            } elseif ($index == $this->size) {
                $this->addTail($element);
            } else {
                $tmp = $this->get($index - 1);
                $node = new Node;
                $node->setData($element);
                $node->setNext($tmp->getNext());
                $tmp->setNext($node);
            }
            $this->size++;
        } else {
            throw new \Exception("插入位置无效或超出链表长度");
        }
    }
    
    /**
     * 获取指定位置的元素
     */
    public function get(int $index) {
        $tmp = $this->first;
        for ($i = 0; $i < $index - 1; $i++) {
            $tmp = $tmp->getNext();
        }
        return $tmp;
    }

    /**
     * 删除头节点
     */
    public function deleteFirst() {
        if ($this->size == 0) {
            throw new \Exception("空链表，无元素可删除");
        } elseif ($this->size == 1) {
            $this->clear();
        } else {
            $tmp = $this->first;
            $this->first = $tmp->getNext();
            $this->size--;
        }
    }

    /**
     * 删除尾节点
     */
    public function deleteLast() {
        if ($this->size == 0) {
            throw new \Exception("空链表，无元素可删除");
        } elseif ($this->size == 1) {
            $this->clear();
        } else {
            $tmp = $this->get($this->size - 1);
            $tmp->setNext(null);
            $this->size--;
        }
    }

    /**
     * 删除指定节点
     */
    public function deleteIndex(int $index) {
        if ($this->size == 0) {
            throw new \Exception("空链表，无元素可删除");
        } elseif ($this->size == 1) {
            $this->clear();
        } else {
            $tmp = $this->get($index - 1);
            $tmp->setNext($tmp->getNext()->getNext());
            $this->size--;
        }

    }

    /**
     * 打印现有的所有元素
     */
    public function printLink() {
        $tmp = $this->first;
        if(is_null($tmp)) {
            return false;
        } 
        echo $tmp->getData();
        while(!is_null($tmp->getNext())) {
            $tmp = $tmp->getNext();
            echo "->" . $tmp->getData();
        }
        echo "\n";
    }
}

$link = new Link();
$link->addHead("1");
$link->printLink(); // 1

$link->addHead("5");
$link->printLink(); // 5->1

$link->addTail("9");
$link->printLink(); // 5->1->9

$link->addTail("7");
$link->printLink(); // 5->1->9->7

$link->add(3, "8"); 
$link->printLink(); // 5->1->9->8->7

print_r("链表长度：" . $link->getLength() . "\n");

$link->deleteFirst();
$link->printLink();

$link->deleteLast();
$link->printLink();

$link->deleteIndex(1);
$link->printLink();

print_r("链表长度：" . $link->getLength() . "\n");
```

[0]: https://www.baoguoxiao.com/php%E7%89%88%E9%93%BE%E8%A1%A8%E7%9A%84%E5%AE%9E%E7%8E%B0/
[1]: https://segmentfault.com/a/1190000014027208
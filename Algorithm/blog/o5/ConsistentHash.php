<?php
class ConsistentHash
{
    public $nodes      = array(); //实际节点
    protected $v_nodes = array(); //虚节点更实际节点的对应关系
    protected $v_mul   = 32; //一个节点对应$v_mul个虚节点

    //根据折半查找原理搜索距离$key最近的比$key最大的值
    protected function binary_search($key)
    {
        $arr_key = array_keys($this->v_nodes);
        if ($arr_key[0] >= $key) {
            return $arr_key[0];
        }
        $arr_num = count($arr_key);
        if ($arr_key[$arr_num - 1] <= $key) {
            return $arr_key[$arr_num - 1];
        }
        //折半查找
        $low    = 0;
        $height = $arr_num - 1;
        while ($low <= $height) {
            $mid = (int) (($low + $height) / 2);
            if ($arr_key[$mid] < $key) {
                if ($arr_key[$mid + 1] >= $key) {
                    return $arr_key[$mid + 1];
                }
                $low = $mid + 1;
            } else if ($arr_key[$mid] > $key) {
                $height = $mid - 1;
            } else {
                return $arr_key[$mid];
            }
        }
        return $arr_key[0];
    }

    //初始化
    public function __construct($nodes = array())
    {
        foreach ($nodes as $v) {
            $this->addNode($v);
        }
    }

    //哈希函数
    public function hash($str)
    {
        return sprintf('%u', crc32($str));
    }

    //根据字符串获取节点位置(对应节点名称/键名)
    public function getPosition($str)
    {
        $hash      = $this->hash($str);
        $node_hash = $this->binary_search($hash);
        return $this->v_nodes[$node_hash];
    }

    //添加节点
    public function addNode($node)
    {
        if (in_array($node, $this->nodes)) {
            return;
        }
        $this->nodes[$node] = null;
        for ($i = 0; $i < $this->v_mul; $i++) {
            $hash                 = $this->hash("{$node}_{$i}");
            $this->v_nodes[$hash] = $node;
        }
        ksort($this->v_nodes);
    }

    //删除节点
    public function deleteNode($node)
    {
        if (isset($this->nodes[$node])) {
            return;
        }
        unset($this->nodes[$node]);
        foreach ($this->v_nodes as $k => $v) {
            if ($v == $node) {
                unset($this->v_nodes[$k]);
            }
        }
    }
}

<?php
// 服务器一致性哈希算法
class CHash {
    // server列表
    private $_server_list = array();
    //一个节点对应的虚拟节点的数量
    private function get_vnode_num()
    {
        return 64;
    }
    public function addServer($server)
    {
        $num = $this->get_vnode_num();
        $add_num = 0;
        for($i=0; $i<$num; $i++)
        {
            //生成虚拟节点
            $vserver=$server.$i.$i.$i.$i.$i;
            $hash = $this->str2int($vserver);
            //插入
            if(!isset($this->_server_list[$hash]))
            {
                $this->_server_list[$hash] = $server;
            }
            else
            {
                ++$add_num;
            }
        }
        if($add_num > 0)
        {
            for($i=0; $i<$num && $add_num>0; $i++)
            {
                //生成虚拟节点
                $vserver=$server.$i.$i.$i.$i.$i.$i.$i.$i.$i.$i;
                $hash = $this->str2int($vserver);
                //插入
                if(!isset($this->_server_list[$hash]))
                {
                    $this->_server_list[$hash] = $server;
                    --$add_num;
                }
            }
        }
    }
    /**
     * 将string类型的hash转为int
     */
    private function str2int($str)
    {
        return crc32(md5($str)) & 0xffff;
    }
    public function quickfind($key)
    {
        // 排序
        ksort($this->_server_list);
        $hash = $this->str2int($key);
        $len = sizeof($this->_server_list);
        if ($len == 0) {
            return FALSE;
        }
        if ($len == 1)
        {
            return end($this->_server_list);
        }
        foreach ($this->_server_list as $key => $val) {
            if($hash < $key)
            {
                return $val;
            }
        }
        return end($this->_server_list);
    }
}
$chash = new CHash();
$chash->addServer("127.0.0.1:1883");
$chash->addServer("127.0.0.1:1884");
$chash->addServer("127.0.0.1:1885");
$chash->addServer("127.0.0.1:1886");
$chash->addServer("127.0.0.1:1887");
$chash->addServer("127.0.0.1:1888");
$chash->addServer("127.0.0.1:1889");
$result = array();
for($i=0;$i<100000;$i++)
{
    $name = "127.0.0.1:".$i;
    $server = $chash->quickfind($name);
    if(isset($result[$server]))
    {
        $result[$server] = $result[$server]+1;
    }
    else
    {
        $result[$server] = 1;
    }
}
foreach($result as $key=>$val)
{
    echo $key.":".$val."\n";
}
# [常用算法-一致性哈希算法](https://www.jianshu.com/p/65f23e426c3a)

作者  林湾村龙猫关注 2016.01.21 01:22  

## **概述**

1. 我们的memcache客户端（这里我看的spymemcache的源码），使用了一致性hash算法ketama进行数据存储节点的选择。与常规的hash算法思路不同，只是对我们要存储数据的key进行hash计算，分配到不同节点存储。一致性hash算法是对我们要存储数据的服务器进行hash计算，进而确认每个key的存储位置。
1. 常规hash算法的应用以及其弊端  
最常规的方式莫过于hash取模的方式。比如集群中可用机器适量为N，那么key值为K的的数据请求很简单的应该路由到hash(K) mod N对应的机器。的确，这种结构是简单的，也是实用的。但是在一些高速发展的web系统中，这样的解决方案仍有些缺陷。随着系统访问压力的增长，缓存系统不得不通过增加机器节点的方式提高集群的相应速度和数据承载量。增加机器意味着按照hash取模的方式，在增加机器节点的这一时刻，大量的缓存命不中，缓存数据需要重新建立，甚至是进行整体的缓存数据迁移，瞬间会给DB带来极高的系统负载，设置导致DB服务器宕机。
1. 设计分布式cache系统时，一致性hash算法可以帮我们解决哪些问题？  
分布式缓存设计核心点：在设计分布式cache系统的时候，我们需要让key的分布均衡，并且在增加cache server后，cache的迁移做到最少。  
这里提到的一致性hash算法ketama的做法是：选择具体的机器节点不在只依赖需要缓存数据的key的hash本身了，而是机器节点本身也进行了hash运算。

## **理论**

[http://blog.csdn.net/cywosp/article/details/23397179][1]  
[http://blog.csdn.net/kongqz/article/details/6695417][2]  
[http://blog.csdn.net/caigen1988/article/details/7708806][3]

## **代码（PHP）**

```php
<?php
    class ConsistentHash {
        public $nodes = array();//实际节点
        protected $v_nodes = array();//虚节点更实际节点的对应关系
        protected $v_mul = 32; //一个节点对应$v_mul个虚节点
    
        //根据折半查找原理搜索距离$key最近的比$key最大的值
        protected function binary_search($key){
            $arr_key = array_keys($this->v_nodes);
            if($arr_key[0]>=$key){
                return $arr_key[0];
            }
            $arr_num = count($arr_key);
            if($arr_key[$arr_num-1] <= $key){
                return $arr_key[$arr_num-1];
            }
            //折半查找
            $low = 0;
            $height = $arr_num-1;
            while($low <= $height){
                $mid = (int)(($low+$height)/2);
                if($arr_key[$mid] < $key){
                    if($arr_key[$mid+1] >= $key){
                        return $arr_key[$mid+1];
                    }
                    $low = $mid+1;
                }else if($arr_key[$mid] > $key){
                    $height = $mid-1;
                }else{
                    return $arr_key[$mid];
                }
            }
            return $arr_key[0];
        }
    
        //初始化
        public function __construct($nodes=array()){
            foreach($nodes as $v){
                $this->addNode($v);
            }
        }
    
        //哈希函数
        public function hash($str){
            return sprintf('%u',crc32($str));
        }
    
        //根据字符串获取节点位置(对应节点名称/键名)
        public function getPosition($str){
            $hash = $this->hash($str);
            $node_hash = $this->binary_search($hash);
            return $this->v_nodes[$node_hash];
        }
    
        //添加节点
        public function addNode($node){
            if(in_array($node,$this->nodes)){
                return;
            }
            $this->nodes[$node]= null;
            for($i=0;$i<$this->v_mul;$i++){
                $hash = $this->hash("{$node}_{$i}");
                $this->v_nodes[$hash]=$node;
            }
            ksort($this->v_nodes);
        }
    
        //删除节点
        public function deleteNode($node){
            if(isset($this->nodes[$node])){
                return;
            }
            unset($this->nodes[$node]);
            foreach($this->v_nodes as $k=>$v){
                if($v == $node){
                    unset($this->v_nodes[$k]);
                }
            }
        }
    }
```


[1]: http://blog.csdn.net/cywosp/article/details/23397179
[2]: http://blog.csdn.net/kongqz/article/details/6695417
[3]: http://blog.csdn.net/caigen1988/article/details/7708806
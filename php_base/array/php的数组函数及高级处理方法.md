# php的数组函数及高级处理方法

 时间 2017-03-05 21:51:33  海诺博客

原文[https://blog.dmic.studio/posts/php-array-function/][1]


usort是用户自定义数组排序的方法

先举一个最新的在用的例子。

```php
    usort($arr, function($a, $b){
        return $a['isHot'] <= $b['isHot'] ? 1 : -1;
    });
    usort($arr, function($a, $b){
        if ($a['isHot'] == $b['isHot'])
            return $a['catSort'] <= $b['catSort'] ? 1 : -1;
        else
            return 0;
    });
```

一开始我是这么用的

后来想了想可以这样用

```php
    usort($arr, function($a, $b){
        
        if ($a['isHot'] == $b['isHot'])
            return $a['catSort'] <= $b['catSort'] ? 1 : -1;
        else
            return $a['isHot'] <= $b['isHot'] ? 1 : -1;
    });
```

这样就可以做到 按照 isHot DESC,catSort DESC 逆序排序

## 扩展函数 

## list2tree 

将数组转换成tree 

```php
    /**
     * 把返回的数据集转换成Tree
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     * @return array
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    function list_to_tree($list, $pk='id', $pid ='pid', $child ='_child', $root =0){
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId =  $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }
```

## tree2list 

```php
    /**
     * 将list_to_tree的树还原成列表
     * @param  array $tree  原来的树
     * @param  string $child 孩子节点的键
     * @param  array  $list  过渡用的中间数组，
     * @return array        返回排过序的列表数组
     * @author yangweijie <yangweijiester@gmail.com>
     */
    function tree_to_list($tree, $child ='_child', &$list = array()){
        if(is_array($tree)) {
            foreach ($tree as $key => $value) {
                $reffer = $value;
                $list[] = $reffer;
                if(isset($reffer[$child])){
                    unset($reffer[$child]);
                    tree_to_list($value[$child], $child, $order, $list);
                }
                $list[] = $reffer;
            }
        }
        return $list;
    }
```

## list_sort_by 

根据某个字段对数组进行排序

这个其实并不如上面的usort要好，但是也是有些时候在弄 

```php
    /**
    * 对查询结果集进行排序
    * @access public
    * @param array $list 查询结果
    * @param string $field 排序的字段名
    * @param array $sortby 排序类型
    * asc正向排序 desc逆向排序 nat自然排序
    * @return array
    */
    function list_sort_by($list,$field, $sortby='asc'){
       if(is_array($list)){
           $refer = $resultSet = array();
           foreach ($list as $i => $data)
               $refer[$i] = &$data[$field];
           switch ($sortby) {
               case 'asc': // 正向排序
                    asort($refer);
                    break;
               case 'desc':// 逆向排序
                    arsort($refer);
                    break;
               case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
           }
           foreach ( $refer as $key=> $val)
               $resultSet[] = &$list[$key];
           return $resultSet;
       }
       return false;
    }
```

[1]: https://blog.dmic.studio/posts/php-array-function/

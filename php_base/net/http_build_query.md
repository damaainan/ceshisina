### http_build_query
use：将数组转化成url参数

    $data = array('foo', 'bar', 'baz', 'boom', 'cow' => 'milk', 'php' =>'hypertext processor'); 
    echo http_build_query($data);
    // 输出
    //0=foo&1=bar&2=baz&3=boom&cow=milk&php=hypertext+processor
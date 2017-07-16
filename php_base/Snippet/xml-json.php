<?php
/**
 * TODO:Json格式数据通信
 * Author：entner
 * time:   2017-5-8
 * version:1.0
 * ready:
        状态码  ：int    $code
        提示消息：string $message
        消息内容：array $data
        数组包装：array $result
        

   function： 
       show         封装多种通信数据格式
       jsonEncode   封装Json格式通信数据
       xmlToJson    封装xml格式通信数据
       xmlEncode     递归调用xmlToJson

 */
Class Json{

    const  JSON = "json";

/**
     *按综合方式输出通信数据
     *@param inter code 状态码
     *@param char  message 消息提示
     *@param array data 通信数据
     *@param string type 数据类型
     *return string
    */    

    public function show($code,$message,$data=array(),$type = self::JSON){
        /*    检查状态码是否合法    */
        if(!is_numeric($code)){
            exit();
        }

        $result = array(
            'code'=>$code,
            'message'=>$message,
            'data'=>$data
            );

        /* 由客户端传递参数决定封装数据的格式，默认Json格式 */
        $type = isset($_GET['format'])? $_GET['format']:self::JSON;

        if($type == 'xml'){
            $this->xmlEnCode($code,$message,$data);
            exit;    //一次不允许并发多种格式，所以没必要执行下面的判断
        }else if($type == 'json'){
            $this->jsonEncode($code,$message,$data);
            exit ;
        }else if($type == 'array'){
            var_dump($result);
            exit;
        }else{
            // 客户端传来的数据格式为 http/ftp/
        }

    }


/**
     *输出Json格式通信数据
     *@param inter code 状态码
     *@param char  message 消息提示
     *@param array data 通信数据
     *return string
    */    
    public function jsonEnCode($code,$message,$data=array()){
        if(!is_numeric($code)){
            exit();
        }
        $result = array(
            'code'=>$code,
            'message'=>$message,
            'data'=>$data
            );
        echo json_encode($result);    //json_encode会自动递归转换数组变量
        return true;
    }

    /**
     *输出XML格式通信数据
     *@param inter code 状态码
     *@param char  message 消息提示
     *@param array data 通信数据
     *return string
    */    
    public function xmlEnCode($code,$message,$data=array()){
        if(!is_numeric($code)){
            exit();
        }
        $result = array(
            'code'=>$code,
            'message'=>$message,
            'data'=>$data
            );

        /*    拼接xml格式数据    */
        
        
        /*    这里一定要注意声明头部信息和XML声明    */
        header("Content-type:text/xml");
        $xml  = "<?xml version = '1.0' encoding = 'UTF-8'?>\n";
        $xml .= "<root>\n";          /*     XML标签其实也是字符串，所以要用 . 连接运算符     */
        $xml .= self::xmlToJson($result); /* 调用xmlToJson函数解析数组转换为节点 */        
        $xml .= "</root>";
        echo $xml;        
    }

    /**
     *递归拼接XML数据
     *@param inter code 状态码
     *@param char  message 消息提示
     *@param array data 通信数据
     *return string
    */    
    public static function xmlToJson($data){
        $xml = $attr = "";
        foreach($data as $k => $v){

            /*XML不允许出现数字标签，所以要么奖数字转化为字母，要么混合拼接，这里采用很合拼接的方式 */
            if(is_numeric($k)){
                $attr = " id = '{$k}' ";
                $k = "item";
                $xml .="<{$k}{$attr}>\n";
                /*    因为数组内部可能还存在数组，所以需要自行递归检查一遍，注意，在每次递归的时候，都要连接在$xml尾部，并换行    */
                $xml .=is_array($v)?self::xmlToJson($v):$v;
                $xml .="</{$k}>\n";
            }else{
                $xml .="<{$k}>\n";
                $xml .=is_array($v)?self::xmlToJson($v):$v;
                $xml .="</{$k}>\n";
            }
            
        }
        return $xml;
    }

}

$data = array(
    
    'name'=>'entner',
    'type'=>array(
            0=>'a',
            1=>'b'
        )
    );
$try = new Json();
$try->xmlEnCode(200,'success',$data);
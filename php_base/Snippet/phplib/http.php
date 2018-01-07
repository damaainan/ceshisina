<?php
/**
 * http.php
 * 用来向服务器的RESTful API发起各类HTTP请求的工具函数。
 *
 * 使用: http://localhost/http.php?action=xxx
 * xxx \in {get,post,put,patch,delete}
 *
 * Created by PhpStorm.
 */

class commonFunction{
    public function callInterfaceCommon(string $URL, string $type, string $params, array $headers):string {
        $ch = curl_init($URL);
        $timeout = 5;
        if($headers!=""){
            curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
        }else {
            curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        }
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        switch ($type){
            case "GET" : curl_setopt($ch, CURLOPT_HTTPGET, true);break;
            case "POST": curl_setopt($ch, CURLOPT_POST,true);
                         curl_setopt($ch, CURLOPT_POSTFIELDS,$params);break;
            case "PUT" : curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                         curl_setopt($ch, CURLOPT_POSTFIELDS,$params);break;
            case "PATCH": curl_setopt($ch, CULROPT_CUSTOMREQUEST, 'PATCH');
                          curl_setopt($ch, CURLOPT_POSTFIELDS, $params);break;
            case "DELETE":curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                          curl_setopt($ch, CURLOPT_POSTFIELDS,$params);break;
        }
        $file_contents = curl_exec($ch);//获得返回值
        return $file_contents;
        curl_close($ch);
    }
}
$params="{user:\"admin\",pwd:\"admin\"}";
//$headers=array('Content-Type: text/html; charset=utf-8');
//$headers=array('accept: application/json; Content-Type:application/json-patch+json');
$headers=array('Content-Type:application/json-patch+json');
#$url=$GLOBALS["serviceUrl"]."/user";
$url='http://localhost/action.php';
$cf = new commonFunction();

$action=strtoupper($_GET['action']);
echo "你指定的HTTP请求动作为".$action."<br/><hr/>";

$strResult = $cf->callInterfaceCommon($url,$action,$params,$headers);
echo "执行该HTTP请求动作,得到<br/>".$strResult;
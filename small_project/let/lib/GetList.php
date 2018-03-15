<?php
namespace Tools;
require "../vendor/autoload.php";
use phpQuery;
use QL\QueryList;
// 用不到
class GetList {
    public static function dealUrl($url) {
        $rules = array(
            "center" => array("center:eq(1)", 'html'),
        );

        // $html = file_get_contents("../data/list.html");
        $str = file_get_contents($url);
        $html = iconv("GB2312", "utf-8", $str);
        // $html = file_get_contents($url);
        $data = QueryList::html($html)->rules($rules)->removeHead()->query()->getData();
        $ret = $data->all();
        // var_dump($ret);
        $ret = $ret[0]['center'];
        // echo $ret;

        $data = self::getList($ret);
        return $data;
    }

    public static function getList($ret) {
        //https://www.17500.cn/let/details.php?issue=18028
        $doc = phpQuery::newDocumentHTML($ret);
        $ch = pq($doc)->find("a");
        foreach ($ch as $va) {
            $href = pq($va)->attr("href");
            $name = pq($va)->text();
            $temp['link'] = "https://www.17500.cn/let/" . $href;
            $arr = explode('(', str_replace(")", '', $name));
            $temp['pdate'] = $arr[0];
            $temp['turn'] = $arr[1];
            $temp['status'] = 0;
            $temp['create_time'] = date("Y-m-d H:i:s", time());
            // echo "https://www.17500.cn/let/".$href,$name,"\n";
            $data[] = $temp;
        }
        return $data;
    }
}
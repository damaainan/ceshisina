<?php
namespace Tools;
require "../vendor/autoload.php";
use QL\QueryList;

// use phpQuery;  // 用不到
class GetContent {
    public static function dealUrl($url) {
        $rules = array(
            "title" => array("table:eq(2)", 'html'),
        );

        // $html = file_get_contents("../data/let1.html");
        // echo $url;
        $str = file_get_contents($url);
        $html = iconv("GB2312", "utf-8", $str);
        $data = QueryList::html($html)->rules($rules)->removeHead()->query()->getData();
        $ret = $data->all();
        // var_dump($ret);
        $ret = $ret[0]['title'];

        $result = self::getDetail($ret);
        return $result;

    }

    public static function getDetail($ret) {
        $rule = [
            'turn' => ["table:eq(0)", 'text'],
            'code' => ["table:eq(1) tr:eq(1)", 'text'],
            'total' => ["table:eq(2) tr td", 'text'],
            'pride' => ["table:eq(3)", 'text'],
            'plus' => ["table:eq(4)", 'text'],
            'local' => ["table:eq(5) tr td", 'text'],
        ];
        $data = QueryList::html($ret)->rules($rule)->query()->getData();
        $ret = $data->all();
        // var_dump($ret);
        $result = [];
        foreach ($ret[0] as $key => $val) {
            // $rr= pq($val)->html();
            // echo $rr,"***".$key,"\n*****\n";
            $val = preg_replace("/[\s\nr]{1,}/", ' ', $val);
            switch ($key) {
            // case 后的选项 是数字就都是数字  是字符就都是字符，否则不报错，不显示
            case "turn":
                $resu = self::dealTurn($val);
                // echo trim($val),"\n";
                break;
            case "code":
                $resu = self::dealCode($val);
                break;
            case "total":
                $resu = self::dealTotal($val);
                break;
            case "pride":
                $resu = self::dealPride($val);
                break;
            case "plus":
                $resu = self::dealPlus($val);
                break;
            case "local":
                $resu = self::dealLocal($val);
                break;
            default:
                break;

            }
            $result = array_merge($result, $resu);
        }
        // var_dump($result);
        return $result;
    }
    public static function dealTurn($info) {
        preg_match('/\d{5}/', $info, $match1);
        $turn = $match1[0];
        preg_match('/\d{4}-\d{2}-\d{2}/', $info, $match2);
        $date = $match2[0];
        $data['turn'] = $turn;
        $data['date'] = $date;
        return $data;
    }
    public static function dealCode($code) {
        $code = trim($code);
        $codes = explode(' ', $code);
        $data['codes'] = $codes;
        return $data;
    }
    public static function dealTotal($total) {
        preg_match_all('/[\d,]{4,13}/', $total, $match);
        if($match[0][0]){
            $data['ctotal'] = $ctotal = $match[0][0];
        }else{
            $data['ctotal'] = 0;
        }
        if(isset($match[0][1])){
            $data['total'] = $total = $match[0][1];
        }else{
            $data['total'] = 0;
        }
        return $data;
    }
    public static function dealPride($info) {
        preg_match_all('/[\x{4e00}-\x{9fa5}]等奖[ ][\d,]{1,10}[ ][\d,]{1,10}[ ]{0,1}/u', $info, $match);
        $pride = [];
        // var_dump($match);
        foreach ($match[0] as $val) {
            $arr = explode(' ', $val);
            //一等奖 0 0 二等奖 43 223,044 三等奖 503 7,433 四等奖 25,204 200 五等奖 494,801 10 六等奖
            switch ($arr[0]) {
            case "一等奖":
                $key = "p_1";
                break;
            case "二等奖":
                $key = "p_2";
                break;
            case "三等奖":
                $key = "p_3";
                break;
            case "四等奖":
                $key = "p_4";
                break;
            case "五等奖":
                $key = "p_5";
                break;
            case "六等奖":
                $key = "p_6";
                break;
            default:
                break;

            }
            $pride[$key . "_num"] = $arr[1];
            $pride[$key] = $arr[2];
        }
        $data['pride'] = $pride;
        return $data;
        // var_dump($match);
    }
    public static function dealPlus($info) {
        preg_match_all('/[\x{4e00}-\x{9fa5}]等奖[ ][\d,]{1,10}[ ][\d,]{1,10}[ ]{0,1}/u', $info, $match);
        $pride = [];
        foreach ($match[0] as $val) {
            $arr = explode(' ', $val);
            switch ($arr[0]) {
            case "一等奖":
                $key = "pl_1";
                break;
            case "二等奖":
                $key = "pl_2";
                break;
            case "三等奖":
                $key = "pl_3";
                break;
            default:
                break;

            }
            $pride[$key . '_num'] = $arr[1];
            $pride[$key] = $arr[2];
        }
        $data['plus'] = $pride;
        return $data;
    }
    public static function dealLocal($local) {
        preg_match('/[\d\+\s]{22}/', $local, $match);
        // var_dump($match1);
        if(!$match){
            $data['ballturn'] = '';
        }else{
            $data['ballturn'] = $match[0];
        }
        return $data;
    }

// $doc = phpQuery::newDocumentHTML($ret);
    // $ch = pq($doc)->find("table");
    // foreach ($ch as $key=>$val) {
    //     $rr= pq($val)->html();
    //     // echo $rr,"***".$key,"\n*****\n";
    //     switch ($key){
    //         case 0:
    //             break;
    //         case 1:
    //             $code = pq($val)->find("tr:eq(1)")->text();
    //             $code = preg_replace("/[\s\nr]{1,}/",' ',$code);
    //             echo trim($code),"\n";
    //             break;
    //         case 2:
    //             $total = pq($val)->text();
    //             $total = preg_replace("/[\s\nr]{1,}/",' ',$total);
    //             echo trim($total),"\n";
    //             break;
    //         case 3:
    //             $pride = pq($val)->text();
    //             $pride = preg_replace("/[\s\nr]{1,}/",' ',$pride);
    //             echo trim($pride),"\n";
    //             break;
    //         case 4:
    //             $plus = pq($val)->text();
    //             $plus = preg_replace("/[\s\nr]{1,}/",' ',$plus);
    //             echo trim($plus),"\n";
    //             break;
    //         case 5:
    //             $local = pq($val)->text();
    //             $local = preg_replace("/[\s\nr]{1,}/",' ',$local);
    //             echo trim($local),"\n";
    //             break;
    //         default:
    //             break;
    //     }
    // }
    // 0-5
    // 0  期数
    // 1  号码
    // 2  销售额
    // 3  奖数
    // 4  加注
    // 5  顺序
}
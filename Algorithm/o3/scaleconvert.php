<?php
/**
 *栈的应用举例
 *
 *1.十进制整数转换为二、八、十六进制整数
 *2.括号匹配问题
 */
header("content-type:text/html;charset=UTF-8");
//在PHP数据结构之五 栈的PHP的实现和栈的基本操作 可以找到StackLinked类
include_once("./StackLinked.php");
/**
 *十进制整数转换为二、八、十六进制整数
 *
 *@param int $input 待转换的十进制数
 *@param int $output_scale 输出的进制
 *@return array $array['before']输出的十进制整型数（整型）
$array['after']  转换后的整型数（整型）
$array['stringclass'] 转换后的整型数的字符串表示（字符串型）
 */
function scaleconvert($input,$output_scale){
    if(is_int($input)){
        $a=$input;
        $scale=array(2,8,16);
        if(in_array($output_scale,$scale)){
            $stack=new StackLinked();
            while($a!=0){
                $mod=$a % $output_scale;
                $stack--->getPushStack($mod);
                $a=(int)($a-$mod)/$output_scale;
            }
            $elems=$stack->getAllPopStack();
            $output='';
            if($output_scale == 16){
                $output.='0x';
            }elseif($output_scale == 8){
                $output.='0';
            }
            foreach($elems as $value){
                if($output_scale == 16){
                    switch($value){
                        case 10:
                            $value='A';
                            break;
                        case 11:
                            $value='B';
                            break;
                        case 12:
                            $value='C';
                            break;
                        case 13:
                            $value='D';
                            break;
                        case 14:
                            $value='E';
                            break;
                        case 15:
                            $value='F';
                            break;
                    }
                }
                $output.=$value;
            }
            //因为输出语句会自动将整型的数转换为10进制输出
            //也即如果转换后的结果为0xff,直接将0xff输出会得到255，所以返回一数组
            return array('before'=>$input,'after'=>intval($output,$output_scale),'stringclass'=>$output);
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}
/**
 *实现括号匹配算法
 *
 *@param string $str
 *@return mixed 匹配成功返回一个数组,否则返回false
 */
function bracketmatch($str){
    $substr='';
    $brackets=array();
    $stack=new StackLinked();
    $strlen=strlen($str);
    $leftb="(";
    $rightb=")";
    for($i=0;$i<$strlen;$i++){
        $cu=$str[$i];
        if(ord($cu)>127){
            $cu=substr($str,$i,2);
            $i++;
        }
        if($cu == $leftb){
            if(strlen($substr)>0){
                $e=array('v'=>$substr,'d'=>'L');
                $stack->getPushStack($e);
            }
            $stack->getPushStack($cu);
            $substr='';
        }
        if($cu == $rightb){
            if(strlen($substr)>0){
                $e=array('v'=>$substr,'d'=>'R');
                $stack->getPushStack($e);
                $substr='';
            }
            $bl='(';
            $tag=true;
            $remain=$stack->getCountForElem('(');
            $e1='';
            while($tag && !$stack->getIsEmpty()){
                $stack->getPopStack($e1);
                if($e1 == $leftb){
                    $bl.=')';
                    $brackets[$remain][]=$bl;
                    $tag=false;
                }else{
                    if($e1['d'] == 'L'){
                        $bl='('.$e1['v'].substr($bl,1);
                    }else{
                        $bl.=$e1['v'];
                    }
                }
            }
        }
        if($cu !='(' && $cu != ')'){
            $substr.=$cu;
        }
    }
    if($stack->getCountForElem('(') == 0){
        $stack->getAllPopStack();
        return $brackets;
    }else{
        return false;
    }
}
$num=255;
$output_scale=16;
$renum=scaleconvert($num,$output_scale);
echo "10进制数{$renum['before']}转换为{$output_scale}进制为：".$renum['stringclass'];
echo "<br><pre>";
var_dump(bracketmatch("(sfsf((徐典阳(喹)sfsdf)(sfsf)sdfsa(啦啦))啦嘌呆在abc)"));
echo "</pre>";
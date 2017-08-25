<?php
#字符串匹配，sunday算法


/**
 * PHP 字符串匹配算法 Sunday算法
搜索文本 text = "my testing algorithm in test"

模式 pattern = "test"

Sunday算法的关键点在于

1.设定一个匹配位移映射 shift[]，这个shift[]映射关系必须按从左到右的顺序简历，例如pattern = "test",注意到此处有2个t，那么建立出来的位移映射是 shift[] = Array ( [t] => 1 [e] => 3 [s] => 2 )，而如果不是从左到右，是从右到左的建立映射，就会变成 shift[] = Array ( [t] => 4 [e] => 3 [s] => 2)，这样到时候匹配就无法得到正确结果

2.根据当前比对字符串的下一个字符来确定位移长度，如下图

sunday.jpg


第一次比较的时候，如图1，第一个字符“m”就和“t”不一样，那就查找比patter长1位的text中的字符，为“e”，然后查找映射表，e => 3，接下来把pattern向后移动3位，就到了,图2中的位置，再从“t”开始比较，发现匹配到了继续往后，看text中比pattern长1的那个字符，为“i”，此时发现映射表中没有“i”，则直接将pattern向后移动pattern_size位，就到了图3，然后重复前面的过程，直到比较到text_size - patter_size为坐标的那个字符
 */



function sunday($patt, $text)
{
    $patt_size = strlen($patt);
    $text_size = strlen($text);

    #初始化字符串位移映射关系
    #此处注意,映射关系表的建立一定是从左到右，因为patten可能存在相同的字符
    #对于重复字符的位移长度，我们只能让最后一个重复字符的位移长度覆盖前面的位移长度
    #例如pattern = "testing",注意到此处有2个t，那么建立出来的位移映射是 shift[] = Array ( [t] => 4 [e] => 6 [s] => 5 [i] => 3 [n] => 2 [g] => 1 )
    #而如果不是从左到右，是从右到左的建立映射，就会变成 shift[] = Array ( [t] => 7 [e] => 6 [s] => 5 [i] => 3 [n] => 2 [g] => 1 )，这样到时候匹配就无法得到正确结果
    for ($i = 0; $i < $patt_size; $i++) {
        $shift[$patt[$i]] = $patt_size - $i;
    }

    $i     = 0;
    $limit = $text_size - $patt_size; #需要开始匹配的最后一个字符坐标
    while ($i <= $limit) {
        $match_size = 0; #当前已匹配到的字符个数
        #从i开始匹配字符串
        while ($text[$i + $match_size] == $patt[$match_size]) {
            $match_size++;
            if ($match_size == $patt_size) {
                echo "Match index: {$i} \r\n";
                break;
            }
        }

        $shift_index = $i + $patt_size; #在text中比pattern的多一位的字符坐标
        if ($shift_index < $text_size && isset($shift[$text[$shift_index]])) {
            $i += $shift[$text[$shift_index]];
        } else {
            #如果映射表中没有这个字符的位移量，直接向后移动patt_size个单位
            $i += $patt_size;
        }
    }
}

$text = "my testing algorithm test";
$patt = "test";

sunday($patt, $text);

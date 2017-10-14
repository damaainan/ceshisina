<?php 

    function snake($row = 3, $col = 3){
        $res = array();
        // 初始值
        $start = 1;
        // 当前遍历层数
        $flag = intval(($row + 1) / 2);
    
        for ($i = 1; $i <= $flag; $i++) { 
            $startx = $i - 1;
            $starty = $i - 1;
            $width = $col - $i + 1;
            $height = $row - $i + 1;
            // 上
            for ($u = $starty; $u < $width; $u++) { 
                $res[$startx][$u] = $start;
                $start += 1;
            }
            // 右
            for ($r = $startx + 1; $r < $height; $r++) { 
                $res[$r][$u-1] = $start;
                $start += 1;
            }
            // 下
            for ($d = $u - 1 - 1; $d >= $starty; $d--) { 
                $res[$r-1][$d] = $start;
                $start += 1;
            }
            // 左
            for ($l = $r - 1 - 1; $l >= $startx + 1; $l--) { 
                $res[$l][$d+1] = $start;
                $start += 1;
            }
    
        }
    
        // 输出
        for ($i = 0; $i < $row; $i++) { 
            for ($j = 0; $j < $col; $j++) { 
                echo $res[$i][$j] . " ";
            }
        }
    
    }
    
    snake();
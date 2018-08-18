<?php

// 斐波那契序列

function getNumber($num) {
    
    if ($num == 1) {
        return 0;
    } else if ($num == 2) {
        return 1;
    }
    
    return getNumber($num - 1) + getNumber($num - 2);
}


echo getNumber(6);

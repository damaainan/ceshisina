<?php 
header("Content-type:text/html; Charset=utf-8");
/**
 * 五家共井
 */



function Main()
{

    $a = $b = $c = $d = $e = $h = 0;

   $flag = true;

    while ($flag)
    {
        //4的倍数
        $e += 4;

        $a = 0;

        while ($flag)
        {
            //5的倍数
            $a += 5;

            $d = $e + $a / 5;

            $c = $d + $e / 4;

            if ($c % 2 != 0)
                continue;

            if ($d % 3 != 0)
                continue;

            $b = $c + $d / 3;

            if ($b + $c / 2 < $a)
                break;

            if ($b + $c / 2 == $a)
                $flag = false;
        }
    }

    $h = 2 * $a + $b;

    echo "a=".$a.",b=".$b.",c=".$c.",d=".$d.",e=".$e." ------h=".$h."\n";

    // Console.Read();
}

Main();



function Main2()
{

    $a = $b = $c = $d = $e = $h = 0;

    $flag = true;

    while ($flag)
    {
        //4的倍数
        $e += 4;

        $a = 0;

        while ($flag)
        {
            //5的倍数
            $a += 5;

            $d = $e + $a / 5;

            $c = $d + $e / 4;

            if ($c % 2 != 0)
                continue;

            if ($d % 3 != 0)
                continue;

            $b = $c + $d / 3;

            if ($b + $c / 2 < $a)
                break;

            if ($b + $c / 2 == $a)
                $flag = false;
        }
    }

    $h = 2 * $a + $b;

    echo "a=".$a.",b=".$b.",c=".$c.",d=".$d.",e=".$e." ------h=".$h."\n";
}

Main2();
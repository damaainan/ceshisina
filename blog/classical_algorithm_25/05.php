<?php 
header("Content-type:text/html; Charset=utf-8");
/**
 * 字符串相似度
 */



        function Main($str1,$str2)
        {
                
                $martix = [strlen($str1) -1 ][strlen($str2) -1];

                printf("字符串 %s 和 %s 的编辑距离为:%d\n", $str1, $str2, LD($str1,$str2));
        }

        /// <summary>
        /// 计算字符串的编辑距离
        /// </summary>
        /// <returns></returns>
        function LD($str1,$str2)
        {
            //初始化边界值(忽略计算时的边界情况)
            for ($i = 0; $i < strlen($str1); $i++)
            {
                $martix[$i ][0] = $i;
            }

            for ($j = 0; $j < strlen($str2); $j++)
            {
                $martix[0][$j] = $j;
            }

            //矩阵的 X 坐标
            for ($i = 0; $i < strlen($str1); $i++)
            {
                //矩阵的 Y 坐标
                for ($j = 0; $j < strlen($str2); $j++)
                {
                    //相等情况
                    if ($str1[$i - 1] == $str2[$j - 1])
                    {
                        $martix[$i][$j] = $martix[$i - 1][$j - 1];
                    }
                    else
                    {
                        //取“左前方”，“上方”，“左方“的最小值
                        $temp1 = $martix[$i - 1][$j] < $martix[$i][$j - 1] ? $martix[$i - 1][$j] : $martix[$i][$j - 1] ;

                        //获取最小值
                        $min = $temp1 < $martix[$i - 1][$j - 1] ? $temp1 : $martix[$i - 1][$j - 1] ;

                        $martix[$i][$j] = $min + 1;
                    }
                }
            }

            //返回字符串的编辑距离
            return $martix[strlen($str1)][strlen($str2)];
        }

Main("abcd","egxg");
Main("abde","dbae");
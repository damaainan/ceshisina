<?php 

include_once"image.php";

//client.php

class Client
{
    public static function Main()
    {
        try {
            //实现文件的自动加载
            function autoload($class)
            {
                include strtolower($class) . '.php';
            }
            spl_autoload_register('autoload');

            $arr = array(62, 88, 58, 47, 35, 73, 51, 99, 37, 93);

//            $tree = new Bst();   //搜索二叉树
            $tree = new Avl();    //平衡二叉树
//            $tree = new Rbt();   //红黑树

            $tree->init($arr);     //树的初始化
//            $tree->Delete(62);
//            $tree->Insert(100);
//            $tree->MidOrder();    //树的中序遍历（这也是调试的一个手段，看看数字是否从小到大排序）
            $image = new image($tree);
            $image->show();    //显示图像
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

Client::Main();
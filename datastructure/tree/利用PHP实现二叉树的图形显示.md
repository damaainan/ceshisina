# [利用PHP实现二叉树的图形显示][0]

 标签： [php][1][二叉树][2][树的图形化][3]

 2016-10-21 12:49  1396人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [前言][9]
1. [效果显示][10]
1. [上代码][11]

## 前言：

最近老师布置了一个作业：理解并实现平衡二叉树和红黑树，本来老师是说用C#写的，但是我学的C#基本都还给老师了，怎么办？那就用现在最熟悉的语言[PHP][12]来写吧！

有一个问题来了，书上在讲解树的时候基本上会给出形象的树形图。但是当我们自己试着实现某种树，在调试、输出的时候确只能以字符的形式顺序地输出。这给调试等方面带来了很大的不便。然后在各种百度之后，我发现利用[php][12]实现二叉树的图形显示的资源几乎是零！好吧，那我就自己个儿实现一个！

## 效果显示：

如果我是直接在这一步摆代码的话，估计大家会比较烦闷，那我就直接上结果吧，后面在补代码，先激发激发大家的阅读兴趣：

1、搜索二叉树：

![这里写图片描述][13]

2、平衡二叉树：

![这里写图片描述][14]

3、红黑树：

![这里写图片描述][15]

## 上代码：

我们给图片创建一个类吧，显得稍微的小高级：
```php
    
<?php
// image.php 文件
/**
 * author:LSGOZJ
 * time:2016/10/24 12:38
 * description: 绘制二叉树图像
 */
class image
{
    //树相关设置
    //每层之间的间隔高度
    private $level_high = 100;
    //最底层叶子结点之间的宽度
    private $leaf_width = 50;
    //结点圆的半径
    private $rad = 20;
    //根节点离边框顶端距离
    private $leave = 20;
    //树（保存树对象的引用）
    private $tree;
    //树的层数
    private $level;
    //完全二叉树中最底层叶子结点数量（计算图像宽度时用到，论如何实现图片大小自适应）
    private $maxCount;

    //图像相关设置
    //画布宽度
    private $width;
    //画布高度
    private $height;
    //画布背景颜色（RGB）
    private $bg = array(
        220, 220, 220
    );
    //节点颜色（搜索二叉树和平衡二叉树时用）
    private $nodeColor = array(
        255, 192, 203
    );
    //图像句柄
    private $image;

    /**
     * 构造函数，类属性初始化
     * @param $tree 传递一个树的对象
     * @return null
     */
    public function __construct($tree)
    {
        $this->tree = $tree;
        $this->level = $this->getLevel();
        $this->maxCount = $this->GetMaxCount($this->level);
        $this->width = ($this->rad * 2 * $this->maxCount) + $this->maxCount * $this->leaf_width;
        $this->height = $this->level * ($this->rad * 2) + $this->level_high * ($this->level - 1) + $this->leave;
        //1.创建画布
        $this->image = imagecreatetruecolor($this->width, $this->height); //新建一个真彩色图像，默认背景是黑色
        //填充背景色
        $bgcolor = imagecolorallocate($this->image, $this->bg[0], $this->bg[1], $this->bg[2]);
        imagefill($this->image, 0, 0, $bgcolor);
    }

    /**
     * 返回传进来的树对象对应的完全二叉树中最底层叶子结点数量
     * @param $level 树的层数
     * @return 结点数量
     */
    function GetMaxCount($level)
    {
        return pow(2, $level - 1);
    }

    /**
     * 获取树对象的层数
     * @param null
     * @return 树的层数
     */
    function getLevel()
    {
        return $this->tree->Depth();
    }

    /**
     * 显示二叉树图像
     * @param null
     * @return null
     */
    public function show()
    {
        $this->draw($this->tree->root, 1, 0, 0);
        header("Content-type:image/png");
        imagepng($this->image);
        imagedestroy($this->image);
    }

    /**
     * （递归）画出二叉树的树状结构
     * @param $root，根节点（树或子树） $i，该根节点所处的层 $p_x，父节点的x坐标 $p_y,父节点的y坐标
     * @return null
     */
    private function draw($root, $i, $p_x, $p_y)
    {
        if ($i <= $this->level) {
            //当前节点的y坐标
            $root_y = $i * $this->rad + ($i - 1) * $this->level_high;
            //当前节点的x坐标
            if (!is_null($parent = $root->parent)) {
                if ($root == $parent->left) {
                    $root_x = $p_x - $this->width / (pow(2, $i));
                } else {
                    $root_x = $p_x + $this->width / (pow(2, $i));
                }
            } else {
                //根节点
                $root_x = (1 / 2) * $this->width;
                $root_y += $this->leave;
            }

            //画结点（确定所画节点的类型（平衡、红黑、排序）和方法）
            $method = 'draw' . get_class($this->tree) . 'Node';
            $this->$method($root_x, $root_y, $root);

            //将当前节点和父节点连线（黑色线）
            $black = imagecolorallocate($this->image, 0, 0, 0);
            if (!is_null($parent = $root->parent)) {
                imageline($this->image, $p_x, $p_y, $root_x, $root_y, $black);
            }

            //画左子节点
            if (!is_null($root->left)) {
                $this->draw($root->left, $i + 1, $root_x, $root_y);
            }
            //画右子节点
            if (!is_null($root->right)) {
                $this->draw($root->right, $i + 1, $root_x, $root_y);
            }
        }
    }

    /**
     * 画搜索二叉树结点
     * @param $x，当前节点的x坐标 $y，当前节点的y坐标 $node，当前节点的引用
     * @return null
     */
    private function drawBstNode($x, $y, $node)
    {
        //节点圆的线颜色
        $black = imagecolorallocate($this->image, 0, 0, 0);
        $nodeColor = imagecolorallocate($this->image, $this->nodeColor[0], $this->nodeColor[1], $this->nodeColor[2]);
        //画节点圆
        imageellipse($this->image, $x, $y, $this->rad * 2, $this->rad * 2, $black);
        //节点圆颜色填充
        imagefill($this->image, $x, $y, $nodeColor);
        //节点对应的数字
        imagestring($this->image, 4, $x, $y, $node->key, $black);
    }

    /**
     * 画平衡二叉树结点
     * @param $x，当前节点的x坐标 $y，当前节点的y坐标 $node，当前节点的引用
     * @return null
     */
    private function drawAvlNode($x, $y, $node)
    {
        $black = imagecolorallocate($this->image, 0, 0, 0);
        $nodeColor = imagecolorallocate($this->image, $this->nodeColor[0], $this->nodeColor[1], $this->nodeColor[2]);
        imageellipse($this->image, $x, $y, $this->rad * 2, $this->rad * 2, $black);
        imagefill($this->image, $x, $y, $nodeColor);
        imagestring($this->image, 4, $x, $y, $node->key . '(' . $node->bf . ')', $black);
    }

    /**
     * 画红黑树结点
     * @param $x，当前节点的x坐标 $y，当前节点的y坐标 $node，当前节点的引用
     * @return null
     */
    private function drawRbtNode($x, $y, $node)
    {
        $black = imagecolorallocate($this->image, 0, 0, 0);
        $gray = imagecolorallocate($this->image, 180, 180, 180);
        $pink = imagecolorallocate($this->image, 255, 192, 203);
        imageellipse($this->image, $x, $y, $this->rad * 2, $this->rad * 2, $black);
        if ($node->IsRed == TRUE) {
            imagefill($this->image, $x, $y, $pink);
        } else {
            imagefill($this->image, $x, $y, $gray);
        }
        imagestring($this->image, 4, $x, $y, $node->key, $black);
    }
}
```

好，现在我们来看看在客户端如何调用：
```php
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
```

注：这里用到的那三个树的类大家可以参照我的另外三篇博客：[《PHP二叉树（一）：二叉搜索树》][16]、[《PHP二叉树（二）：平衡二叉树（AVL）》][17]、[《PHP二叉树（三）：红黑树》][18]

[0]: http://www.csdn.net/baidu_30000217/article/details/52880578
[1]: http://www.csdn.net/tag/php
[2]: http://www.csdn.net/tag/%e4%ba%8c%e5%8f%89%e6%a0%91
[3]: http://www.csdn.net/tag/%e6%a0%91%e7%9a%84%e5%9b%be%e5%bd%a2%e5%8c%96
[8]: #
[9]: #t0
[10]: #t1
[11]: #t2
[12]: http://lib.csdn.net/base/php
[13]: ./img/20161026133143499.png
[14]: ./img/20161026133235829.png
[15]: ./img/20161026133333463.png
[16]: http://blog.csdn.net/baidu_30000217/article/details/52938495
[17]: http://blog.csdn.net/baidu_30000217/article/details/52938545
[18]: http://blog.csdn.net/baidu_30000217/article/details/52938622
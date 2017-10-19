# PHP数据结构之四：一元多项式的相加PHP单链实现

作者：小涵 | 来源：互联网 | 2013-08-12 15:47 

阅读: 1902 

PHP数据结构之四：一元多项式的相加PHP单链实现

```php

<?php
/**
 *一元多项式的表示和相加
 *一元多项式的表示采用单链表的形式
 **/
header("content-type:text/html;charset=UTF-8");
//该类可以在--PHP数据结构之三 线性表中的单链表的PHP实现--找到
include_once("./SingleLinkedList.class.php");
/**
 *一元多项式的相加实现算法：直接相加
 *@param SingleLinkedList $a 第一个多项式
 *@param SingleLinkedList $b 第二个多项式
 *@param SingleLinkedList $c 相加后的多项式
 *@return void
 **/
function polynomialaddition(&$a,&$b,&$c){
    if(!$a->getIsEmpty() && !$b->getIsEmpty()){
        $pa=$a->mNext;
        $pb=$b->mNext;
        while($pa!=null && $pb!=null){
            if($pa->mElem['expn'] < $pb->mElem['expn']){
                $c->getInsertElem($c->getLength(),$pa->mElem);
                $pc=$pa;
                $pa=$pa->mNext;
            }
            if($pa->mElem['expn'] > $pb->mElem['expn']){
                $c->getInertElem($c->getLength(),$pb->mElem);
                $pc=$pb;
                $pb=$pb->mNext;
            }
            if($pa->mElem['expn'] == $pa->mElem['expn']){
                $ccoef=$pa->mElem['coef']+$pb->mElem['coef'];
                if(abs($ccoef) < 1.0E-10){
                    $pa=$pa->mNext;
                    $pb=$pb->mNext;
                }else{
                    $pa->mElem['coef']=$ccoef;
                    $c->getInsertElem($c->getLength(),$pa->mElem);
                    $pa=$pa->mNext;
                    $pb=$pb->mNext;
                }
            }
        }
        if($pa==null){
            while($pb!=null){
                $c->getInsertElem($c->getLength(),$pb->mElem);
                $pb=$pb->mNext;
            }
        }elseif($pb==null){
            while($pa!=null){
                $c->getInsertElem($c->getLength(),$pa->mElem);
                $pa=$pa->mNext;
            }
        }
    }
}
//coef表示系数
//expn表示指数
$adata=array(
    array('coef'=>5,
        'expn'=>0,
    ),
    array('coef'=>2,
        'expn'=>1,
    ),
    array('coef'=>4,
        'expn'=>2,
    ),
    array('coef'=>13,
        'expn'=>3,
    ),
    array('coef'=>160,
        'expn'=>4,
    ),
    array('coef'=>12,
        'expn'=>5
    ),
    array('coef'=>23,
        'expn'=>6,
    ),
    array('coef'=>34,
        'expn'=>7,
    ),
    array('coef'=>22,
        'expn'=>8,
    ),
);
$bdata=array(
    array('coef'=>16,
        'expn'=>0,
    ),
    array('coef'=>9,
        'expn'=>2,
    ),
    array('coef'=>17,
        'expn'=>4,
    ),
    array('coef'=>-12,
        'expn'=>5,
    ),
);
$a=new SingleLinkedList();
$b=new SingleLinkedList();
$c=new SingleLinkedList();
$a->getTailCreateSLL($adata);
$b->getTailCreateSLL($bdata);
polynomialaddition($a,$b,$c);
echo "\$a多项式的数据为：<pre>";
var_dump($a->getAllElem());
echo "</pre>";
echo "\$b多项式的数据为：<pre>";
var_dump($b->getAllElem());
echo "</pre>";
echo "一元多项式相加结果：<pre>";
var_dump($c->getAllElem());
echo "</pre>";
?>

```


<?php
 
//基本文章类
 
class BaseArt{
 
    //声明文章对象与基本文章
 
    protected $ObjArt,$content;
 
    //构造方法传最基本的文章
 
    public function __construct($content){
        $this->content = $content;
    }
 
    public function decorator(){
        return $this->content;
    }
 
}
 
//编辑类
 
class Editor extends BaseArt{
 
    public function __construct($ObjArt){
         $this->ObjArt = $ObjArt; 
         $this->decorator();
    }
 
    public function decorator(){
        return $this->content = $this->ObjArt->content. '#编辑已添加导读';
    }
 
}
 
//审核组类
 
class Auditor extends BaseArt{
 
    public function __construct($ObjArt){
         $this->ObjArt = $ObjArt;
         $this->decorator();
    }
 
    public function decorator(){
        return $this->content = $this->ObjArt->content. '#审核组已阅';
    }
 
}
 
//市场部类
 
class Market extends BaseArt{
 
    public function __construct($ObjArt){
         $this->ObjArt = $ObjArt; 
         $this->decorator();
    }
 
    public function decorator(){
        return $this->content = $this->ObjArt->content. '#市场部已加广告';
    }
 
}
 
$Art = new Market(new Auditor (new Editor (new BaseArt('#基本文章'))));
print_r($Art->decorator());
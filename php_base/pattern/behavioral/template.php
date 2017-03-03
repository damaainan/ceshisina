<?php 
/**
 * 模板模式
---------------

现实例子
> 假设我们要建房子。建造的步骤类似这样 
> - 准备房子的地基
> - 建造墙
> - 建造房顶
> - 然后是地板
> 这些步骤步骤的顺序永远不会变，即你不能在建墙之前建屋顶，当时每个步骤都可以改变，比如墙可以是木头可以是聚酯或者石头。
  
白话
> 模板模式定义了一个算法会如何执行的骨架，但把这些步骤的实现移交给子类。
 */



abstract class Builder {
    
    // Template method 
    public final function build() {
        $this->test();
        $this->lint();
        $this->assemble();
        $this->deploy();
    }
    
    public abstract function test();
    public abstract function lint();
    public abstract function assemble();
    public abstract function deploy();
}


class AndroidBuilder extends Builder {
    public function test() {
        echo 'Running android tests';
    }
    
    public function lint() {
        echo 'Linting the android code';
    }
    
    public function assemble() {
        echo 'Assembling the android build';
    }
    
    public function deploy() {
        echo 'Deploying android build to server';
    }
}

class IosBuilder extends Builder {
    public function test() {
        echo 'Running ios tests';
    }
    
    public function lint() {
        echo 'Linting the ios code';
    }
    
    public function assemble() {
        echo 'Assembling the ios build';
    }
    
    public function deploy() {
        echo 'Deploying ios build to server';
    }
}


$androidBuilder = new AndroidBuilder();
$androidBuilder->build();

// 输出:
// Running android tests
// Linting the android code
// Assembling the android build
// Deploying android build to server

$iosBuilder = new IosBuilder();
$iosBuilder->build();

// 输出:
// Running ios tests
// Linting the ios code
// Assembling the ios build
// Deploying ios build to server
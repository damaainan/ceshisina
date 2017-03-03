<?php 
/**
 * 状态模式
-----
现实例子
> 想象你在使用画图程序，你选择笔刷来画。现在笔刷根据选择的颜色改变自己的行为。即如果你选择红色，它就用红色画，如果是蓝色它就用蓝色等等。  

白话
> 他让你能类的状态改变时，改变其行为。
 */


interface WritingState {
    public function write(string $words);
}

class UpperCase implements WritingState {
    public function write(string $words) {
        echo strtoupper($words); 
    }
} 

class LowerCase implements WritingState {
    public function write(string $words) {
        echo strtolower($words); 
    }
}

class Default implements WritingState {
    public function write(string $words) {
        echo $words;
    }
}



class TextEditor {
    protected $state;
    
    public function __construct(WritingState $state) {
        $this->state = $state;
    }
    
    public function setState(WritingState $state) {
        $this->state = $state;
    }
    
    public function type(string $words) {
        $this->state->write($words);
    }
}


$editor = new TextEditor(new Default());

$editor->type('First line');

$editor->setState(new UpperCaseState());

$editor->type('Second line');
$editor->type('Third line');

$editor->setState(new LowerCaseState());

$editor->type('Fourth line');
$editor->type('Fifth line');

// 输出:
// First line
// SECOND LINE
// THIRD LINE
// fourth line
// fifth line
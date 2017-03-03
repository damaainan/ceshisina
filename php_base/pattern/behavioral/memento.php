<?php 
/**
 * 备忘录模式
-------
现实例子
> 以计算器（即发起人）为例，无论什么时候你执行一些计算，最后的计算都会保存在内存（即备忘）里，这样你就能返回到这里，并且用一些按钮（即守护者）恢复。 

白话
> 备忘录模式捕捉和保存当前对象的状态，然后用一种平滑的方式恢复。
 */



class EditorMemento {
    protected $content;
    
    public function __construct(string $content) {
        $this->content = $content;
    }
    
    public function getContent() {
        return $this->content;
    }
}


class Editor {
    protected $content = '';
    
    public function type(string $words) {
        $this->content = $this->content . ' ' . $words;
    }
    
    public function getContent() {
        return $this->content;
    }
    
    public function save() {
        return new EditorMemento($this->content);
    }
    
    public function restore(EditorMemento $memento) {
        $this->content = $memento->getContent();
    }
}

//使用
$editor = new Editor();

// 输入一些东西
$editor->type('This is the first sentence.');
$editor->type('This is second.');

// 保存状态到：This is the first sentence. This is second.
$saved = $editor->save();

// 输入些别的东西
$editor->type('And this is third.');

// 输出: Content before Saving
echo $editor->getContent(); // This is the first sentence. This is second. And this is third.

// 恢复到上次保存状态
$editor->restore($saved);

$editor->getContent(); // This is the first sentence. This is second.
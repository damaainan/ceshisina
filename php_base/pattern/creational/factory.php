<?php 
/**
 * 工厂方法模式
--------------

现实例子
> 设想一个人事经理。一个人是不可能面试所有职位的。基于职位空缺，她必须把面试委托给不同的人。

白话
> 它提供了一个把生成逻辑移交给子类的方法。
 */

//一个面试官接口和一些实现
interface Interviewer {
    public function askQuestions();
}

class Developer implements Interviewer {
    public function askQuestions() {
        echo 'Asking about design patterns!';
    }
}

class CommunityExecutive implements Interviewer {
    public function askQuestions() {
        echo 'Asking about community building';
    }
}

//人事经理
abstract class HiringManager {
    
    // Factory method
    abstract public function makeInterviewer() : Interviewer;
    
    public function takeInterview() {
        $interviewer = $this->makeInterviewer();
        $interviewer->askQuestions();
    }
}

//生成需要的面试官
class DevelopmentManager extends HiringManager {
    public function makeInterviewer() : Interviewer {
        return new Developer();
    }
}

class MarketingManager extends HiringManager {
    public function makeInterviewer() : Interviewer {
        return new CommunityExecutive();
    }
}

//使用
$devManager = new DevelopmentManager();
$devManager->takeInterview(); 

$marketingManager = new MarketingManager();
$marketingManager->takeInterview(); 
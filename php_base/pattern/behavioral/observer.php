<?php 
/**
 * 观察者模式
--------
现实例子
> 一个好的例子是求职者，他们订阅了一些工作发布网站，当有合适的工作机会时，他们会收到提醒。   

白话
> 定义了一个对象间的依赖，这样无论何时一个对象改变了状态，其他所有依赖者会收到提醒。
 */


class JobPost {
    protected $title;
    
    public function __construct(string $title) {
        $this->title = $title;
    }
    
    public function getTitle() {
        return $this->title;
    }
}

class JobSeeker implements Observer {
    protected $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function onJobPosted(JobPost $job) {
        // Do something with the job posting
        echo 'Hi ' . $this->name . '! New job posted: '. $job->getTitle();
    }
}


class JobPostings implements Observable {
    protected $observers = [];
    
    protected function notify(JobPost $jobPosting) {
        foreach ($this->observers as $observer) {
            $observer->onJobPosted($jobPosting);
        }
    }
    
    public function attach(Observer $observer) {
        $this->observers[] = $observer;
    }
    
    public function addJob(JobPost $jobPosting) {
        $this->notify($jobPosting);
    }
}


// 创建订阅者
$johnDoe = new JobSeeker('John Doe');
$janeDoe = new JobSeeker('Jane Doe');
$kaneDoe = new JobSeeker('Kane Doe');

// 创建发布者，绑定订阅者
$jobPostings = new JobPostings();
$jobPostings->attach($johnDoe);
$jobPostings->attach($janeDoe);

// 添加一个工作，看订阅者是否收到通知
$jobPostings->addJob(new JobPost('Software Engineer'));

// 输出
// Hi John Doe! New job posted: Software Engineer
// Hi Jane Doe! New job posted: Software Engineer
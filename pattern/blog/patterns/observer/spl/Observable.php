<?php
namespace spl;

/**
 * 被观察者实体类
 *
 * 实现附加观察者，删除观察者，通知观察者方法
 */
class Observable implements \SplSubject
{
    /**
     * 观察者们
     * @var array
     */
    private $observers = [];

    private $message = "通知消息";

    /**
     * 附加观察者
     * @param \SplObserver $observer
     * @return void
     */
    public function attach(\SplObserver $observer)
    {
        if (!in_array($observer, $this->observers, true)) {
            $this->observers[] = $observer;
        }
    }

    /**
     * 解除观察者
     * @param \SplObserver $observer
     * @return void
     */
    public function detach(\SplObserver $observer)
    {
        foreach ($this->observers as $k => $v) {
            if ($v === $observer) {
                unset($this->observers[$k]);
            }
        }
    }

    /**
     * 通知观察者
     * @return void
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }


    /**
     * 获取消息
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($msg)
    {
        $this->message = $msg;
    }
}

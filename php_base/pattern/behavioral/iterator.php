<?php 
/**
 * 迭代器模式
--------

现实例子
> 老式调频收音机是迭代器的好例子，用户可以在一些频道开始，然后使用前进或后退按钮来浏览每个频道。或者以 MP3 播放器或电视机为例，你可以按前进或后退按钮来浏览连续的频道。或者说，它们都提供了迭代连续的频道，歌曲或广播的接口。  

白话
> 它提供了一种方式来获得对象的元素，而不必暴露底层实现。



Station  类不在这里
 */
// use \Countable;
// use \Iterator;


class RadioStation {
    protected $frequency;

    public function __construct(float $frequency) {
        $this->frequency = $frequency;    
    }
    
    public function getFrequency() : float {
        return $this->frequency;
    }
}





class StationList implements Countable, Iterator {
    /** @var RadioStation[] $stations */
    protected $stations = [];
    
    /** @var int $counter */
    protected $counter;
    
    public function addStation(RadioStation $station) {
        $this->stations[] = $station;
    }
    
    public function removeStation(RadioStation $toRemove) {
        $toRemoveFrequency = $toRemove->getFrequency();
        $this->stations = array_filter($this->stations, function (RadioStation $station) use ($toRemoveFrequency) {
            return $station->getFrequency() !== $toRemoveFrequency;
        });
    }
    
    public function count() : int {
        return count($this->stations);
    }
    
    public function current() : RadioStation {
        return $this->stations[$this->counter];
    }
    
    public function key() {
        return $this->counter;
    }
    
    public function next() {
        $this->counter++;
    }
    
    public function rewind() {
        $this->counter = 0;
    }
    
    public function valid(): bool
    {
        return isset($this->stations[$this->counter]);
    }
}



$stationList = new StationList();

$stationList->addStation(new RadioStation(89));
$stationList->addStation(new RadioStation(101));
$stationList->addStation(new RadioStation(102));
$stationList->addStation(new RadioStation(103.2));

foreach($stationList as $station) {
    echo $station->getFrequency() . PHP_EOL;
}

$stationList->removeStation(new RadioStation(89)); // Will remove station 89
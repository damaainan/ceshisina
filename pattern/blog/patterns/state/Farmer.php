<?php
namespace state;

use Exception;

/**
 * 农民类
 */
class Farmer
{
  /**
   * 当前季节
   *
   * @var string
   */
    private $currentSeason = '';

  /**
   * 季节
   * @var string
   */
    private $season = [
    'spring',
    'summer',
    'autumn',
    'winter'
    ];

  /**
   * 状态
   * @var object
   */
    private $state;

  /**
   * 设置状态
   * @param Farm $farm 种植方法
   */
    private function setState($currentSeason)
    {
        if ($currentSeason === 'spring') {
            $this->state = new FarmSpring();
        }
        if ($currentSeason === 'summer') {
            $this->state = new FarmSummer();
        }
        if ($currentSeason === 'autumn') {
            $this->state = new FarmAutumn();
        }
        if ($currentSeason === 'winter') {
            $this->state = new FarmWinter();
        }
    }

  /**
   * 设置下个季节状态
   */
    private function nextSeason()
    {
        $nowKey = (int)array_search($this->currentSeason, $this->season);
        if ($nowKey < 3) {
            $nextSeason = $this->season[$nowKey+1];
        } else {
            $nextSeason = 'spring';
        }
        $this->currentSeason = $nextSeason;
        $this->setState($this->currentSeason);
    }

  /**
   * 设置初始状态
   */
    public function __construct($season = 'spring')
    {
        $this->currentSeason = $season;
        $this->setState($this->currentSeason);
    }

  /**
   * 种植
   *
   * @return string
   */
    public function grow()
    {
        $this->state->grow();
    }

  /**
   * 收割
   *
   * @return string
   */
    public function harvest()
    {
        $this->state->harvest();
      // 设置下一个季节状态
        $this->nextSeason();
    }
}

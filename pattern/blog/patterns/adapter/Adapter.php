<?php
namespace adapter;

use Exception;

/**
 * 高级播放器适配器
 */
class Adapter
{
    private $advancePlayerInstance;

    private $type = '';

    public function __construct($type = '')
    {
        switch ($type) {
            case 'mp4':
                $this->advancePlayerInstance = new AdvanceMp4Player();
                break;
            case 'wma':
                $this->advancePlayerInstance = new AdvanceWmaPlayer();
                break;

            default:
                throw new Exception("$type is not supported", 400);
            break;
        }
        $this->type = $type;
    }

    public function play($file = '')
    {
        switch ($this->type) {
            case 'mp4':
                $this->advancePlayerInstance->playMp4($file);
                break;
            case 'wma':
                $this->advancePlayerInstance->playWma($file);
                break;
            default:
                break;
        }
    }
}

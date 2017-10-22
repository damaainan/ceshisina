<?php
/**
 * 接口
 */
interface IDeviceWriter
{
    public function saveToDevice();
}

/**
 * 高层
 */
class Business
{
    /**
     * @var IDeviceWriter
     */
    private $writer;

    /**
     * @param IDeviceWriter $writer
     */
    public function setWriter($writer)
    {
        $this->writer = $writer;
    }

    public function save()
    {
        $this->writer->saveToDevice();
    }
}

/**
 * 低层，软盘存储
 */
class FloppyWriter implements IDeviceWriter
{

    public function saveToDevice()
    {
        echo __METHOD__;
    }
}

/**
 * 低层，USB盘存储
 */
class UsbDiskWriter implements IDeviceWriter
{

    public function saveToDevice()
    {
        echo __METHOD__;
    }
}

$biz = new Business();
$biz->setWriter(new UsbDiskWriter());
$biz->save(); // UsbDiskWriter::saveToDevice

$biz->setWriter(new FloppyWriter());
$biz->save(); // FloppyWriter::saveToDevice

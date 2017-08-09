<?php

namespace Builder;

/**
 * Director 是建造者模式的一部分，它知道建造者接口并通过建造者构建复杂对象。
 *
 * 可以通过依赖注入建造者的方式构造任何复杂对象
 */
class Director
{

    /**
     * “导演”并不知道具体实现细节
     *
     * @param BuilderInterface $builder
     *
     * @return Parts\Vehicle
     */
    public function build(BuilderInterface $builder)
    {
        $builder->createVehicle();
        $builder->addDoors();
        $builder->addEngine();
        $builder->addWheel();

        return $builder->getVehicle();
    }
}
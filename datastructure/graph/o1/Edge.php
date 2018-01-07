<?php
namespace DataStructure;

/**
 * 边
 */
class Edge
{

    /**
     * @var Vertex $source
     */
    private $source;

    /**
     * @var Vertex $target
     */
    private $target;

    /**
     * @var float $weight
     */
    private $weight;

    /**
     * @return Vertex
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return Vertex
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Edge constructor.
     * @param Vertex $source
     * @param Vertex $target
     * @param float $weight
     */
    public function __construct(Vertex $source, Vertex $target, $weight)
    {
        if (false === is_numeric($weight)) {
            throw new \Exception('权重必须是数字');
        }
        if ($source == $target) {
            throw new \Exception('起点和终点不能相同');
        }
        //始终把序号小的顶点作为无向图的起始，序号大的顶点作为无向图的终点
        $min = min($source->getNumber(), $target->getNumber());
        $max = max($source->getNumber(), $target->getNumber());
        $source->setNumber($min);
        $target->setNumber($max);
        $this->source = $source;
        $this->target = $target;
        $this->weight = $weight;
    }

    public function __toString()
    {
        return $this->getSource()->getNumber() . ',' . $this->getTarget()->getNumber() . ',' . $this->getWeight();
    }

}
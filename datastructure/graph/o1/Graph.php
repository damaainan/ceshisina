<?php
namespace DataStructure;

/**
 * 图
 */
class Graph
{

    private $edgeContainer = [];

    private $vertexContainer = [];

    /**
     * @return array
     */
    public function getVertexContainer()
    {
        return $this->vertexContainer;
    }

    /**
     * @param Edge $edge
     * @throws \Exception
     */
    public function addEdge(Edge $edge)
    {
        //遍历图，判断添加的边是否构成连通图
        $edgeSourceValue = $edge->getSource()->getNumber();
        $edgeTargetValue = $edge->getTarget()->getNumber();
        $edgeWeight = $edge->getWeight();
        if ($this->isEdgeExist($edge)) {
            $remind = "添加边($edgeSourceValue,$edgeTargetValue,$edgeWeight)失败,边已经存在";
            throw new \Exception($remind);
        }
        if (false === $this->isVertexExist($edge->getSource())
            && false == $this->isVertexExist($edge->getTarget())
            && 0 < count($this->edgeContainer)
        ) {
            $remind = "添加边($edgeSourceValue,$edgeTargetValue,$edgeWeight)失败，每次添加边后必须构成连通图";
            throw new \Exception($remind);
        }
        $this->vertexContainer[$edgeSourceValue] = $edge->getSource();
        $this->vertexContainer[$edgeTargetValue] = $edge->getTarget();
        array_push($this->edgeContainer, $edge);
    }

    /**
     * @param Vertex $vertex
     * @return bool
     */
    public function isVertexExist(Vertex $vertex)
    {
        return isset($this->vertexContainer[$vertex->getNumber()]);
    }

    /**
     * @param Edge $edge
     * @return bool
     */
    public function isEdgeExist(Edge $edge)
    {
        /**@var Edge $item */
        foreach ($this->edgeContainer as $item) {
            if ($edge->getSource()->getNumber() == $item->getSource()->getNumber()
                && $edge->getTarget()->getNumber() == $item->getTarget()->getNumber()
                && $edge->getWeight() == $item->getWeight()
            ) {

                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getGraph()
    {
        return $this->edgeContainer;
    }

    /**
     * @return int
     */
    public function getEdgeCount()
    {
        return count($this->edgeContainer);
    }

    /**
     * 获取最小权值的边
     * @return Edge|null
     */
    public function getMinWeightEdge()
    {
        if ($this->getEdgeCount() <= 0) {
            return null;
        }
        $minWeightEdge = reset($this->edgeContainer);
        /**@var Edge $edge */
        foreach ($this->edgeContainer as $edge) {
            if ($edge->getWeight() < $minWeightEdge->getWeight()) {
                $minWeightEdge = $edge;
            }
        }
        return $minWeightEdge;
    }
}
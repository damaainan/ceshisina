<?php
namespace DataStructure;

class Tree
{
    /**
     * @var Graph $graph
     */
    private $graph;

    /**
     * @var array $treeContainer
     */
    private $treeContainer = [];

    /**
     * @var array $vertexes
     */
    private $vertexes = [];

    /**
     * Tree constructor.
     * @param Graph $graph
     */
    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    public function getMinimalSpanningTree()
    {
        $graphEdgeCount = $this->graph->getEdgeCount();
        for ($i = 0; $i < $graphEdgeCount; $i++) {
            $this->addMinimalEdge();
        }
        return $this->treeContainer;
    }

    /**
     * 添加最小权值的边
     */
    public function addMinimalEdge()
    {
        if (0 == count($this->treeContainer) && 0 < $this->graph->getEdgeCount()) {
            $minWeightEdge = $this->graph->getMinWeightEdge();
            $this->vertexes[] = $minWeightEdge->getSource();
            $this->vertexes[] = $minWeightEdge->getTarget();
            $this->treeContainer[] = $minWeightEdge;
        } else {
            $vertexConnectEdges = [];
            foreach ($this->vertexes as $vertex) {
                $vertexConnectEdges = array_merge($vertexConnectEdges, $this->getConnectEdgesByVertex($vertex));
            }
            $vertexConnectEdges = array_unique($vertexConnectEdges);
            /**@var Edge $minWeightEdge */
            $minWeightEdge = $this->getMinimalConnectEdge($vertexConnectEdges);
            /**@var Edge $edge */
            if ($minWeightEdge instanceof Edge) {
                $this->vertexes[] = $minWeightEdge->getSource();
                $this->vertexes[] = $minWeightEdge->getTarget();
                $this->treeContainer[] = $minWeightEdge;
            }
        }
    }

    /**
     * @param Edge $edge
     * @return bool
     */
    private function isHaveCircuit(Edge $edge)
    {
        $vertexNumbers = [];
        /**@var Vertex $vertex */
        foreach ($this->vertexes as $vertex) {
            $vertexNumbers [] = $vertex->getNumber();
        }
        return in_array($edge->getSource()->getNumber(), $vertexNumbers)
        && in_array($edge->getTarget()->getNumber(), $vertexNumbers);
    }

    /**
     * 获取权值最小的边
     * @param array $edges
     * @return Edge|null
     */
    private function getMinimalConnectEdge(array $edges)
    {
        if (!is_array($edges)) {
            return null;
        }
        //把树的边排除掉
        /**@var Edge $edge */
        foreach ($edges as &$connectEdge) {
            foreach ($this->treeContainer as $edge) {
                if ($connectEdge == $edge) {
                    $connectEdge = null;
                }
            }
        }
        $edges = array_filter($edges);
        //按照权值从小到大升续排列，冒泡法
        /**@var Edge $edgeOne */
        /**@var Edge $edgeTwo */
        foreach ($edges as &$edgeOne) {
            foreach ($edges as &$edgeTwo) {
                if ($edgeOne->getWeight() < $edgeTwo->getWeight()) {
                    $swap = $edgeTwo;
                    $edgeTwo = $edgeOne;
                    $edgeOne = $swap;
                }
            }
        }
        foreach ($edges as $edge) {
            if (false == ($edge instanceof Edge)) {
                continue;
            }
            if (false == $this->isHaveCircuit($edge)) {
                return $edge;
            }
        }
        return null;
    }

    /**
     * 根据点获取相邻的所有边
     * @param \DataStructure\Vertex $vertex
     * @return array
     */
    private function getConnectEdgesByVertex(Vertex $vertex)
    {
        $connectEdges = [];
        //获取点的相邻边
        /**@var Edge $edge */
        foreach ($this->graph->getGraph() as $edge) {
            if ($edge->getSource() == $vertex || $edge->getTarget() == $vertex) {
                $connectEdges[] = $edge;
            }
        }
        return $connectEdges;
    }
}
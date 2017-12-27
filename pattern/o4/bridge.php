<?php
interface DrawingAPI {
    public function drawCircle($x, $y, $radius);
}

/**
 * drawAPI1
 */
class DrawingAPI1 implements DrawingAPI {
    public function drawCircle($x, $y, $radius) {
        echo "API1.circle at (" . $x . ',' . $y . ') radius ' . $radius . '<br>';
    }
}

/**
 * drawAPI2
 */
class DrawingAPI2 implements DrawingAPI {
    public function drawCircle($x, $y, $radius) {
        echo "API2.circle at (" . $x . ',' . $y . ') radius ' . $radius . '<br>';
    }
}

/**
 *shape接口
 */
interface Shape {
    public function draw();
    public function resize($radius);
}

class CircleShape implements Shape {
    private $x;
    private $y;
    private $radius;
    private $drawingAPI;
    function __construct($x, $y, $radius, DrawingAPI $drawingAPI) {
        $this->x = $x;
        $this->y = $y;
        $this->radius = $radius;
        $this->drawingAPI = $drawingAPI;
    }

    public function draw() {
        $this->drawingAPI->drawCircle($this->x, $this->y, $this->radius);
    }

    public function resize($radius) {
        $this->radius = $radius;
    }
}

$shape1 = new CircleShape(1, 2, 4, new DrawingAPI1());
$shape2 = new CircleShape(1, 2, 4, new DrawingAPI2());
$shape1->draw();
$shape2->draw();
$shape1->resize(10);
$shape1->draw();

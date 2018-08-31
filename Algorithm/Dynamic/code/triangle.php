<?php
//Input
$matrix2 = array(array(2),
    array(3, 5),
    array(6, 3, 4),
    array(5, 6, 1, 4),
);
//Utility Class File Iterator
class FileIterator
{
    //the filename
    public $filename = null;
    //declare a handler for file which is currently NIL
    public $handler = null;
    //constructor of class
    public function __construct($filename)
    {
        $this->filename = $filename;
    }
    public function fetch()
    {
        if (!isset($this->handler)) {
            //prepare the file to read
            $this->handler = fopen($this->filename, 'r');
        }
        if (($line = fgets($this->handler, 4096)) != false) {
            return $line;
        } else {
            fclose($this->handler);
            $this->handler = null;
        }
    }
}
//Dymamic Programming Application Triangle
$matrix = array();
$file   = new FileIterator('triangle.in');
$inputs = array();
while (($line = $file->fetch()) != false) {
    array_push($inputs, $line);
}
$N = intVal($inputs[0]);
for ($i = 0; $i < $N; $i++) {

    $temp     = array_map("intVal", explode(" ", $inputs[$i + 1]));
    $matrix[] = $temp;
}
function writeTriangle()
{
    global $N, $matrix;

    for ($i = 0; $i < $N; $i++) {
        if (is_array($matrix[$i])) {
            foreach ($matrix[$i] as $value) {
                echo $value . " ";
            }
        }
        echo "<br/>";
    }
}
writeTriangle();
$n = count($matrix) - 1;
$T = array();
$R = array();
for ($i = 0; $i <= $n; $i++) {
    $T[$n][$i] = $matrix[$n][$i];
}
for ($line = $n - 1; $line >= 0; $line--) {

    for ($col = 0; $col <= $line; $col++) {
        if ($matrix[$line][$col] + $T[$line + 1][$col] > $matrix[$line][$col] + $T[$line + 1][$col + 1]) {
            $T[$line][$col] = $matrix[$line][$col] + $T[$line + 1][$col];
            $R[$line][$col] = $col;
        } else {
            $T[$line][$col] = $matrix[$line][$col] + $T[$line + 1][$col + 1];
            $R[$line][$col] = $col + 1;
        }

    }
}
echo "The Max Sum = ", $T[0][0], "<br/>";
$i = 0;
$j = 0;
while ($i <= $n) {
    echo $matrix[$i][$j] . " | ";
    // var_dump($R);
    $j = $R[$i][$j];
    $i++;
}
function debug()
{
    global $T, $R;
    echo "<pre>";
    print_r($T);
    echo "</pre>";
    echo "<pre>";
    print_r($R);
    echo "</pre>";
}

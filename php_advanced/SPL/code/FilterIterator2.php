<?php

class PrimeFilter extends FilterIterator
{

/*** The filteriterator takes  a iterator as param: ***/
    public function __construct(Iterator $it)
    {
        parent::__construct($it);
    }

/*** check if current value is prime ***/
    public function accept()
    {
        if ($this->current() % 2 != 1) {
            return false;
        }
        $d = 3;
        $x = sqrt($this->current());
        while ($this->current() % $d != 0 && $d < $x) {
            $d += 2;
        }
        return (($this->current() % $d == 0 && $this->current() != $d) * 1) == 0 ? true : false;
    }

} /*** end of class ***/

/*** an array of numbers ***/
$numbers = range(212345, 212456);

/*** create a new FilterIterator object ***/
$primes = new primeFilter(new ArrayIterator($numbers));

foreach ($primes as $value) {
    echo $value . ' is prime.<br />';
}

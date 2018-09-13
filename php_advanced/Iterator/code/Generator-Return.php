<?php
$traverser = (function () {
  yield "foo";
  yield "bar";
  return "value";
})();

$traverser->getReturn();  // Exception with message 'Cannot get return value of a generator that hasn't returned'

foreach ($traverser as $value) {
    echo "{$value}", PHP_EOL;
}

$traverser->getReturn();  // "value"
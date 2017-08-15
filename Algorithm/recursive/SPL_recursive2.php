<?php

$teams = array(
    'Popular Football Teams',
    array(
        'La Lega',
        array('Real Madrid', 'FC Barcelona', 'Athletico Madrid', 'RealBetis', 'Osasuna'),
    ),
    array(
        'English Premier League',
        array('Manchester United', 'Liverpool', 'Manchester City', 'Arsenal', 'Chelsea'),
    ),
);
$tree = new RecursiveTreeIterator(
    new RecursiveArrayIterator($teams), null, null, RecursiveIteratorIterator::LEAVES_ONLY
);
foreach ($tree as $leaf) {
    echo $leaf . PHP_EOL;
}

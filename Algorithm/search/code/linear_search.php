<?php

/**

Best time complexity O(1)
Worst time complexity O(n)
Average time complexity O(n)
Space complexity (worst case) O(1)



 */

function search(array $numbers, int $needle): bool
{
    $totalItems = count($numbers);
    for ($i = 0; $i < $totalItems; $i++) {
        if ($numbers[$i] === $needle) {
            return true;
        }
    }
    return false;
}

$numbers = range(1, 200, 5);
if (search($numbers, 31)) {
    echo "Found";
} else {
    echo "Not found";
}

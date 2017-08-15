<?php

/**
 *        Heapsort(A as array)
 *            BuildHeap(A)
 *            for i = n-1 to 0
 *                swap(A[0], A[i])
 *                n = n - 1
 *                Heapify(A, 0)
 *
 *        BuildHeap(A as array)
 *            n = elements_in(A)
 *            for i = floor(n/2) to 0
 *                Heapify(A,i)
 *
 *        Heapify(A as array, i as int)
 *            left = 2i+1
 *            right = 2i+2
 *            max = i
 *
 *            if (left <= n) and (A[left] > A[i])
 *                max = left
 *
 *            if (right<=n) and (A[right] > A[max])
 *                max = right
 *
 *            if (max != i)
 *                swap(A[i], A[max])
 *                Heapify(A, max)
 *
 *

 */

function heapSort(array &$a)
{
    $length = count($a);
    buildHeap($a);
    $heapSize = $length - 1;
    for ($i = $heapSize; $i >= 0; $i--) {
        $tmp          = $a[0];
        $a[0]         = $a[$heapSize];
        $a[$heapSize] = $tmp;
        $heapSize--;
        heapify($a, 0, $heapSize);
    }
}
function buildHeap(array &$a)
{
    $length   = count($a);
    $heapSize = $length - 1;
    for ($i = ($length / 2); $i >= 0; $i--) {
        heapify($a, $i, $heapSize);
    }
}
function heapify(array &$a, int $i, int $heapSize)
{
    $largest = $i;
    $l       = 2 * $i + 1;
    $r       = 2 * $i + 2;
    if ($l <= $heapSize && $a[$l] > $a[$i]) {
        $largest = $l;
    }
    if ($r <= $heapSize && $a[$r] > $a[$largest]) {
        $largest = $r;
    }
    if ($largest != $i) {
        $tmp         = $a[$i];
        $a[$i]       = $a[$largest];
        $a[$largest] = $tmp;
        heapify($a, $largest, $heapSize);
    }
}

$numbers = [37, 44, 34, 65, 26, 86, 143, 129, 9];
heapSort($numbers);
echo implode("\t", $numbers);
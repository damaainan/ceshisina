/*
https://www.codewars.com/kata/linked-lists-merge-sort

Linked Lists - Merge Sort

Write a MergeSort() function which recursively sorts a list in ascending order. Note that this
problem requires recursion. Given FrontBackSplit() and SortedMerge(), you can write a classic
recursive MergeSort(). Split the list into two smaller lists, recursively sort those lists, and
finally merge the two sorted lists together into a single sorted list. Return the list.

    var list = 4 -> 2 -> 1 -> 3 -> 8 -> 9 -> null
    mergeSort(list) === 1 -> 2 -> 3 -> 4 -> 8 -> 9 -> null

FrontBackSplit() and SortedMerge() need not be redefined. You may call these functions in your
solution.

These function names will depend on the accepted naming conventions of language you are using. In
Python, FrontBackSplit() is actually front_back_split(). In JavaScript, it is frontBackSplit(), etc.
*/

const { Node } = require('./00-utils')
const { frontBackSplit } = require('./12-front-back-split')
const { sortedMerge } = require('./14-sorted-merge')

/*
 * Recursive version
 */
function mergeSort(list) {
  if (!list || !list.next) return list

  const first = new Node()
  const second = new Node()
  frontBackSplit(list, first, second)

  return sortedMerge(mergeSort(first), mergeSort(second))
}

module.exports = {
  mergeSort,
}

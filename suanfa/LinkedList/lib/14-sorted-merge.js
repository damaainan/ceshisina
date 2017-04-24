/*
https://www.codewars.com/kata/linked-lists-sorted-merge

Linked Lists - Sorted Merge

Write a SortedMerge() function that takes two lists, each of which is sorted in increasing order,
and merges the two together into one list which is in increasing order. SortedMerge() should return
the new list. The new list should be made by splicing together the nodes of the first two lists.
Ideally, SortedMerge() should only make one pass through each list. SortedMerge() is tricky to get
right and it may be solved iteratively or recursively.

    var first = 2 -> 4 -> 6 -> 7 -> null
    var second = 1 -> 3 -> 5 -> 6 -> 8 -> null
    sortedMerge(first, second) === 1 -> 2 -> 3 -> 4 -> 5 -> 6 -> 6 -> 7 -> 8 -> null

There are many cases to deal with: either 'first' or 'second' may be null/None/nil, during
processing either 'first' or 'second' may run out first, and finally there's the problem of
starting the result list empty, and building it up while going through 'first' and 'second'.

If one of the argument lists is null, the returned list should be the other linked list (even if it
is also null). No errors need to be thrown in SortedMerge().

Try doing Linked Lists - Shuffle Merge before attempting this problem.
*/

const { Node } = require('./00-utils')

/*
 * The recursive version
 */
function sortedMerge(first, second) {
  if (!first || !second) return first || second

  if (first.data <= second.data) {
    return new Node(first.data, sortedMerge(first.next, second))
  } else {
    return new Node(second.data, sortedMerge(first, second.next))
  }
}

/*
 * The iterative version
 */
function sortedMergeV2(first, second) {
  const result = new Node()
  let [pr, p1, p2] = [result, first, second]

  while (p1 || p2) {
    // if either list is null, append the other one to the result list
    if (!p1 || !p2) {
      pr.next = (p1 || p2)
      break
    }

    if (p1.data <= p2.data) {
      pr = pr.next = new Node(p1.data)
      p1 = p1.next
    } else {
      // switch 2 lists to make sure it's always p1 <= p2
      [p1, p2] = [p2, p1]
    }
  }

  return result.next
}

module.exports = {
  sortedMerge,
  sortedMergeV2,
}

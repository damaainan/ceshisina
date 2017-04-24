/*
https://www.codewars.com/kata/linked-lists-sorted-intersect

Linked Lists - Sorted Intersect

Write a SortedIntersect() function which creates and returns a list representing the intersection
of two lists that are sorted in increasing order. Ideally, each list should only be traversed once.
The resulting list should not contain duplicates.

    var first = 1 -> 2 -> 2 -> 3 -> 3 -> 6 -> null
    var second = 1 -> 3 -> 4 -> 5 -> 6 -> null
    sortedIntersect(first, second) === 1 -> 3 -> 6 -> null
*/

const { Node } = require('./00-utils')

/*
 * Recursive version
 */
function sortedIntersect(first, second) {
  if (!first || !second) return null

  if (first.data === second.data) {
    return new Node(first.data, sortedIntersect(nextDifferent(first), nextDifferent(second)))
  } else if (first.data < second.data) {
    return sortedIntersect(first.next, second)
  } else {
    return sortedIntersect(first, second.next)
  }
}

/*
 * Iterative version
 */
function sortedIntersectV2(first, second) {
  const result = new Node()
  let [pr, p1, p2] = [result, first, second]

  while (p1 || p2) {
    if (!p1 || !p2) break

    if (p1.data === p2.data) {
      pr = pr.next = new Node(p1.data)
      p1 = nextDifferent(p1)
      p2 = nextDifferent(p2)
    } else if (p1.data < p2.data) {
      p1 = p1.next
    } else {
      p2 = p2.next
    }
  }

  return result.next
}

function nextDifferent(node) {
  let nextNode = node.next
  while (nextNode && nextNode.data === node.data) nextNode = nextNode.next
  return nextNode
}

module.exports = {
  sortedIntersect,
  sortedIntersectV2,
}

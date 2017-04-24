/*
https://www.codewars.com/kata/linked-lists-remove-duplicates/javascript

Linked Lists - Remove Duplicates

Write a RemoveDuplicates() function which takes a list sorted in increasing order and deletes any
duplicate nodes from the list. Ideally, the list should only be traversed once. The head of the
resulting list should be returned.

    var list = 1 -> 2 -> 3 -> 3 -> 4 -> 4 -> 5 -> null
    removeDuplicates(list) === 1 -> 2 -> 3 -> 4 -> 5 -> null

If the passed in list is null/None/nil, simply return null.

Note: Your solution is expected to work on long lists. Recursive solutions may fail due to stack size limitations.

The push() and buildOneTwoThree() functions need not be redefined.
*/

/*
 * The recursion version
 * It can trigger a tail call optimization but I don't know why.
 */
function removeDuplicates(head) {
  if (!head) return null

  const nextNode = head.next
  if (nextNode && head.data === nextNode.data) {
    head.next = nextNode.next
    return removeDuplicates(head)
  }

  head.next = removeDuplicates(nextNode)
  return head
}

/*
 * The recursion version with tail call optimization
 */
function removeDuplicatesV2(head, prev = null, re = null) {
  if (!head) return re

  re = re || head
  if (prev && prev.data === head.data) {
    prev.next = head.next
  } else {
    prev = head
  }

  return removeDuplicatesV2(head.next, prev, re)
}

/*
 * The loop version
 */
function removeDuplicatesV3(head) {
  for (let node = head; node; node = node.next) {
    while (node.next && node.data === node.next.data) node.next = node.next.next
  }
  return head
}

module.exports = {
  removeDuplicates,
  removeDuplicatesV2,
  removeDuplicatesV3,
}

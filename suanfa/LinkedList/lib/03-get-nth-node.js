/*
https://www.codewars.com/kata/linked-lists-get-nth-node

Linked Lists - Get Nth

Implement a GetNth() function that takes a linked list and an integer index and returns the node
stored at the Nth index position. GetNth() uses the C numbering convention that the first node is
index 0, the second is index 1, ... and so on. So for the list 42 -> 13 -> 666, GetNth() with index
1 should return Node(13);

    getNth(1 -> 2 -> 3 -> null, 0).data === 1
    getNth(1 -> 2 -> 3 -> null, 1).data === 2

The index should be in the range [0..length-1]. If it is not, GetNth() should throw/raise an
exception (InvalidArgumentException in PHP). You should also raise an exception
(InvalidArgumentException in PHP) if the list is empty/null/None.

The push() and buildOneTwoThree() (build_one_two_three() in PHP) functions do not need to be
redefined.
*/

/*
 * The recursion version
 */
function getNth(head, idx) {
  if (!head || idx < 0) throw 'invalid argument'
  if (idx === 0) return head
  return getNth(head.next, idx - 1)
}

/*
 * The loop version
 */
function getNthV2(head, idx) {
  for (let node = head; node && idx >= 0; node = node.next, idx--) {
    if (idx === 0) return node
  }
  throw 'invalid argument'
}

module.exports = {
  getNth,
  getNthV2,
}

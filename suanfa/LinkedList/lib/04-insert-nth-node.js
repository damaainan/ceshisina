/*
https://www.codewars.com/kata/linked-lists-insert-nth-node/javascript

Linked Lists - Insert Nth

Implement an InsertNth() function (insert_nth() in PHP) which can insert a new node at any index
within a list.

InsertNth() is a more general version of the Push() function that we implemented in the first kata
listed below. Given a list, an index 'n' in the range 0..length, and a data element, add a new node
to the list so that it has the given index. InsertNth() should return the head of the list.

    insertNth(1 -> 2 -> 3 -> null, 0, 7) === 7 -> 1 -> 2 -> 3 -> null)
    insertNth(1 -> 2 -> 3 -> null, 1, 7) === 1 -> 7 -> 2 -> 3 -> null)
    insertNth(1 -> 2 -> 3 -> null, 3, 7) === 1 -> 2 -> 3 -> 7 -> null)

You must throw/raise an exception (InvalidArgumentException in PHP) if the index is too large.

The push() and buildOneTwoThree() (build_one_two_three() in PHP) functions do not need to be
redefined. The Node class is also preloaded for you in PHP.
*/

const { push } = require('../lib/01-push-and-build-one-two-three')

/*
 * The recursion version
 */
function insertNth(head, index, data) {
  if (index === 0) return push(head, data)
  if (!head) throw 'invalid argument'
  head.next = insertNth(head.next, index - 1, data)
  return head
}

/*
 * The loop version
 */
function insertNthV2(head, index, data) {
  if (index === 0) return push(head, data)

  for (let node = head, idx = 0; node; node = node.next, idx++) {
    if (idx + 1 === index) {
      node.next = push(node.next, data)
      return head
    }
  }

  throw 'invalid argument'
}

/*
 * The loop version - use dummy node at head
 */
function insertNthV3(head, index, data) {
  const dummy = push(head, null)

  for (let node = dummy, idx = 0; node; node = node.next, idx++) {
    if (idx === index) {
      node.next = push(node.next, data)
      return dummy.next
    }
  }

  throw 'invalid argument'
}

module.exports = {
  insertNth,
  insertNthV2,
  insertNthV3,
}

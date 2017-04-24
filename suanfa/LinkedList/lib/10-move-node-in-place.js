/*
https://www.codewars.com/kata/linked-lists-move-node-in-place/javascript

Linked Lists - Move Node In-place

Write a MoveNode() function which takes the node from the front of the source list and moves it to
the front of the destintation list. This problem should be done after Linked Lists - Move Node.

    var source = 1 -> 2 -> 3 -> null
    var dest = 4 -> 5 -> 6 -> null
    moveNode(source, dest)
    source === 2 -> 3 -> null
    dest === 1 -> 4 -> 5 -> 6 -> null

You should throw an error if any of the following conditions exist:

- The source argument is null.
- The dest argument is null.
- The source argument is an "empty" node with a null data attribute.

Unlike the Linked Lists - Move Node kata, we are not returning a Context object but rather we are
changing two objects in-place. We are also introducing the concept of an emtpy Node object whose
data attribute is set to null. Passing in an empty node rather than null for the dest argument to
indicate an empty destination list enables members/attributes of dest to be mutated within the
function with the side effect of changing the destination list outside of the function.

The push() and buildOneTwoThree() functions need not be redefined.
*/

const { Node } = require('./00-utils')
const { push } = require('./01-push-and-build-one-two-three')

/*
 * The best practise
 */
function moveNode(source, dest) {
  if (!source || !dest || source.data === null) throw new Error("invalid arguments")

  const data = source.data

  if (source.next) {
    source.data = source.next.data
    source.next = source.next.next
  } else {
    source.data = null
  }

  if (dest.data === null) {
    dest.data = data
  } else {
    dest.next = new Node(dest.data, dest.next)
    dest.data = data
  }
}

/*
 * My first idea, the only difference is that the pushInPlace uses recursion.
 */
function moveNodeV2(source, dest) {
  if (source === null || dest === null || source.isEmpty()) {
    throw new Error('invalid arguments')
  }

  pushInPlace(dest, source.data)

  if (source.next) {
    source.data = source.next.data
    source.next = source.next.next
  } else {
    source.data = null
  }
}

/*
 * Push the data to the front of the list in-place
 *
 * It solves the problem by simply insert a new node between the 1st node and the rest.
 */
function pushInPlace(head, data) {
  const origData = head.data
  head.data = data
  if (head.next) head.next = push(head.next, origData)
}

/*
 * Push the data to the front of the list in-place
 *
 * This is the first idea came to my mind. It passes the data to the 1st node, then passes the 1st
 * node's data to the 2nd node, ang go on.
 *
 * This is a recursion version. It can also be implemented with loop version. But I'll leave it
 * since neither of them are the best way.
 */
function pushInPlaceV2(head, data) {
  if (!head) return new Node(data)

  if (!head.isEmpty()) head.next = pushInPlaceV2(head.next, head.data)
  head.data = data
  return head
}

module.exports = {
  moveNode,
  moveNodeV2,
  pushInPlace,
  pushInPlaceV2,
}

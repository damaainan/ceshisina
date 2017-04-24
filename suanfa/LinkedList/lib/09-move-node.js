/*
https://www.codewars.com/kata/linked-lists-move-node/javascript

Linked Lists - Move Node

Write a MoveNode() function which takes the node from the front of the source list and moves it to
the front of the destintation list. You should throw an error when the source list is empty. For
simplicity, we use a Context object to store and return the state of the two linked lists. A Context
object containing the two mutated lists should be returned by moveNode.

MoveNode() is a handy utility function to have for later problems

    var source = 1 -> 2 -> 3 -> null
    var dest = 4 -> 5 -> 6 -> null
    moveNode(source, dest).source === 2 -> 3 -> null
    moveNode(source, dest).dest === 1 -> 4 -> 5 -> 6 -> null

The push() and buildOneTwoThree() functions need not be redefined.

There is another kata called "Linked Lists - Move Node In-place" that is related but more difficult.
*/

const { push } = require('./01-push-and-build-one-two-three')

function Context(source, dest) {
  this.source = source
  this.dest = dest
}

function moveNode(source, dest) {
  if (!source) throw new Error('source is empty')
  return new Context(source.next, push(dest, source.data))
}

module.exports = {
  Context,
  moveNode,
}

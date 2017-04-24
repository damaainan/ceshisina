/*
https://www.codewars.com/kata/linked-lists-iterative-reverse

Linked Lists - Iterative Reverse

Write an iterative Reverse() function that reverses a linked list. Ideally, Reverse() should only
need to make one pass of the list.

    var list = 2 -> 1 -> 3 -> 6 -> 5 -> null
    reverse(list)
    list === 5 -> 6 -> 3 -> 1 -> 2 -> null

The push() and buildOneTwoThree() functions need not be redefined.
*/

const { Node } = require('./00-utils')

function reverse(list) {
  if (!list) return null

  let result
  for (let node = list; node; node = node.next) {
    result = new Node(node.data, result)
  }

  list.data = result.data
  list.next = result.next
}

module.exports = {
  reverse,
}

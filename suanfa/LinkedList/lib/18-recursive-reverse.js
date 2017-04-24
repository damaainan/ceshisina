/*
https://www.codewars.com/kata/linked-lists-recursive-reverse

Linked Lists - Recursive Reverse

Write a Recursive Reverse() function that recursively reverses a linked list. You may want to use a
nested function for the recursive calls.

    var list = 2 -> 1 -> 3 -> 6 -> 5 -> null
    reverse(list) === 5 -> 6 -> 3 -> 1 -> 2 -> null
*/

const { Node } = require('./00-utils')

function reverse(head, acc = null) {
  return head ? reverse(head.next, new Node(head.data, acc)) : acc
}

module.exports = {
  reverse,
}

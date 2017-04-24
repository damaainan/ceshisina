/*
https://www.codewars.com/kata/linked-lists-length-and-count/javascript

Linked Lists - Length & Count

Implement Length() to count the number of nodes in a linked list.

    length(null) === 0
    length(1 -> 2 -> 3 -> null) === 3

Implement Count() to count the occurrences of an integer in a linked list.

    count(null, 1) === 0
    count(1 -> 2 -> 3 -> null, 1) === 1
    count(1 -> 1 -> 1 -> 2 -> 2 -> 2 -> 2 -> 3 -> 3 -> null, 2) === 4

I've decided to bundle these two functions within the same Kata since they are both very similar.

The push() and buildOneTwoThree() functions do not need to be redefined.
*/

/*
 * The recursion version
 */
function length(head) {
  return head ? 1 + length(head.next) : 0
}

/*
 * The loop version, use "while"
 */
function lengthV2(head) {
  let len = 0
  let node = head

  while (node) {
    len++
    node = node.next
  }

  return len
}

/*
 * The loop version, use "for" to reduce some lines
 */
function lengthV3(head) {
  for (var len = 0, node = head; node; node = node.next) len++
  return len
}

/*
 * The recursion version
 */
function count(head, data) {
  if (!head) return 0
  return (head.data === data ? 1 : 0) + count(head.next, data)
}

/*
 * The loop version
 */
function countV2(head, data) {
  for (var n = 0, node = head; node; node = node.next) {
    if (node.data === data) n++
  }
  return n
}

module.exports = {
  length,
  lengthV2,
  lengthV3,
  count,
  countV2,
}

/*
http://www.codewars.com/kata/linked-lists-alternating-split/javascript

Linked Lists - Alternating Split

Write an AlternatingSplit() function that takes one list and divides up its nodes to make two
smaller lists. The sublists should be made from alternating elements in the original list. So if the
original list is a -> b -> a -> b -> a -> null then one sublist should be a -> a -> a -> null and
the other should be b -> b -> null.

    var list = 1 -> 2 -> 3 -> 4 -> 5 -> null
    alternatingSplit(list).first === 1 -> 3 -> 5 -> null
    alternatingSplit(list).second === 2 -> 4 -> null

For simplicity, we use a Context object to store and return the state of the two linked lists. A
Context object containing the two mutated lists should be returned by AlternatingSplit().

If the passed in head node is null/None/nil or a single node, throw an error.
*/

const { Node } = require('./00-utils')

function Context(first, second) {
  this.first = first
  this.second = second
}

/*
 * The recursion version
 */
function alternatingSplit(head) {
  if (!head || !head.next) throw new Error('invalid arguments')
  return new Context(split(head), split(head.next))
}

function split(head) {
  const list = new Node(head.data)
  if (head.next && head.next.next) list.next = split(head.next.next)
  return list
}

/*
 * The recursion version 2
 */
function alternatingSplitV2(head) {
  if (!head || !head.next) throw new Error('invalid arguments')
  return new Context(...splitV2(head))
}

function splitV2(head) {
  if (!head) return [null, null]

  const first = new Node(head.data)
  const [second, firstNext] = splitV2(head.next)
  first.next = firstNext
  return [first, second]
}


/*
 * The loop version
 */
function alternatingSplitV3(head) {
  if (!head || !head.next) throw new Error('invalid arguments')

  const sublists = []
  const tails = []

  for (let node = head, idx = 0; node; node = node.next, idx = idx ? 0 : 1) {
    const newNode = new Node(node.data)

    if (sublists[idx]) {
      tails[idx].next = newNode
      tails[idx] = newNode
    } else {
      sublists[idx] = newNode
      tails[idx] = newNode
    }
  }

  return new Context(...sublists)
}

/*
 * The loop version, use dump node
 */
function alternatingSplitV4(head) {
  if (!head || !head.next) throw new Error('invalid arguments')

  const first = new Node()
  const second = new Node()
  const tails = [first, second]

  for (let node = head, idx = 0; node; node = node.next, idx = idx ? 0 : 1) {
    tails[idx].next = new Node(node.data)
    tails[idx] = tails[idx].next
  }

  return new Context(first.next, second.next)
}

module.exports = {
  alternatingSplit,
  alternatingSplitV2,
  alternatingSplitV3,
  alternatingSplitV4,
}

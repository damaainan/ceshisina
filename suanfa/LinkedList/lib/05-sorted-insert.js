/*
https://www.codewars.com/kata/linked-lists-sorted-insert/javascript

Linked Lists - Sorted Insert

Write a SortedInsert() function which inserts a node into the correct location of a pre-sorted
linked list which is sorted in ascending order. SortedInsert takes the head of a linked list and
data used to create a node as arguments. SortedInsert() should also return the head of the list.

    sortedInsert(1 -> 2 -> 3 -> null, 4) === 1 -> 2 -> 3 -> 4 -> null)
    sortedInsert(1 -> 7 -> 8 -> null, 5) === 1 -> 5 -> 7 -> 8 -> null)
    sortedInsert(3 -> 5 -> 9 -> null, 7) === 3 -> 5 -> 7 -> 9 -> null)
*/

const { Node } = require('./00-utils')

/*
 * The recursion version
 */
function sortedInsert(head, data) {
  if (!head || data <= head.data) return new Node(data, head)

  head.next = sortedInsert(head.next, data)
  return head
}

/*
 * The loop version
 */
function sortedInsertV2(head, data) {
  let node = head
  let prevNode

  while (true) {
    if (!node || data <= node.data) {
      let newNode = new Node(data, node)
      if (prevNode) {
        prevNode.next = newNode
        return head
      } else {
        return newNode
      }
    }

    prevNode = node
    node = node.next
  }
}

/*
 * The loop version, use dummy node
 */
function sortedInsertV3(head, data) {
  const dummy = new Node(null, head)
  let prevNode = dummy
  let node = dummy.next

  while (true) {
    if (!node || node.data > data) {
      prevNode.next = new Node(data, node)
      return dummy.next
    }

    prevNode = node
    node = node.next
  }
}

/*
 * The loop version, use dummy node and check next node in each loop
 */
function sortedInsertV4(head, data) {
  const dummy = new Node(null, head)

  for (let node = dummy; node; node = node.next) {
    const nextNode = node.next
    if (!nextNode || nextNode.data >= data) {
      node.next = new Node(data, nextNode)
      return dummy.next
    }
  }
}

module.exports = {
  sortedInsert,
  sortedInsertV2,
  sortedInsertV3,
  sortedInsertV4,
}

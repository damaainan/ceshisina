/*
https://www.codewars.com/kata/linked-lists-front-back-split/javascript

Linked Lists - Front Back Split

Write a FrontBackSplit() function that takes one list and splits it into two sublists — one for the
front half, and one for the back half. If the number of elements is odd, the extra element should
go in the front list. For example, FrontBackSplit() on the list 2 -> 3 -> 5 -> 7 -> 11 -> null
should yield the two lists 2 -> 3 -> 5 -> null and 7 -> 11 -> null. Getting this right for all the
cases is harder than it looks. You will probably need special case code to deal with lists of
length < 2 cases.

    var source = 1 -> 3 -> 7 -> 8 -> 11 -> 12 -> 14 -> null
    var front = new Node()
    var back = new Node()
    frontBackSplit(source, front, back)
    front === 1 -> 3 -> 7 -> 8 -> null
    back === 11 -> 12 -> 14 -> null

You should throw an error if any of the arguments to FrontBackSplit are null or if the source list
has < 2 nodes.

Hint. Probably the simplest strategy is to compute the length of the list, then use a for loop to
hop over the right number of nodes to find the last node of the front half, and then cut the list at
that point. There is a trick technique that uses two pointers to traverse the list. A "slow" pointer
advances one nodes at a time, while the "fast" pointer goes two nodes at a time. When the fast
pointer reaches the end, the slow pointer will be about half way. For either strategy, care is
required to split the list at the right point.
*/

const { Node } = require('./00-utils')

/*
 * The simplest way, transform list to array, split, then append data to front and back.
 */
function frontBackSplit(source, front, back) {
  if (!front || !back || !source || !source.next) throw new Error('invalid arguments')

  const array = []
  for (let node = source; node; node = node.next) array.push(node.data)

  const splitIdx = Math.round(array.length / 2)
  const frontData = array.slice(0, splitIdx)
  const backData = array.slice(splitIdx)

  appendData(front, frontData)
  appendData(back, backData)
}

function appendData(list, array) {
  let node = list
  for (const data of array) {
    if (node.data !== null) {
      node.next = new Node(data)
      node = node.next
    } else {
      node.data = data
    }
  }
}

/*
 * The simplest way, without intermediate data (array)
 */
function frontBackSplitV2(source, front, back) {
  if (!front || !back || !source || !source.next) throw new Error('invalid arguments')

  let len = 0
  for (let node = source; node; node = node.next) len++
  const backIdx = Math.round(len / 2)

  for (let node = source, idx = 0; node; node = node.next, idx++) {
    append(idx < backIdx ? front : back, node.data)
  }
}

// Note that it uses the "tail" property to track the tail of the list.
function append(list, data) {
  if (list.data === null) {
    list.data = data
    list.tail = list
  } else {
    list.tail.next = new Node(data)
    list.tail = list.tail.next
  }
}

/*
 * The slow & fast pointer way
 */
function frontBackSplitV3(source, front, back) {
  if (!front || !back || !source || !source.next) throw new Error('invalid arguments')

  let slow = source
  let fast = source

  while (fast) {
    // use append to copy nodes to "front" list because we don't want to mutate the source list.
    append(front, slow.data)
    slow = slow.next
    fast = fast.next && fast.next.next
  }

  // "back" list just need to copy one node and point to the rest.
  back.data = slow.data
  back.next = slow.next
}

module.exports = {
  frontBackSplit,
  frontBackSplitV2,
  frontBackSplitV3,
}

/*
https://www.codewars.com/kata/linked-lists-append/javascript

Linked Lists - Append

Write an Append() function which appends one linked list to another. The head of the resulting list
should be returned.

    var listA = 1 -> 2 -> 3 -> null
    var listB = 4 -> 5 -> 6 -> null
    append(listA, listB) === 1 -> 2 -> 3 -> 4 -> 5 -> 6 -> null

If both listA and listB are null/None/nil, return null/None/nil. If one list is null/None/nil and
the other is not, simply return the non-null/None/nil list.

The push() and buildOneTwoThree() functions need not be redefined.
*/

function append(listA, listB) {
  if (!listA) return listB
  if (!listB) return listA

  listA.next = append(listA.next, listB)
  return listA
}

function appendV2(listA, listB) {
  if (!listA) return listB
  if (!listB) return listA

  let node = listA
  while (node.next) node = node.next

  node.next = listB
  return listA
}

module.exports = {
  append,
  appendV2,
}

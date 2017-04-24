const expect = require('expect')

expect.extend({
  toEqualLinkedList(list) {
    const idx = _compareNodes(this.actual, list, 0)
    expect.assert(
      idx === null,
      `linked list not match at index ${idx}`
    )
  }
})

function _compareNodes(nodeA, nodeB, idx) {
  if (!nodeA && !nodeB) return null
  if (!nodeA || !nodeB || nodeA.data !== nodeB.data) return idx
  return _compareNodes(nodeA.next, nodeB.next, idx + 1)
}

global.expect = expect

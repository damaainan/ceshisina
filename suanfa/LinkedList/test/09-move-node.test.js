const { Node } = require('../lib/00-utils')
const { buildList } = require('../lib/01-push-and-build-one-two-three')
const { moveNode } = require('../lib/09-move-node')

describe('09 Move Node', () => {
  it('should be able to handle two empty lists.', () => {
    expect(() => moveNode(null, null)).toThrow('source is empty')
  })

  it('should be able to handle one empty list.', () => {
    expect(() => moveNode(null, new Node(23))).toThrow('source is empty')

    const context = moveNode(buildList([1, 2, 3]), null)
    expect(context.source).toEqualLinkedList(buildList([2, 3]))
    expect(context.dest).toEqualLinkedList(buildList([1]))
  })

  it('should be able to handle two non-empty lists.', () => {
    const context = moveNode(buildList([1, 2, 3]), buildList([1, 2, 3]))
    expect(context.source).toEqualLinkedList(buildList([2, 3]))
    expect(context.dest).toEqualLinkedList(buildList([1, 1, 2, 3]))
  })
})

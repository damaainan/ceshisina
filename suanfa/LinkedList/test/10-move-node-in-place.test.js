const { Node } = require('../lib/00-utils')
const { buildList } = require('../lib/01-push-and-build-one-two-three')
const { moveNode, moveNodeV2, pushInPlace, pushInPlaceV2 } = require('../lib/10-move-node-in-place')

describe('10 Move Node In-place', () => {
  createMoveNodeTests(moveNode)
  createMoveNodeTests(moveNodeV2)

  createPushInPlaceTests(pushInPlace)
  createPushInPlaceTests(pushInPlaceV2)

  function createMoveNodeTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle two null lists.', () => {
        expect(() => fn(null, null)).toThrow('invalid arguments')
      })

      it('should be able to handle two empty lists.', () => {
        expect(() => fn(new Node(), new Node())).toThrow('invalid arguments')
      })

      it('should be able to handle one null list.', () => {
        expect(() => fn(null, new Node(23))).toThrow('invalid arguments')
        expect(() => fn(new Node(23), null)).toThrow('invalid arguments')
      })

      it('should be able to handle one empty list.', () => {
        expect(() => fn(new Node(), new Node(23))).toThrow('invalid arguments')

        const source = buildList([1, 2, 3])
        const dest = new Node()
        fn(source, dest)
        expect(source).toEqualLinkedList(buildList([2, 3]))
        expect(dest).toEqualLinkedList(buildList([1]))
      })

      it('should be able to handle two small non-null & non-empty lists.', () => {
        const source = buildList([1, 2, 3])
        const dest = buildList([1, 2, 3])

        fn(source, dest)
        expect(source).toEqualLinkedList(buildList([2, 3]))
        expect(dest).toEqualLinkedList(buildList([1, 1, 2, 3]))

        fn(source, dest)
        expect(source).toEqualLinkedList(buildList([3]))
        expect(dest).toEqualLinkedList(buildList([2, 1, 1, 2, 3]))

        fn(source, dest)
        expect(source).toEqualLinkedList(new Node())
        expect(dest).toEqualLinkedList(buildList([3, 2, 1, 1, 2, 3]))
      })
    })
  }

  function createPushInPlaceTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle empty list.', () => {
        const list = new Node()
        fn(list, 1)
        expect(list).toEqualLinkedList(new Node(1))
      })

      it('should be able to handle non-empty list.', () => {
        const list = buildList([1, 2, 3])
        fn(list, 1)
        expect(list).toEqualLinkedList(buildList([1, 1, 2, 3]))
      })
    })
  }
})

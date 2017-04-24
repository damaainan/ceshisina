const { Node } = require('../lib/00-utils')
const { buildList } = require('../lib/01-push-and-build-one-two-three')
const {
  alternatingSplit,
  alternatingSplitV2,
  alternatingSplitV3,
  alternatingSplitV4,
} = require('../lib/11-alternating-split')

describe('11 Alternating Split', () => {
  createTests(alternatingSplit)
  createTests(alternatingSplitV2)
  createTests(alternatingSplitV3)
  createTests(alternatingSplitV4)

  function createTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle an empty list.', () => {
        expect(() => fn(null)).toThrow('invalid arguments')
      })

      it('should be able to handle a list of length 1.', () => {
        expect(() => fn(new Node(1))).toThrow('invalid arguments')
      })

      it('should be able to handle a list of length 2.', () => {
        const ctx = fn(buildList([1, 2]))
        expect(ctx.first).toEqualLinkedList(buildList([1]))
        expect(ctx.second).toEqualLinkedList(buildList([2]))
      })

      it('should be able to handle a list of length 6.', () => {
        const ctx = fn(buildList([1, 2, 3, 4, 5, 6]))
        expect(ctx.first).toEqualLinkedList(buildList([1, 3, 5]))
        expect(ctx.second).toEqualLinkedList(buildList([2, 4, 6]))
      })
    })
  }
})

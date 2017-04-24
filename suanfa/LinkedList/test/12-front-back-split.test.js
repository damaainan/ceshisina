const { Node } = require('../lib/00-utils')
const { buildList } = require('../lib/01-push-and-build-one-two-three')
const {
  frontBackSplit,
  frontBackSplitV2,
  frontBackSplitV3,
} = require('../lib/12-front-back-split')

describe('12 Front Back Split', () => {
  createTests(frontBackSplit)
  createTests(frontBackSplitV2)
  createTests(frontBackSplitV3)

  function createTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle all null arguments.', () => {
        expect(() => fn(null, null, null)).toThrow('invalid arguments')
      })

      it('should be able to handle cases when one argument is null.', () => {
        expect(() => fn(null, new Node(), new Node())).toThrow('invalid arguments')
        expect(() => fn(new Node(), null, new Node())).toThrow('invalid arguments')
        expect(() => fn(new Node(), new Node(), null)).toThrow('invalid arguments')
      })

      it('should be able to handle a list of length 0 or 1.', () => {
        expect(() => fn(new Node(), new Node(), new Node())).toThrow('invalid arguments')
        expect(() => fn(new Node(23), new Node(), new Node())).toThrow('invalid arguments')
      })

      it('should be able to handle a list of length 3.', () => {
        const front = new Node()
        const back = new Node()
        fn(buildList([1, 2, 3]), front, back)
        expect(front).toEqualLinkedList(buildList([1, 2]))
        expect(back).toEqualLinkedList(new Node(3))
      })

      it('should be able to handle a list of length 6.', () => {
        const front = new Node()
        const back = new Node()
        fn(buildList([1, 2, 3, 4, 5, 6]), front, back)
        expect(front).toEqualLinkedList(buildList([1, 2, 3]))
        expect(back).toEqualLinkedList(buildList([4, 5, 6]))
      })

      it('should be able to handle a list of length 11.', () => {
        const front = new Node()
        const back = new Node()
        fn(buildList([3, 4, 6, 1, 2, 4, 2, 0, 3, 2, 6]), front, back)
        expect(front).toEqualLinkedList(buildList([3, 4, 6, 1, 2, 4]))
        expect(back).toEqualLinkedList(buildList([2, 0, 3, 2, 6]))
      })
    })
  }
})

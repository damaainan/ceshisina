const { Node } = require('../lib/00-utils')
const { buildList } = require('../lib/01-push-and-build-one-two-three')
const { insertSort, insertSortV2 } = require('../lib/06-insert-sort')

describe('06 Insert Sort', () => {
  createTests(insertSort)
  createTests(insertSortV2)

  function createTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle an empty list.', () => {
        const list = fn(null)
        expect(list).toEqual(null)
      })

      it('should be able to handle a list of length 1.', () => {
        const list = fn(new Node(5))
        expect(list.data).toEqual(5)
      })

      it('should be able to handle a pre-sorted list of length 2.', () => {
        const list = fn(buildList([1, 2]))
        expect(list).toEqualLinkedList(buildList([1, 2]))
      })

      it('should be able to handle a reverse sorted list of length 2.', () => {
        const list = fn(buildList([2, 1]))
        expect(list).toEqualLinkedList(buildList([1, 2]))
      })

      it('should be able to handle a pre-sorted list of length 3.', () => {
        const list = fn(buildList([1, 2, 3]))
        expect(list).toEqualLinkedList(buildList([1, 2, 3]))
      })

      it('should be able to handle a reverse sorted list of length 3.', () => {
        const list = fn(buildList([3, 2, 1]))
        expect(list).toEqualLinkedList(buildList([1, 2, 3]))
      })

      it('should be able to handle an unordered list of length > 3.', () => {
        const list = fn(buildList([4, 8, 1, 3, 2, 9, 6, 5, 9, 2]))
        expect(list).toEqualLinkedList(buildList([1, 2, 2, 3, 4, 5, 6, 8, 9, 9]))
      })
    })
  }
})

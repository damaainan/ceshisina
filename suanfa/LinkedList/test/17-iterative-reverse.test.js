const { Node } = require('../lib/00-utils')
const { buildList } = require('../lib/01-push-and-build-one-two-three')
const { buildRandomArray } = require('./support/helpers')
const { reverse } = require('../lib/17-iterative-reverse')

describe('17 Iterative Reverse', () => {
  createTests(reverse)

  function createTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle a null list.', () => {
        const list = null
        reverse(null)
        expect(list).toEqual(null)
      })

      it('should be able to handle a list of length 1', () => {
        const list = buildList([1])
        reverse(list)
        expect(list).toEqualLinkedList(buildList([1]))
      })

      it('should be able to handle lists of length 2', () => {
        const list1 = buildList([1, 3])
        reverse(list1)
        expect(list1).toEqualLinkedList(buildList([3, 1]))

        const list2 = buildList([3, 1])
        reverse(list2)
        expect(list2).toEqualLinkedList(buildList([1, 3]))
      })

      it('should be able to handle lists of length 3', () => {
        const list = buildList([1, 3, 8])
        reverse(list)
        expect(list).toEqualLinkedList(buildList([8, 3, 1]))

        const list2 = buildList([8, 3, 1])
        reverse(list2)
        expect(list2).toEqualLinkedList(buildList([1, 3, 8]))

        const list3 = buildList([1, 8, 3])
        reverse(list3)
        expect(list3).toEqualLinkedList(buildList([3, 8, 1]))

        const list4 = buildList([3, 8, 1])
        reverse(list4)
        expect(list4).toEqualLinkedList(buildList([1, 8, 3]))
      })

      it('should be able to handle a list of length 8', () => {
        const list1 = buildList([1, 3, 5, 7, 9, 11, 13, 15])
        reverse(list1)
        expect(list1).toEqualLinkedList(buildList([15, 13, 11, 9, 7, 5, 3, 1]))

        const list2 = buildList([15, 13, 11, 9, 7, 5, 3, 1])
        reverse(list2)
        expect(list2).toEqualLinkedList(buildList([1, 3, 5, 7, 9, 11, 13, 15]))

        const list3 = buildList([9, 1, 7, 3, 5, 15, 13, 11])
        reverse(list3)
        expect(list3).toEqualLinkedList(buildList([11, 13, 15, 5, 3, 7, 1, 9]))

        const list4 = buildList([1, 1, 1, 1, 1, 1, 1, 1])
        reverse(list4)
        expect(list4).toEqualLinkedList(buildList([1, 1, 1, 1, 1, 1, 1, 1]))
      })

      it('should be able to handle a very large list.', () => {
        const largeArray = buildRandomArray(1000)
        const list = buildList(largeArray.slice())
        reverse(list)
        const largeReversedArray = largeArray.slice()
        largeReversedArray.reverse()
        expect(list).toEqualLinkedList(buildList(largeReversedArray))
      })
    })
  }
})

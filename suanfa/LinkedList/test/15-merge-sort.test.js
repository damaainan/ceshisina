const { Node } = require('../lib/00-utils')
const { buildList } = require('../lib/01-push-and-build-one-two-three')
const { mergeSort } = require('../lib/15-merge-sort')
const { buildRandomArray } = require('./support/helpers')

describe('15 Merge Sort', () => {
  createTests(mergeSort)

  function createTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle a null list.', () => {
        expect(fn(null)).toEqual(null)
      })

      it('should be able to handle a list of length 1', () => {
        expect(fn(new Node(1))).toEqualLinkedList(new Node(1))
      })

      it('should be able to handle lists of length 2', () => {
        expect(fn(buildList([1, 3]))).toEqualLinkedList(buildList([1, 3]))
        expect(fn(buildList([3, 1]))).toEqualLinkedList(buildList([1, 3]))
      })

      it('should be able to handle lists of length 3', () => {
        expect(fn(buildList([1, 3, 8]))).toEqualLinkedList(buildList([1, 3, 8]))
        expect(fn(buildList([8, 3, 1]))).toEqualLinkedList(buildList([1, 3, 8]))
        expect(fn(buildList([1, 8, 3]))).toEqualLinkedList(buildList([1, 3, 8]))
        expect(fn(buildList([3, 8, 1]))).toEqualLinkedList(buildList([1, 3, 8]))
      })

      it('should be able to handle a list of length 8', () => {
        expect(fn(buildList([1, 3, 5, 7, 9, 11, 13, 15]))).toEqualLinkedList(buildList([1, 3, 5, 7, 9, 11, 13, 15]))
        expect(fn(buildList([15, 13, 11, 9, 7, 5, 3, 1]))).toEqualLinkedList(buildList([1, 3, 5, 7, 9, 11, 13, 15]))
        expect(fn(buildList([9, 1, 7, 3, 5, 15, 13, 11]))).toEqualLinkedList(buildList([1, 3, 5, 7, 9, 11, 13, 15]))
        expect(fn(buildList([1, 1, 1, 1, 1, 1, 1, 1]))).toEqualLinkedList(buildList([1, 1, 1, 1, 1, 1, 1, 1]))
      })

      it('should be able to handle a very large list.', () => {
        const largeArray = buildRandomArray(1000)
        const largeSortedArray = largeArray.slice().sort((a, b) => a - b)
        expect(fn(buildList(largeArray))).toEqualLinkedList(buildList(largeSortedArray))
      })
    })
  }
})

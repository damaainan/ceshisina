const { Node } = require('../lib/00-utils')
const { buildList } = require('../lib/01-push-and-build-one-two-three')
const {
  sortedMerge,
  sortedMergeV2,
} = require('../lib/14-sorted-merge')

describe('14 Sorted Merge', () => {
  createTests(sortedMerge)
  createTests(sortedMergeV2)

  function createTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle two null arguments.', () => {
        expect(fn(null, null)).toEqual(null)
      })

      it('should be able to handle cases where one argument is null.', () => {
        expect(fn(new Node(1), null)).toEqualLinkedList(new Node(1))
        expect(fn(null, new Node(1))).toEqualLinkedList(new Node(1))
      })

      it('should be able to handle two lists of length 1', () => {
        expect(fn(new Node(23), new Node(44))).toEqualLinkedList(buildList([23, 44]))
        expect(fn(new Node(44), new Node(23))).toEqualLinkedList(buildList([23, 44]))
      })

      it('should be able to handle lists of length 2', () => {
        expect(fn(buildList([1, 3]), buildList([2, 4]))).toEqualLinkedList(buildList([1, 2, 3, 4]))
        expect(fn(buildList([3]), buildList([2, 4]))).toEqualLinkedList(buildList([2, 3, 4]))
        expect(fn(buildList([3, 4, 5]), buildList([2, 9]))).toEqualLinkedList(buildList([2, 3, 4, 5, 9]))
        expect(fn(buildList([4, 5]), buildList([1]))).toEqualLinkedList(buildList([1, 4, 5]))
      })

      it('should be able to handle lists of length 3', () => {
        expect(fn(buildList([1, 3, 8]), null)).toEqualLinkedList(buildList([1, 3, 8]))
        expect(fn(buildList([1, 3, 8]), buildList([2, 4, 9]))).toEqualLinkedList(buildList([1, 2, 3, 4, 8, 9]))
        expect(fn(buildList([2, 3, 5, 8]), buildList([2, 4, 6]))).toEqualLinkedList(buildList([2, 2, 3, 4, 5, 6, 8]))
        expect(fn(buildList([3, 4, 5]), buildList([2]))).toEqualLinkedList(buildList([2, 3, 4, 5]))
        expect(fn(buildList([1, 1, 1]), buildList([1, 1, 1]))).toEqualLinkedList(buildList([1, 1, 1, 1, 1, 1]))
      })

      it('should be able to handle a list of length 8', () => {
        expect(fn(buildList([1, 3, 5, 7, 9, 11, 13, 15]), null)).toEqualLinkedList(buildList([1, 3, 5, 7, 9, 11, 13, 15]))

        expect(fn(buildList([1, 3, 5, 7, 9, 11, 13, 15]), buildList([1, 3, 3, 5, 6])))
          .toEqualLinkedList(buildList([1, 1, 3, 3, 3, 5, 5, 6, 7, 9, 11, 13, 15]))

        expect(fn(buildList([1, 3, 5, 7, 9, 11, 13, 15]), buildList([2, 4, 6, 8, 10, 12, 14, 16])))
          .toEqualLinkedList(buildList([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16]))

        expect(fn(buildList([1, 3, 5, 7, 9, 11, 13, 15]), new Node(88)))
          .toEqualLinkedList(buildList([1, 3, 5, 7, 9, 11, 13, 15, 88]))
      })
    })
  }
})

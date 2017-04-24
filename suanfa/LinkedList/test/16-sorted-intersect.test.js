const { Node } = require('../lib/00-utils')
const { buildList } = require('../lib/01-push-and-build-one-two-three')
const { sortedIntersect, sortedIntersectV2 } = require('../lib/16-sorted-intersect')

describe('16 Sorted Intersect', () => {
  createTests(sortedIntersect)
  createTests(sortedIntersectV2)

  function createTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle two null arguments.', () => {
        expect(fn(null, null)).toEqual(null)
      })

      it('should be able to handle cases where one argument is null.', () => {
        expect(fn(new Node(1), null)).toEqual(null)
        expect(fn(null, new Node(1))).toEqual(null)
      })

      it('should be able to handle two lists of length 1', () => {
        expect(fn(new Node(23), new Node(44))).toEqual(null)
        expect(fn(new Node(44), new Node(23))).toEqual(null)
        expect(fn(new Node(12), new Node(12))).toEqual(new Node(12))
      })

      it('should be able to handle lists of length 2', () => {
        expect(fn(buildList([1, 3]), buildList([2, 4]))).toEqual(null)
        expect(fn(buildList([3]), buildList([2, 4]))).toEqual(null)
        expect(fn(buildList([3, 4, 5]), buildList([2, 9]))).toEqual(null)
        expect(fn(buildList([4, 5]), buildList([1]))).toEqual(null)
        expect(fn(buildList([4, 5]), buildList([4, 5]))).toEqualLinkedList(buildList([4, 5]))
        expect(fn(buildList([4, 5]), buildList([1, 2, 3, 4, 5]))).toEqualLinkedList(buildList([4, 5]))
        expect(fn(buildList([1, 2, 2, 2, 2, 2, 5]), buildList([1, 5])))
          .toEqualLinkedList(buildList([1, 5]))
      })

      it('should be able to handle lists of length 3', () => {
        expect(fn(buildList([1, 3, 8]), buildList([1, 3, 8]))).toEqualLinkedList(buildList([1, 3, 8]))
        expect(fn(buildList([1, 3, 8]), buildList([2, 4, 9]))).toEqualLinkedList(null)
        expect(fn(buildList([2, 3, 5, 8]), buildList([2, 4, 6]))).toEqual(new Node(2))
        expect(fn(buildList([3, 4, 5]), buildList([2]))).toEqual(null)
        expect(fn(buildList([3, 4, 5]), buildList([4, 4, 4, 4, 4]))).toEqual(new Node(4))
        expect(fn(buildList([1, 1, 1]), buildList([1, 1, 1]))).toEqual(new Node(1))
      })

      it('should be able to handle a list of length 8', () => {
        expect(fn(buildList([1, 3, 5, 7, 9, 11, 13, 15]), null)).toEqual(null)

        expect(fn(buildList([1, 3, 5, 7, 9, 11, 13, 15]), buildList([1, 3, 3, 5, 6])))
          .toEqualLinkedList(buildList([1, 3, 5]))

        expect(fn(buildList([1, 3, 5, 7, 9, 11, 13, 15]), buildList([2, 4, 6, 8, 10, 12, 14, 16])))
          .toEqual(null)

        expect(fn(buildList([1, 3, 5, 7, 9, 11, 13, 15]), new Node(88))).toEqual(null)

        expect(fn(buildList([1, 3, 5, 7, 9, 11, 13, 15]), buildList([1, 3, 5, 7, 9, 11, 13, 15])))
          .toEqualLinkedList(buildList([1, 3, 5, 7, 9, 11, 13, 15]))

        expect(fn(buildList([1, 3, 5, 7, 9, 11, 13, 15]), buildList([1, 5, 9, 13])))
          .toEqualLinkedList(buildList([1, 5, 9, 13]))

        expect(fn(
          buildList([1, 1, 3, 3, 5, 5, 7, 7, 9, 9, 11, 11, 13, 13, 13, 13, 15]),
          buildList([1, 3, 5, 7, 9, 11, 13, 15])
        )).toEqualLinkedList(buildList([1, 3, 5, 7, 9, 11, 13, 15]))
      })
    })
  }
})

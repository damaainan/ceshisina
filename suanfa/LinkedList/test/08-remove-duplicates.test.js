const { Node } = require('../lib/00-utils')
const { buildList } = require('../lib/01-push-and-build-one-two-three')
const {
  removeDuplicates,
  removeDuplicatesV2,
  removeDuplicatesV3,
} = require('../lib/08-remove-duplicates')

describe('08 Remove Duplicates', () => {
  createTests(removeDuplicates)
  createTests(removeDuplicatesV2)
  createTests(removeDuplicatesV3)

  createLargeListTests(removeDuplicates, { isOverflow: true })
  createLargeListTests(removeDuplicatesV2, { isOverflow: false })
  createLargeListTests(removeDuplicatesV3, { isOverflow: false })

  function createTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle an empty list.', () => {
        expect(fn(null)).toEqual(null)
      })

      it('should be able to handle a list of length 1.', () => {
        expect(fn(buildList([23])).data).toEqual(23)
      })

      it('should be able to handle a list without duplicates.', () => {
        expect(fn(buildList([1, 2, 3]))).toEqualLinkedList(buildList([1, 2, 3]))
        expect(fn(buildList([4, 5, 6]))).toEqualLinkedList(buildList([4, 5, 6]))
      })

      it('should be able to handle a list with duplicates.', () => {
        expect(fn(buildList([1, 2, 2]))).toEqualLinkedList(buildList([1, 2]))
        expect(fn(buildList([1, 1, 1, 1, 1]))).toEqualLinkedList(buildList([1]))
        expect(fn(buildList([1, 2, 3, 3, 4, 4, 5]))).toEqualLinkedList(buildList([1, 2, 3, 4, 5]))
        expect(fn(buildList([1, 1, 1, 2, 2, 2]))).toEqualLinkedList(buildList([1, 2]))
      })
    })
  }

  function createLargeListTests(fn, { isOverflow }) {
    describe(`${fn.name} - max stack size exceed test`, () => {
      it(`${isOverflow ? 'should NOT' : 'should'} be able to handle a big random list.`, () => {
        Error.stackTraceLimit = 10

        expect(() => {
          fn(buildRandomSortedList(40000))
        })[isOverflow ? 'toThrow' : 'toNotThrow'](RangeError, 'Maximum call stack size exceeded')
      })
    })
  }

  function buildRandomSortedList(len) {
    let list
    let prevNode
    let num = 1

    for (let i = 0; i < len; i++) {
      const node = new Node(randomBool() ? num++ : num)
      if (!list) {
        list = node
      } else {
        prevNode.next = node
      }
      prevNode = node
    }

    return list
  }

  function randomBool() {
    return Math.random() >= 0.5
  }
})

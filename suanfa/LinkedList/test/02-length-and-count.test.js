const { Node } = require('../lib/00-utils')
const { buildList } = require('../lib/01-push-and-build-one-two-three')
const {
  length,
  lengthV2,
  lengthV3,
  count,
  countV2,
} = require('../lib/02-length-and-count')

describe('02 Length & Count', () => {
  createLengthTests(length)
  createLengthTests(lengthV2)
  createLengthTests(lengthV3)

  createCountTests(count)
  createCountTests(countV2)

  function createLengthTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle null list', () => {
        expect(fn(null)).toEqual(0)
      })

      it('should be able to handle single node', () => {
        expect(fn(new Node(1))).toEqual(1)
      })

      it('should be able to handle list of multiple nodes', () => {
        expect(fn(buildList([1, 2, 3]))).toEqual(3)
      })
    })
  }

  function createCountTests(fn) {
    describe(fn.name, () => {
      it('tests for counting occurrences of a particular integer in a linked list', () => {
        const list = buildList([1, 2, 3])
        expect(fn(list, 1)).toEqual(1)
        expect(fn(list, 2)).toEqual(1)
        expect(fn(list, 3)).toEqual(1)
        expect(fn(list, 99)).toEqual(0)
        expect(fn(null, 1)).toEqual(0)
      })

      it('tests for counting multiple occurrences of a particular integer in a linked list.', () => {
        const list = buildList([1, 1, 1, 2, 2, 2, 2, 3, 3])
        expect(fn(list, 1)).toEqual(3)
        expect(fn(list, 2)).toEqual(4)
        expect(fn(list, 3)).toEqual(2)
      })
    })
  }
})

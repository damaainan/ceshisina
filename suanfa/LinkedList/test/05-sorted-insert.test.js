const { buildOneTwoThree } = require('../lib/01-push-and-build-one-two-three')
const {
  sortedInsert,
  sortedInsertV2,
  sortedInsertV3,
  sortedInsertV4,
} = require('../lib/05-sorted-insert')

describe('05 Sorted Insert', () => {
  createTests(sortedInsert)
  createTests(sortedInsertV2)
  createTests(sortedInsertV3)
  createTests(sortedInsertV4)

  function createTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle an empty list.', () => {
        const list = fn(null, 23)
        expect(list.data).toEqual(23)
        expect(list.next).toEqual(null)
      })

      it('should be able to insert a new node at the head of a list.', () => {
        const list = fn(buildOneTwoThree(), 0.5)
        expect(list.data).toEqual(0.5)
        expect(list.next.data).toEqual(1)
        expect(list.next.next.data).toEqual(2)
        expect(list.next.next.next.data).toEqual(3)
        expect(list.next.next.next.next).toEqual(null)
      })

      it('should be able to insert a new node at index 1 of a list.', () => {
        const list = fn(buildOneTwoThree(), 1.5)
        expect(list.data).toEqual(1)
        expect(list.next.data).toEqual(1.5)
        expect(list.next.next.data).toEqual(2)
        expect(list.next.next.next.data).toEqual(3)
        expect(list.next.next.next.next).toEqual(null)
      })

      it('should be able to insert a new node at index 2 of a list.', () => {
        const list = fn(buildOneTwoThree(), 2.5)
        expect(list.data).toEqual(1)
        expect(list.next.data).toEqual(2)
        expect(list.next.next.data).toEqual(2.5)
        expect(list.next.next.next.data).toEqual(3)
        expect(list.next.next.next.next).toEqual(null)
      })

      it('should be able to insert a new node at tail of a list.', () => {
        const list = fn(buildOneTwoThree(), 3.5)
        expect(list.data).toEqual(1)
        expect(list.next.data).toEqual(2)
        expect(list.next.next.data).toEqual(3)
        expect(list.next.next.next.data).toEqual(3.5)
        expect(list.next.next.next.next).toEqual(null)
      })
    })
  }
})

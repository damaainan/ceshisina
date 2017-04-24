const { buildList } = require('../lib/01-push-and-build-one-two-three')
const { insertNth, insertNthV2, insertNthV3 } = require('../lib/04-insert-nth-node')

describe('04 Insert Nth Node', () => {
  createInsertNthNodeTests(insertNth)
  createInsertNthNodeTests(insertNthV2)
  createInsertNthNodeTests(insertNthV3)

  function createInsertNthNodeTests(fn) {
    describe(fn.name, () => {
      it('should be able to handle an empty list.', () => {
        const list = fn(null, 0, 12)
        expect(list.data).toEqual(12)
        expect(list.next).toEqual(null)
      })

      it('should be able to insert a new node at the head of a list.', () => {
        const list = fn(buildList([1, 2, 3]), 0, 23)
        expect(list).toEqualLinkedList(buildList([23, 1, 2, 3]))
      })

      it('should be able to insert a new node at index 1 of a list.', () => {
        const list = fn(buildList([1, 2, 3]), 1, 23)
        expect(list).toEqualLinkedList(buildList([1, 23, 2, 3]))
      })

      it('should be able to insert a new node at index 2 of a list.', () => {
        const list = fn(buildList([1, 2, 3]), 2, 23)
        expect(list).toEqualLinkedList(buildList([1, 2, 23, 3]))
      })

      it('should be able to insert a new node at tail of a list.', () => {
        const list = fn(buildList([1, 2, 3]), 3, 23)
        expect(list).toEqualLinkedList(buildList([1, 2, 3, 23]))
      })

      it('should throw exception if index is too large.', () => {
        expect(() => fn(buildList([1, 2, 3]), 4, 23)).toThrow('invalid argument')
      })
    })
  }
})

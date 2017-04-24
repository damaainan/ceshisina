const { Node } = require('../lib/00-utils')
const {
  push,
  buildOneTwoThree,
  buildList,
  buildListV2,
  buildListV3,
} = require('../lib/01-push-and-build-one-two-three')

describe('01 Push & BuildOneTwoThree', () => {
  describe('push', () => {
    it('should be able to create a new linked list.', () => {
      const list = push(null, 1)
      expect(list.data).toEqual(1)
      expect(list.next).toEqual(null)
    })

    it('should be able to prepend a node to an existing node.', () => {
      const list = push(new Node(1), 2)
      expect(list.data).toEqual(2)
      expect(list.next.data).toEqual(1)
      expect(list.next.next).toEqual(null)
    })
  })

  describe('buildOneTwoThree', () => {
    it('should build a linked list 1 -> 2 -> 3 -> null', () => {
      const list = buildOneTwoThree()
      expect(list.data).toEqual(1)
      expect(list.next.data).toEqual(2)
      expect(list.next.next.data).toEqual(3)
      expect(list.next.next.next).toEqual(null)
    })
  })

  createbuildListTests(buildList)
  createbuildListTests(buildListV2)
  createbuildListTests(buildListV3)

  function createbuildListTests(fn) {
    describe(fn.name, () => {
      it('should return null for empty data', () => {
        expect(buildList()).toEqual(null)
        expect(buildList(null)).toEqual(null)
        expect(buildList([])).toEqual(null)
      })

      it('should build a linked list', () => {
        const list = fn([1, 2, 3])
        expect(list.data).toEqual(1)
        expect(list.next.data).toEqual(2)
        expect(list.next.next.data).toEqual(3)
        expect(list.next.next.next).toEqual(null)
      })
    })
  }
})

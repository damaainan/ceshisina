const { buildList } = require('../lib/01-push-and-build-one-two-three')
const { getNth, getNthV2 } = require('../lib/03-get-nth-node')

describe('03 Get Nth Node', () => {
  createGetNthTests(getNth)
  createGetNthTests(getNthV2)

  function createGetNthTests(fn) {
    describe(fn.name, () => {
      it('should be able to get nth node', () => {
        const list = buildList([1, 2, 3])
        expect(fn(list, 0).data).toEqual(1)
        expect(fn(list, 1).data).toEqual(2)
        expect(fn(list, 2).data).toEqual(3)
      })

      it('should be able to throw exception', () => {
        const list = buildList([1, 2, 3])
        expect(() => fn(list, -1)).toThrow('invalid argument')
        expect(() => fn(list, 3)).toThrow('invalid argument')
        expect(() => fn(list, 10)).toThrow('invalid argument')
        expect(() => fn(null, 0)).toThrow('invalid argument')
      })
    })
  }
})

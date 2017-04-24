class Node {
  constructor(data = null, next = null) {
    this.data = data
    this.next = next
  }

  isEmpty() {
    return this.data === null
  }
}

module.exports = {
  Node,
}

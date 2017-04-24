function buildRandomArray(max) {
  const re = []
  for (let i = 0; i < max; i++) {
    re.push(getRandomIntInclusive(0, max))
  }
  return re
}

function getRandomIntInclusive(min, max) {
  min = Math.ceil(min)
  max = Math.floor(max)
  return Math.floor(Math.random() * (max - min + 1)) + min
}

module.exports = {
  buildRandomArray,
}

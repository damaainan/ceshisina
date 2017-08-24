/**
 * 莱文斯坦距离，又称Levenshtein距离，是编辑距离的一种。指两个字串之间，由一个转成另一个所需的最少编辑操作次数。许可的编辑操作包括将一个字符替换成另一个字符，插入一个字符，删除一个字符。
 */

//https://rosettacode.org/wiki/Levenshtein_distance#JavaScript





'use strict'


const min = Math.min


// 构造二维数组
const initialArray = (lgt1, lgt2) => {
  let array = []
  for (let i = 0; i < lgt1; i++) {
    array[i] = Array(lgt2)
  }
  return array
}


/**
 * Levenshtein Distance
 * 从一个字符串转成另一个字符串所需要的最小操作步数
 */
const levenshtein = (sa, sb) => {
  let d = []
  , algt = sa.length
  , blgt = sb.length
  , i = 0
  , j = 0


  if(algt === 0) return blgt 


  if(blgt === 0) return algt
  // 初始化二维数组
  d = initialArray(algt + 1, blgt + 1)


  for (i = 0; i < algt + 1; i++) {
    d[i][0]  = i
  }


  for (j = 0; j < blgt + 1; j++) {
    d[0][j] = j
  }


  for(i = 1; i < algt + 1; i++) {
    for(j = 1; j < blgt + 1; j++) {
      if(sa[i - 1] === sb[j - 1]) {
        d[i][j] = d[i - 1][j - 1]
      } else {
        d[i][j] = min(
          d[i - 1][j] + 1,
          d[i][j - 1] + 1,
          d[i - 1][j - 1] + 1
        )  
      }
    }
  }


  return d[i - 1][j - 1]
}



// test case
// copy from https://rosettacode.org/wiki/Levenshtein_distance#JavaScript
;[ 
  ['', '', 0],
  ['yo', '', 2],
  ['', 'yo', 2],
  ['yo', 'yo', 0],
  ['tier', 'tor', 2],
  ['saturday', 'sunday', 3],
  ['mist', 'dist', 1],
  ['tier', 'tor', 2],
  ['kitten', 'sitting', 3],
  ['stop', 'tops', 2],
  ['rosettacode', 'raisethysword', 8],
  ['mississippi', 'swiss miss', 8]
].forEach(function(v) {
  var a = v[0], b = v[1], t = v[2], d = levenshtein(a, b);
  if (d !== t) {
    console.log('levenstein("' + a + '","' + b + '") was ' + d + ' should be ' + t)
  }
})
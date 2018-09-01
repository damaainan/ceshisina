## 使用动态规划 实现字符级Diff &amp; Patch

来源：[https://juejin.im/post/5a82f7016fb9a063475f82f0](https://juejin.im/post/5a82f7016fb9a063475f82f0)

时间 2018-02-16 12:37:46

 
文章开头先上demo，只需键入任意内容的两个字符串，页面上就能自动计算并呈现字符串之间的差分。
 
demo地址： [string-diff-demo.herokuapp.com][1] 
 
源码地址： [github.com/lqt0223/str…][2] 
 
## 动态规划
 
动态规划(dynamic programming)是大家在算法学习中都会遇到的话题之一。我个人对于它的理解是：
 
 
* 动态规划针对的是规模较大的问题 
* 但就像递归那样，问题的base case是有解的 
* 并且同一个问题的较大规模版本的解，可以通过一组规则，从已有的较小规模的同一问题的解中推导而出 
* 动态规划与递归不同的是，后者是利用了方法定义了在其过程中调用自己，“声明式”(declaratively)地形成了整个求解的过程；前者需要建立动态规划矩阵，用以记录每一问题规模下的解，并需要显式地执行循环来不断扩大问题规模和求解，这是“命令式”(imperatively)地形式了求解的过程 
 
 
可以被动态规划解决的问题，常见的有：
 
 
* 背包问题(knapsack problem)：给定一个容量有限的背包，与一组带有重量和价值的物品，如何选择其中的几个放入背包使得价值的和最大 
* 最长公共序列问题(longest common sequence problem，下文简称为LCS问题)：求两个字符串中，最长的公共序列。公共序列指的是在两个字符串中都出现的序列，这个序列在原字符串中不一定是连续的 
* 子集和问题(subset sum problem)：给定一个整数集合，是否存在它的非空子集使子集内的数字和为0 
* ... 
 
 
## 最长公共序列问题与Diff & Patch算法的关系
 
曾经，我自己在codewar等网站上做算法题时，很多次刷到"longest common sequence"或者类似的题目，也通过一些算法书了解到这类问题的一个比较易于理解的算法是动态规划。但我一直不太明白这类问题的实际应用何在。直到最近看到了下面的论文：
 
[An O(ND) Difference Algorithm and Its Variations - EUGENE W. MYERS][3] 
 
此文我只读了其中的1-2节，总结一下它的内容其实是：使用图算法，求解两个字符串之间的LCS，以及 **`最短编辑步骤`**  （shortest edit script，以下简称SES，指的是从字符串A变换至字符串B，所需要的步骤。步骤是针对字符串的操作，例如删除某一位置上的字符、在某一位置上插入字符等）。 
 
从此文可知：LCS和SES是对偶问题(dual problem)，这两个问题只不过是一个优化问题的两个方面。即，当我们寻找两个字符串的公共子序列时，如果已经找到了最优解（最长公共子序列），那么在此最优解情形下的两个字符串之间的编辑步骤，也就是最短编辑步骤。通俗地讲， **`求解LCS的过程中，我们就可以得到SES`**  。 
 
由于SES描述了从一个字符串到另一个字符串的一系列操作步骤，这就类似于各类数据比较工具产生的差量数据。于是我们知道了，LCS问题的实际应用之一，就是数据的比较、差量计算和差量更新。
 
## 使用矩阵转化LCS和SES问题
 
由于是用动态规划来求解LCS和SES问题，我们需要用到矩阵（二维数组）来记录最优解的一些信息。
 
这一小节主要是说明在使用矩阵求解以上问题的过程中，矩阵有哪些性质，以及这些性质对应着LCS或SES问题的什么方面。这些内容也是对于上一小节中提到的论文第2节内容的归纳和简化。
 
如果之前没有接触过使用动态规划求解LCS问题的话，可以看一下下面的视频，从而对于这一求解过程有一个基本概念。
 
[Longest Common Subsequence - Tushar Roy - Youtube][4] 
 
总体来说，使用矩阵转化并求解LCS和SES问题需要以下三个阶段：
 
 
* 初始化阶段。假设字符串A长度为m，字符串B长度为n，则初始化一个m + 1 * n + 1的矩阵，将矩阵的第一列和第一行都初始化为0（矩阵中后续需要填入的是LCS的长度，所以在初始化时，第一行或第一列表示两个字符串中的任意一个为空的情况，需要填入0） 
* 推算阶段。从左至右从上到下，根据一定的推演规则，填写矩阵。即，不断地求解字符串A或B的前缀之间的LCS的长度 
* 回溯(backtracking)阶段。当矩阵填满时，位于矩阵最右下角的值即是字符串A和B的LCS的长度。如果需要进一步找出LCS是什么，则需要从矩阵的右下角出发，按一定的规则，找到一个到达矩阵左上角的路径，保证经过路径时，LCS的长度值每次减小0或1。 
 
 
经过三个阶段后，矩阵会变成类似下图的形式。
 
![][0] 

图中是字符串A为"abcabba"，字符串B为"cbabac"时，使用动态规划求解LCS和SES形成的矩阵。由此矩阵我们可以得出以下关于两个字符串之间的LCS和SES的相关答案：
 
 
* 两个字符串的LCS长度为4（即矩阵最右下角位置所填入的值） 
* 两个字符串的LCS为"caba"（这是完成回溯后，通过观察红色箭头所形成的路径而得来；观察上图可知，回溯阶段时，每一次遇到需要向左上角移动的情况下，该坐标对应的字符串A内的某一字符与字符串B内的某一字符相同，即这个字符可以作为LCS的组成字符之一） 
*  回溯时的每一次移动都可以映射为SES中的某一步： 
 
 
* 向左上角移动，意味着找到了组成LCS的一个字符串，对于SES来说，表示不需要操作 
* 向左移动，对于SES来说，意味着在字符串A中的指定位置删除字符 
*  向上移动 
   
 
* 如果是在矩阵的第1列（也就是全部被初始化为0的最左边一列）向上移动，对于SES来说，意味着在字符串A的头部添加字符 
* 如果是在矩阵的其他列向上移动，对于SES来说，意味着在字符串A的指定位置的后面添加字符 
     
  
   
  
 
 
  
例：字符串A为"abcabba"，字符串B为"cbabac"时，如何知道经过什么样的步骤，可以最快地将字符串A变为字符串B呢？我们可以使用上面的规则，将红色路径翻译成我们需要的SES
 

 
* 删除字符串A的第1、2个字符（最左上角的两个向左箭头） 
* 在字符串A的第3个位置添加字符"b"（从左上至右下的第四个向上箭头） 
* 删除字符串A的第6个字符（从左上至右下的倒数第三个向左箭头） 
* 在字符串A的第7个位置添加字符"c"（最右下角的向上箭头） 
  
 
经过上述操作后我们就可以将字符串A变换为字符串B
 
 
 
## SES的同时操作问题
 
上一节的末尾给出了从"abcabba"到"cbabac"的SES，也许你试着用草稿纸或者其他工具来使用这段SES，但却无法顺利地完成字符串的转换。这是因为：SES所表示的编译步骤，需要被 **`同时操作`**  。这个说法比较抽象，下面使用"abcabba"到"cbabac"例子，说明SES的正确用法： 
 
  
原字符串
 
```
a b c a b b a
```
 

 
* 删除字符串A的第1、2个字符（最左上角的两个向左箭头）（这里用*标记将要被删除的字符）

```
* * c a b b a
```
  
* 在字符串A的第3个位置添加字符"b"（从左上至右下的第四个向上箭头）

```
* * c a b b a
        b
```
  
* 删除字符串A的第6个字符（从左上至右下的倒数第三个向左箭头）

```
* * c a b * a
        b
```
  
* 在字符串A的第7个位置添加字符"c"（最右下角的向上箭头）

```
* * c a b * a
        b     c
```
  
* 将以上类似于hashTable的结构还原为一个字符串，规则为：遇到需要删除的字符时则忽略，遇到纵向伸展的list时将其连缀为一个子字符串，最后将所有子字符串按顺序连接，即得到"cbabac"
  
  
 
 
 
由此可知，SES的 **`同时操作`**  ，指的是任何一个操作步骤，都不应该影响到字符串最初的字符排列。我们可以用这种纵向的数据结构，重新整理字符串操作，并在最后转换成目标字符串。 
 
## 差分可视化
 
如上一小节所示，SES的应用之一就是直接执行，其结果就是生成目标字符串。
 
我们也可以结合原字符串和SES，生成DOM String，在浏览器中将原字符串到目标字符串的差分呈现出来。本文开头的demo即是对于这种应用方式的展示。
 
```html
<html>
  <body>
    <input id="str1">
    <input id="str2">
    <p id="result"></p>

    <script>
      // solving 'longest common sequence' problem using dynamic programming
      // will return lcs, and ses (shortest edit script) for the two string arguments
      function dp_lcs(str1, str2) {
        var len1 = str1.length
        var len2 = str2.length
        var lcsLengths = initMatrix(len1, len2)
        fill(lcsLengths, str1, str2)
        var info = backtrack(lcsLengths, str1, str2)
        return info
      }
      function backtrack(matrix, str1, str2) {
        var lcs = []
        var ses = []
        // an internal function to fetch element from matrix
        function ref(m, x, y) {
          var t = m[x]
          if (t === undefined) {
            return undefined
          } else {
            t = t[y]
            if (t === undefined) {
              return undefined
            } else {
              return t
            }
          }
        }
        function walk(x, y) {
          var top = ref(matrix, x - 1, y)
          var left = ref(matrix, x, y - 1)
          var topLeft = ref(matrix, x - 1, y - 1)
          var focus = ref(matrix, x, y)
          // base case when top boundary is reached, can only backtrack leftward
          // this case implies that some heading letters in str1 need to be deleted to form str2
          if (top === undefined && topLeft === undefined && left !== undefined && focus !== undefined) {
            ses.push({
              op: 'delete',
              index: y
            })
            return walk(x, y - 1)
          // base case when left boundary is reached, can only backtrack upward
          // this case implies that some letters need to be added before the head of str1 to form str2
          } else if (top !== undefined && topLeft === undefined && left === undefined && focus !== undefined) {
            ses.push({
              op: 'insertBefore',
              index: x,
              element: str2[x - 1]
            })
            return walk(x - 1, y)
          // base case when top left corner is reached
          // this case marks the end of backtracking
          } else if (top === undefined && topLeft === undefined && left === undefined && focus !== undefined) {
            lcs.reverse()
            lcs = lcs.join('')
            ses.reverse()
            return {
              lcs, ses
            }
          // recursive case when free to move in the matrix
          // a leftward move stands for deletion
          // a upward move stands for insertion
          // a top-leftward move stands for no-operation, and the letter the backtracker is on will be included in the lcs
          } else {
            if (top == left && top == topLeft && focus - topLeft == 1) {
              lcs.push(str1[y - 1])
              return walk(x - 1, y - 1)
            } else {
              if (top == focus) {
                ses.push({
                  op: 'insert',
                  index: y,
                  element: str2[x - 1]
                })
                return walk(x - 1, y)
              } else {
                ses.push({
                  op: 'delete',
                  index: y
                })
                return walk(x, y - 1)
              }
            }
          }
        }
        var len1 = matrix.length - 1
        var len2 = matrix[0].length - 1
        return walk(len1, len2)
      }
      function fill(matrix, str1, str2) {
        var i = 1, j = 1
        var len1 = str1.length
        var len2 = str2.length
        while (i <= len2) {
          matrix[i][j] = getSolution(matrix, i, j, str1, str2)
          j++
          if (j >= len1 + 1) {
            j = 1
            i++
          }
        }
      }
      function getSolution(matrix, i, j, str1, str2) {
        var char1 = str1[j - 1]
        var char2 = str2[i - 1]
        var leftTop = matrix[i - 1][j - 1]
        var top = matrix[i - 1][j]
        var left = matrix[i][j - 1]
        if (char1 == char2 && top == left) {
          return leftTop + 1
        } else {
          return Math.max(left, top)
        }
      }
      function initMatrix(a, b) {
        var row = new Array(a + 1)
        row[0] = 0
        var matrix = new Array(b + 1)
        for (var i = 0; i < b + 1; i++) {
          matrix[i] = row.slice()
        }
        matrix[0].fill(0)
        return matrix
      }
      // apply edit scripts to string to transform it into target string
      function patch(str, edits) {
        // convert every letter in str into nodes to patch
        str = str.split('').map(c => {
          return {
            value: c,
            head: [],
            trail: [],
            delete: false
          }
        })
        for (var i = 0; i < edits.length; i++) {
          var {op, index, element} = edits[i]
          if (op == 'delete') {
            // a placeholder for letter to be deleted
            str[index - 1].delete = true
          } else if (op == 'insert') {
            // append letter to be added into the letter right before
            str[index - 1].trail.push(element)
          } else if (op == 'insertBefore') {
            // append letter to be added into the letter right after
            if (str[0] === undefined) {
              str[0] = {head: []}
            }
            str[0].head.push(element)
          }
        }
        // resolve the str (which now has an hirearchical structure) into a flat string as result
        var result = ''
        for (var i = 0; i < str.length; i++) {
          var node = str[i]
          if (node.head) {
            result += node.head.join('')
          }
          if (node.delete == false) {
            result += node.value
          }
         if (node.trail) {
            result += node.trail.join('')
          }
        }
        return result
      }
      // accept source string and edits, and return
      // a string representation of DOM, showing the diff using text decoration and background color
      function visualize(str, edits) {
        str = str.split('').map(e => {
          return {
            value: e,
            flag: 0, // 0 for normal text node, 1 for an text node to insert, 2 for a text node to delete
            head: [],
            trail: []
          }
        })
        // apply edits
        for (var i = 0; i < edits.length; i++) {
          var {op, index, element} = edits[i]
          if (op == 'delete') {
            // a placeholder for letter to be deleted
            str[index - 1].flag = 2
          } else if (op == 'insert') {
            // append letter to be added into the letter right before
            str[index - 1].trail.push({
              value: element,
              flag: 1
            })
          } else if (op == 'insertBefore') {
            // append letter to be added into the letter right after
            if (str[0] === undefined) {
              str[0] = {head: []}
            }
            str[0].head.push({
              value: element,
              flag: 1
            })
          }
        }
        // flatten str into one dimensional node array
        var arr = []
        for (var i = 0; i < str.length; i++) {
          var node = str[i]
          if (node.head) {
            arr = arr.concat(node.head)
          }
          if (node.value !== undefined) {
            arr.push(node)
          }
         if (node.trail) {
            arr = arr.concat(node.trail)
          }
        }
        // map the node array into dom string
        arr = arr.map(node => {
          return `<span style="${stylize(node.flag)}">${node.value}</span>`
        })
        return arr.join('')
      }
      // return CSS inline style attribute value for flag
      function stylize(flag) {
        switch(flag) {
          case 0: {
            return ''
            break
          }
          case 1: {
            return 'background-color:lightgreen;'
            break
          }
          case 2: {
            return 'background-color:lightsalmon;text-decoration:line-through'
          }
        }
      }
    </script>
    <script>
      // main
      var s1 = document.querySelector('#str1')
      var s2 = document.querySelector('#str2')
      s1.oninput = render
      s2.oninput = render
      function render() {
        var str1 = s1.value
        var str2 = s2.value
        var {ses} = dp_lcs(str1, str2)
        var dom = visualize(str1, ses)
        document.querySelector('#result').innerHTML = dom
      }
    </script>
  </body>
</html>
```

[1]: https://link.juejin.im?target=https%3A%2F%2Fstring-diff-demo.herokuapp.com
[2]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Flqt0223%2Fstring-diff-demo%2Fblob%2Fmaster%2Findex.html
[3]: https://link.juejin.im?target=http%3A%2F%2Fwww.xmailserver.org%2Fdiff2.pdf
[4]: https://link.juejin.im?target=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3DNnD96abizww%26amp%3Bt%3D376s
[0]: ./img/ZfYjUjJ.png
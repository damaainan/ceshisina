## 初学者应该了解的数据结构：Array、HashMap 与 List

来源：[http://www.zcfy.cc/article/data-structures-for-beginners-arrays-hashmaps-and-lists](http://www.zcfy.cc/article/data-structures-for-beginners-arrays-hashmaps-and-lists)

时间 2018-06-30 15:34:40

 
![][0]
 
当开发程序时，我们（通常）需要在内存中存储数据。根据操作数据方式的不同，可能会选择不同的数据结构。有很多常用的数据结构，如：Array、Map、Set、List、Tree、Graph 等等。（然而）为程序选取合适的数据结构可能并不容易。因此，希望这篇文章能帮助你了解（不同数据结构的）表现，以求在工作中合理地使用它们。
 
本文主要聚焦于线性的数据结构，如：Array、Set、List、Sets、Stacks、Queues 等等。
 
本篇是以下教程的一部分（译者注：如果大家觉得还不错，我会翻译整个系列的文章）:
 
#### 初学者应该了解的数据结构与算法（DSA）
 
 
* [算法的时间复杂性与大 O 符号][9]  
* [每个程序员应该知道的八种时间复杂度][10]  
* 初学者应该了解的数据结构：Array、HashMap 与 List **`:point_left: 即本文`**   
* [初学者应该了解的数据结构： Graph][11]  
* 初学者应该了解的数据结构：Tree ( **`敬请期待`**  )  
* [附录 I：递归算法分析][12]  
 
 
## （操作）数据结构的时间复杂度
 
下表是本文所讨论内容的概括。
 
加个书签、收藏或分享本文，以便不时之需。
 
* = 运行时分摊
 
| 数据结构 | 插入 | 访问 | 查找 | 删除 | 备注 | 
|-|-|-|-|-|-|
| **`Array`** | O(n) | O(1) | O(n) | O(n) | 插入最后位置复杂度为 O(1)  。 | 
| (Hash) **`Map`** | O(1)* | O(1)* | O(1)* | O(1)* | 重新计算哈希会影响插入时间。 | 
| **`Map`** | O(log(n)) | - | O(log(n)) | O(log(n)) | 通过二叉搜索树实现 | 
| **`Set`**  （使用 HashMap） | O(1）* | - | O(1)* | O(1)* | 由 HashMap 实现 | 
| **`Set`**  (使用 List) | [O(n)][13] | - | [O(n)][14] ] | [O(n)][15] | 通过 List 实现 | 
| **`Set`**  (使用二叉搜索树) | O(log(n)) | - | O(log(n)) | O(log(n)) | 通过二叉搜索树实现 | 
| **`Linked List`**  (单向) | O(n) | - | O(n) | O(n) | 在起始位置添加或删除元素，复杂度为 O(1) | 
| **`Linked List`**  (双向） | O(n) | - | O(n) | O(n) | 在起始或结尾添加或删除元素，复杂度为 O(1)  。然而在其他位置是 O(n)  。 | 
| **`Stack`**  (由 Array 实现) | O(1) | - | - | O(1)] | 插入与删除都遵循与后进先出（LIFO） | 
| **`Queue`**  (简单地由 Array 实现) | O(n) | - | - | O(1) | 插入（Array.shift）操作的复杂度是 O(n) | 
| **`Queue`**  (由 Array 实现，但进行了改进) | O(1)* | - | - | O(1) | 插入操作的最差情况复杂度是 O(n)  。然而分摊后是 O(1) | 
| **`Queue`**  (由 List 实现) | O(1) | - | - | O(1) | 使用双向链表 | 
 
 
注意： **`二叉搜索树`**  与其他树结构、图结构，将在另一篇文章中讨论。
 
## 原始数据类型
 
原始数据类型是构成数据结构最基础的元素。下面列举出一些原始原始数据类型：
 
 
* 整数，如：1, 2, 3, … 
* 字符，如：a, b, "1", "*" 
* 布尔值， true 与 false. 
* 浮点数 ，如：3.14159, 1483e-2. 
 
 
## Array
 
数组可由零个或多个元素组成。由于数组易于使用且检索性能优越，它是最常用的数据结构之一。
 
你可以将数组想象成一个抽屉，可以将数据存到匣子中。
 
#### 数组就像是将东西存到匣子中的抽屉
 
![][1]
 
当你想查找某个元素时，你可以直接打开对应编号的匣子（时间复杂度为 O(1)  ）。然而，如果你忘记了匣子里存着什么，就必须逐个打开所有的匣子（时间复杂度为 O(n)  ），直到找到所需的东西。数组也是如此。
 
根据编程语言的不同，数组存在一些差异。对于 JavaScript 和 Ruby 等动态语言而言，数组可以包含不同的数据类型：数字，字符串，对象甚至函数。而在 Java 、 C 、C ++ 之类的强类型语言中，你必须在使用数组之前，定好它的长度与数据类型。JavaScript 会在需要时自动增加数组的长度。
 
## Array 的内置方法
 
根据编程序言的不同，数组（方法）的实现稍有不同。
 
比如在 JavaScript 中，我们可以使用`unshift`与`push`添加元素到数组的头或尾，同时也可以使用`shift`与`pop`删除数组的首个或最后一个元素。让我们来定义一些本文用到的数组常用方法。
 
#### 常用的 JS 数组内置函数
 
| 函数 | 复杂度 | 描述 | 
|-|-|-|
| [ array. push (element1[, …[, elementN]]) ][16] | O(1) | 将一个或多个元素添加到数组的末尾 | 
| [ array. pop () ][17] | O(1) | 移除数组末尾的元素 | 
| [ array. shift () ][18] | O(n) | 移除数组开头的元素 | 
| [ array. unshift (element1[, …[, elementN]]) ][19] | O(n) | 将一个或多个元素添加到数组的开头 | 
| [ array. slice ([beginning[, end]]) ][20] | O(n) | 返回浅拷贝原数组从`beginning`到`end`（不包括`end`）部分组成的新数组 | 
| [ array. splice (start[, deleteCount[, item1[,…]]]) ][21] | O(n) | 改变 (插入或删除) 数组 | 
 
 
## 向数组插入元素
 
将元素插入到数组有很多方式。你可以将新数据添加到数组末尾，也可以添加到数组开头。
 
先看看如何添加到末尾：
 
```js
function insertToTail(array, element) {
  array.push(element);
  return array;
}

const array = [1, 2, 3];
console.log(insertToTail(array, 4)); // => [ 1, 2, 3, 4 ]
```
 
根据 [规范][22] ，`push`操作只是将一个新元素添加到数组的末尾。因此，
 `Array.push`的时间复杂度度是 O(1)  。
 
现在看看如添加到开头：
 
```js
function insertToHead(array, element) {
  array.unshift(element);
  return array;
}

const array = [1, 2, 3];
console.log(insertToHead(array, 0));// => [ 0, 1, 2, 3, ]
```
 
你觉得添加元素到数组开头的函数，时间复杂度是什么呢？看起来和上面（`push`）差不多，除了调用的方法是`unshift`而不是`push`。但这有个问题， [ unshift ][23] 是通过将数组的每一项移到下一项，腾出首项的空间来容纳新添加的元素。所以它是遍历了一次数组的。
 `Array.unshift`的时间复杂度度是 O(n)  。
 
## 访问数组中的元素
 
如果你知道待查找元素在数组中的索引，那你可以通过以下方法直接访问该元素：
 
```js
function access(array, index) {
  return array[index];
}

const array = [1, 'word', 3.14, { a: 1 }];
access(array, 0);// => 1
access(array, 3);// => {a: 1}
```
 
正如上面你所看到的的代码一样，访问数组中的元素耗时是恒定的：
 
访问数组中元素的时间复杂度是 O(1)  。
 
注意：通过索引修改数组的值所花费的时间也是恒定的。
 
## 在数组中查找元素
 
如果你想查找某个元素但不知道对应的索引时，那只能通过遍历数组的每个元素，直到找到为止。
 
```js
function search(array, element) {
  for (let index = 0;
       index < array.length;
       index++) {
    if (element === array[index]) {
      return index;
    }
  }
}

const array = [1, 'word', 3.14, { a: 1 }];
console.log(search(array, 'word'));// => 1
console.log(search(array, 3.14));// => 2
```
 
鉴于使用了`for`循环，那么：
 
在数组中查找元素的时间复杂度是 O(n) 
 
## 在数组中删除元素
 
你觉得从数组中删除元素的时间复杂度是什么呢？
 
先一起思考下这两种情况：
 
 
* 从数组的末尾删除元素所需时间是恒定的，也就是 O(1)  。  
* 然而，无论是从数组的开头或是中间位置删除元素，你都需要调整（删除元素后面的）元素位置。因此复杂度为 O(n)  。  
 
 
说多无谓，看代码好了：
 
```js
function remove(array, element) {
  const index = search(array, element);
  array.splice(index, 1);
  return array;
}

const array1 = [0, 1, 2, 3];
console.log(remove(array1, 1));// => [ 0, 2, 3 ]
```
 
我们使用了上面定义的`search`函数来查找元素的的索引，复杂度为 O(n)  。然后使用 [ JS 内置的 splice ][24] 方法，它的复杂度也是 O(n)  。那（删除函数）总的时间复杂度不是 O(2n)  吗?记住，（对于时间复杂度而言，）我们并不关心常量。
 
对于上面列举的两种情况，考虑最坏的情况：
 
在数组中删除某项元素的时间复杂度是 O(n)  。
 
## 数组方法的时间复杂度
 
在下表中，小结了数组（方法）的时间复杂度：
 
#### 数组方法的时间复杂度
 
| 操作方法 | 最坏情况 | 
|-|-|
| 访问 (`Array.[]`) | O(1) | 
| 添加新元素至开头 (`Array.unshift`) | O(n) | 
| 添加新元素至末尾 (`Array.push`) | O(1) | 
| 查找 (通过值而非索引) | O(n) | 
| 删除 (`Array.splice`) | O(n) | 
 
 
## HashMaps
 
HashMap有很多名字，如 HashTableHashMap、Map、Dictionary、Associative Array 等。概念上它们都是一致的，实现上稍有不同。
 
哈希表是一种将键 **`映射到`**  值的数据结构。
 
回想一下关于抽屉的比喻，现在匣子有了标签，不再是按数字顺序了。
 
#### HashMap 也和抽屉一样存储东西，通过不同标识来区分不同匣子。
 
![][2]
 
此例中，如果你要找一个玩具，你不需要依次打开第一个、第二个和第三个匣子来查看玩具是否在内。直接代开被标识为“玩具”的匣子即可。这是一个巨大的进步，查找元素的时间复杂度从 O(n)  降为 O(1)  了。
 
数字是数组的索引，而标识则作为 HashMap 存储数据的键。HashMap 内部通过 哈希函数  将键（也就是标识）转化为索引。
 
至少有两种方式可以实现 hashmap：
 
 
* **`数组`**  ：通过哈希函数将键映射为数组的索引。（查找）最差情况： O(n)，平均： O(1)。  
* **`二叉搜索树`**  : 使用自平衡二叉搜索树查找值（另外的文章会详细介绍）。 （查找）最差情况： O(log n)  ，平均： O(log n)  。  
 
 
我们会介绍树与二叉搜索树，现在先不用担心太多。实现 Map 最常用的方式是使用 **`数组`**  与哈希转换函数。让我们（通过数组）来实现它吧
 
#### 通过数组实现 HashMap
 
![][3]
 
正如上图所示，每个键都被转换为一个 **`hash code`**  。由于数组的大小是有限的（如此例中是10），（如发生冲突，）我们必须使用模函数找到对应的桶（译者注：桶指的是数组的项），再循环遍历该桶（来寻找待查询的值）。每个桶内，我们存储的是一组组的键值对，如果桶内存储了多个键值对，将采用集合来存储它们。
 
我们将讲述 HashMap 的组成，让我们先从 **`哈希函数`**  开始吧。
 
## 哈希函数
 
实现 HashMap 的第一步是写出一个哈希函数。这个函数会将键映射为对应（索引的）值。
 
完美的哈希函数是为每一个不同的键映射为不同的索引。
 
借助理想的哈希函数，可以实现访问与查找在恒定时间内完成。然而，完美的哈希函数在实践中是难以实现的。你很可能会碰到两个不同的键被映射为同一索引的情况，也就是 _冲突_。
 
当使用类似数组之类的数据结构作为 HashMap 的实现时，冲突是难以避免的。因此，解决冲突的其中一种方式是在同一个桶中存储多个值。当我们试图访问某个键对应的值时，如果在对应的桶中发现多组键值对，则需要遍历它们（以寻找该键对应的值），时间复杂度为 O(n)  。然而，在大多数（HashMap）的实现中， HashMap 会动态调整数组的长度以免冲突发生过多。因此我们可以说 **`分摊后`**  的查找时间为 O(1)  。本文中我们将通过一个例子，讲述分摊的含义。
 
## HashMap 的简单实现
 
一个简单（但糟糕）的哈希函数可以是这样的：
 
```js
class NaiveHashMap {

  constructor(initialCapacity = 2) {
    this.buckets = new Array(initialCapacity);
  }

  set(key, value) {
    const index = this.getIndex(key);
    this.buckets[index] = value;
  }

  get(key) {
    const index = this.getIndex(key);
    return this.buckets[index];
  }

  hash(key) {
    return key.toString().length;
  }

  getIndex(key) {
    const indexHash = this.hash(key);
    const index = indexHash % this.buckets.length;
    return index;
  }
}
```
 
[完整代码][25]
 
我们直接使用桶而不是抽屉与匣子，相信你能明白喻义的意思 :)
 
HashMap 的初始容量（译者注：容量指的是用于存储数据的数组长度，即桶的数量）是2（两个桶）。当我们往里面存储多个元素时，通过求余`%`计算出该键应存入桶的编号（，并将数据存入该桶中）。
 
留意代码的第18行（即`return key.toString().length;`）。之后我们会对此进行一点讨论。现在先让我们使用一下这个新的 HashMap 吧。
 
```js
// Usage:
const assert = require('assert');
const hashMap = new NaiveHashMap();
hashMap.set('cat', 2);
hashMap.set('rat', 7);
hashMap.set('dog', 1);
hashMap.set('art', 8);
console.log(hashMap.buckets);
/*
  bucket #0: <1 empty item>,
  bucket #1: 8
*/
assert.equal(hashMap.get('art'), 8); // this one is ok
assert.equal(hashMap.get('cat'), 8); // got overwritten by art :scream:
assert.equal(hashMap.get('rat'), 8); // got overwritten by art :scream:
assert.equal(hashMap.get('dog'), 8); // got overwritten by art :scream:
```
 
这个 HashMap 允许我们通过`set`方法设置一组键值对，通过往`get`方法传入一个键来获取对应的值。其中的关键是哈希函数，当我们存入多组键值时，看看这 HashMap 的表现。
 
你能说出这个简单实现的 HashMap 存在的问题吗？
 
1) Hash function转换出太多相同的索引。如：
 
```js
hash('cat') // 3
hash('dog') // 3
```
 
这会产生非常多的冲突。
 
2)完全不处理 **`冲突`**  的情况。`cat`与`dog`会重写彼此在 HashMap 中的值（它们均在桶 #1 中）。
 
3 数组长度。 即使我们有一个更好的哈希函数，由于数组的长度是2，少于存入元素的数量，还是会产生很多冲突。我们希望 HashMap 的初始容量大于我们存入数据的数量。
 
## 改进哈希函数
 
HashMap 的主要目标是将数组查找与访问的时间复杂度，从 O(n)  降至 O(1)  。
 
为此，我们需要：
 
 
* 一个合适的哈希函数，尽可能地减少冲突。 
* 一个长度足够大的数组用于保存数据。 
 
 
让我们重新设计哈希函数，不再采用字符串的长度为 hash code，取而代之是使用字符串中每个字符的 [ascii 码][26] 的总和为 hash code。
 
```js
hash(key) {
  let hashValue = 0;
  const stringKey = key.toString();
  for (let index = 0; index < stringKey.length; index++) {
    const charCode = stringKey.charCodeAt(index);
    hashValue += charCode;
  }
  return hashValue;
}
```
 
再试一次：
 
```js
hash('cat') // 312  (c=99 + a=97 + t=116)
hash('dog') // 314 (d=100 + o=111 + g=103)
```
 
这函数比之前的要好！这是因为相同长度的单词由不一样的字母组成，因而 ascii 码的总和不一样。
 
然而，仍然有问题！单词 rat 与 art 转换后都是327，产生 **`冲突`**  了！ :boom:
 
可以通过根据字符位置左移它的 ascii 码来解决：
 
```js
hash(key) {
  let hashValue = 0;
  const stringKey = `${key}`;
  for (let index = 0; index < stringKey.length; index++) {
    const charCode = stringKey.charCodeAt(index);
    hashValue += charCode << (index * 8);
  }
  return hashValue;
}
```
 
现在继续试验，下面列举出了十六进制的数字，这样可以方便我们观察位移。
 
```js
// r = 114 or 0x72; a = 97 or 0x61; t = 116 or 0x74

hash('rat'); // 7,627,122 (r: 114 * 1 + a: 97 * 256 + t: 116 * 65,536) or in hex: 0x726174 (r: 0x72 + a: 0x6100 + t: 0x740000)

hash('art'); // 7,631,457 or 0x617274
```
 
然而，以下两种类型有何不同呢？
 
```js
hash(1); // 49
hash('1'); // 49
hash('1,2,3'); // 741485668
hash([1,2,3]); // 741485668
hash('undefined') // 3402815551
hash(undefined) // 3402815551
```
 
天啊，仍然有问题！！不同的数据类型不应该返回相同的 hash code！
 
该如何解决呢？
 
其中一种方式是在哈希函数中，将数据的类型作为转换 hash code 的一部分。
 
```js
hash(key) {
  let hashValue = 0;
  const stringTypeKey = `${key}${typeof key}`;
  for (let index = 0; index < stringTypeKey.length; index++) {
    const charCode = stringTypeKey.charCodeAt(index);
    hashValue += charCode << (index * 8);
  }
  return hashValue;
}
```
 
让我们让我们再试一次：
 
```js
console.log(hash(1)); // 1843909523
console.log(hash('1')); // 1927012762
console.log(hash('1,2,3')); // 2668498381
console.log(hash([1,2,3])); // 2533949129
console.log(hash('undefined')); // 5329828264
console.log(hash(undefined)); // 6940203017
```
 
Yay!!! :tada: 我们终于有了更好的哈希函数！
 
同时，我们可以改变 HashMap 的原始容量以减少冲突，让我们在下一节中优化 HashMap。
 
## 更完善的 HashMap 实现
 
通过优化好的哈希函数，HashMap 可以表现得更好。
 
尽管冲突仍可能发生，但通过一些方式可以很好地处理它们。
 
对于我们的 HashMap，希望有以下改进：
 
 
* **`哈希函数`**  ， 检查类型与计算各字符（ascii 码的总和）以减少冲突的发生。  
* **`处理冲突`**  ，通过将值添加到集合中来解决这问题，同时需要一个计数器追踪冲突的数量。  
 
 
更完善 HashMap 实现 [完整代码][27]
 
```js
class DecentHashMap {
  constructor(initialCapacity = 2) {
    this.buckets = new Array(initialCapacity);
    this.collisions = 0;
  }
  set(key, value) {
    const bucketIndex = this.getIndex(key);
    if(this.buckets[bucketIndex]) {
      this.buckets[bucketIndex].push({key, value});
      if(this.buckets[bucketIndex].length > 1) { this.collisions++; }
    } else {
      this.buckets[bucketIndex] = [{key, value}];
    }
    return this;
  }
  get(key) {
    const bucketIndex = this.getIndex(key);
    for (let arrayIndex = 0; arrayIndex < this.buckets[bucketIndex].length; arrayIndex++) {
      const entry = this.buckets[bucketIndex][arrayIndex];
      if(entry.key === key) {
        return entry.value
      }
    }
  }
  hash(key) {
    let hashValue = 0;
    const stringTypeKey = `${key}${typeof key}`;
    for (let index = 0; index < stringTypeKey.length; index++) {
      const charCode = stringTypeKey.charCodeAt(index);
      hashValue += charCode << (index * 8);
    }
    return hashValue;
  }
  getIndex(key) {
    const indexHash = this.hash(key);
    const index = indexHash % this.buckets.length;
    return index;
  }
}
```
 
看看这个 HashMap 表现如何：
 
```js
// Usage:
const assert = require('assert');
const hashMap = new DecentHashMap();
hashMap.set('cat', 2);
hashMap.set('rat', 7);
hashMap.set('dog', 1);
hashMap.set('art', 8);
console.log('collisions: ', hashMap.collisions); // 2
console.log(hashMap.buckets);
/*
  bucket #0: [ { key: 'cat', value: 2 }, { key: 'art', value: 8 } ]
  bucket #1: [ { key: 'rat', value: 7 }, { key: 'dog', value: 1 } ]
*/
assert.equal(hashMap.get('art'), 8); // this one is ok
assert.equal(hashMap.get('cat'), 2); // Good. Didn't got overwritten by art
assert.equal(hashMap.get('rat'), 7); // Good. Didn't got overwritten by art
assert.equal(hashMap.get('dog'), 1); // Good. Didn't got overwritten by art
```
 
完善后的 HashMap 很好地完成了工作，但仍然有一些问题。使用改良后的哈希函数不容易产生重复的值，这非常好。然而，在桶#0与桶#1中都有两个值。这是为什么呢？？
 
由于 HashMap 的容量是2，尽管算出来的 hash code 是不一样的，当求余后算出所需放进桶的编号时，结果不是桶#0就是桶#1。
 
```js
hash('cat') => 3789411390; bucketIndex => 3789411390 % 2 = 0
hash('art') => 3789415740; bucketIndex => 3789415740 % 2 = 0
hash('dog') => 3788563007; bucketIndex => 3788563007 % 2 = 1
hash('rat') => 3789411405; bucketIndex => 3789411405 % 2 = 1
```
 
很自然地想到，可以通过增加 HashMap 的原始容量来解决这个问题，但原始容量应该是多少呢？先来看看容量是如何影响 HashMap 的表现的。
 
如果初始容量是1，那么所有的键值对都会被存入同一个桶，即桶#0。查找操作并不比纯粹用数组存储数据的时间复杂度简单，它们都是 O(n)  。
 
而假设将初始容量定为10：
 
```js
const hashMapSize10 = new DecentHashMap(10);
hashMapSize10.set('cat', 2);
hashMapSize10.set('rat', 7);
hashMapSize10.set('dog', 1);
hashMapSize10.set('art', 8);
console.log('collisions: ', hashMapSize10.collisions); // 1
console.log('hashMapSize10\n', hashMapSize10.buckets);
/*
  bucket#0: [ { key: 'cat', value: 2 }, { key: 'art', value: 8 } ],
            <4 empty items>,
  bucket#5: [ { key: 'rat', value: 7 } ],
            <1 empty item>,
  bucket#7: [ { key: 'dog', value: 1 } ],
            <2 empty items>
*/
```
 
换个角度看：
 
![][3]
 
正如你所看到的，通过增加 HashMap 的容量，能有效减少冲突次数。
 
那换个更大的试试怎样，比如 :100::
 
```js
const hashMapSize100 = new DecentHashMap(100);
hashMapSize100.set('cat', 2);
hashMapSize100.set('rat', 7);
hashMapSize100.set('dog', 1);
hashMapSize100.set('art', 8);
console.log('collisions: ', hashMapSize100.collisions); // 0
console.log('hashMapSize100\n', hashMapSize100.buckets);
/*
            <5 empty items>,
  bucket#5: [ { key: 'rat', value: 7 } ],
            <1 empty item>,
  bucket#7: [ { key: 'dog', value: 1 } ],
            <32 empty items>,
  bucket#41: [ { key: 'art', value: 8 } ],
            <49 empty items>,
  bucket#90: [ { key: 'cat', value: 2 } ],
            <9 empty items>
*/
```
 
Yay! :confetti_ball: 没有冲突！
 
通过增加初始容量，可以很好的减少冲突，但会消耗 **`更多的内存`**  ，而且很可能许多桶都没被使用。
 
如果我们的 HashMap 能根据需要自动调整容量，这不是更好吗？这就是所谓的 **`rehash`**  （重新计算哈希值），我们将在下一节将实现它！
 
## 优化HashMap 的实现
 
如果 HashMap 的容量足够大，那就不会产生任何冲突，因此查找操作的时间复杂度为 O(1)  。然而，我们怎么知道容量多大才是足够呢，100？1000？还是一百万？
 
（从开始就）分配大量的内存（去建立数组）是不合理的。因此，我们能做的是根据装载因子动态地调整容量。这操作被称为 **`rehash`**  。
 
装载因子是用于衡量一个 HashMap 满的程度，可以通过存储键值对的数量除以 HashMap 的容量得到它。
 
根据这思路，我们将实现最终版的 HashMap：
 
#### 最佳的 HasnMap 实现
 
```js
class HashMap {
  constructor(initialCapacity = 16, loadFactor = 0.75) {
    this.buckets = new Array(initialCapacity);
    this.loadFactor = loadFactor;
    this.size = 0;
    this.collisions = 0;
    this.keys = [];
  }
  hash(key) {
    let hashValue = 0;
    const stringTypeKey = `${key}${typeof key}`;
    for (let index = 0; index < stringTypeKey.length; index++) {
      const charCode = stringTypeKey.charCodeAt(index);
      hashValue += charCode << (index * 8);
    }
    return hashValue;
  }
  _getBucketIndex(key) {
    const hashValue = this.hash(key);
    const bucketIndex = hashValue % this.buckets.length;
    return bucketIndex;
  }
  set(key, value) {
    const {bucketIndex, entryIndex} = this._getIndexes(key);
    if(entryIndex === undefined) {
      // initialize array and save key/value
      const keyIndex = this.keys.push({content: key}) - 1; // keep track of the key index
      this.buckets[bucketIndex] = this.buckets[bucketIndex] || [];
      this.buckets[bucketIndex].push({key, value, keyIndex});
      this.size++;
      // Optional: keep count of collisions
      if(this.buckets[bucketIndex].length > 1) { this.collisions++; }
    } else {
      // override existing value
      this.buckets[bucketIndex][entryIndex].value = value;
    }
    // check if a rehash is due
    if(this.loadFactor > 0 && this.getLoadFactor() > this.loadFactor) {
      this.rehash(this.buckets.length * 2);
    }
    return this;
  }
  get(key) {
    const {bucketIndex, entryIndex} = this._getIndexes(key);
    if(entryIndex === undefined) {
      return;
    }
    return this.buckets[bucketIndex][entryIndex].value;
  }
  has(key) {
    return !!this.get(key);
  }
  _getIndexes(key) {
    const bucketIndex = this._getBucketIndex(key);
    const values = this.buckets[bucketIndex] || [];
    for (let entryIndex = 0; entryIndex < values.length; entryIndex++) {
      const entry = values[entryIndex];
      if(entry.key === key) {
        return {bucketIndex, entryIndex};
      }
    }
    return {bucketIndex};
  }
  delete(key) {
    const {bucketIndex, entryIndex, keyIndex} = this._getIndexes(key);
    if(entryIndex === undefined) {
      return false;
    }
    this.buckets[bucketIndex].splice(entryIndex, 1);
    delete this.keys[keyIndex];
    this.size--;
    return true;
  }
  rehash(newCapacity) {
    const newMap = new HashMap(newCapacity);
    this.keys.forEach(key => {
      if(key) {
        newMap.set(key.content, this.get(key.content));
      }
    });
    // update bucket
    this.buckets = newMap.buckets;
    this.collisions = newMap.collisions;
    // Optional: both `keys` has the same content except that the new one doesn't have empty spaces from deletions
    this.keys = newMap.keys;
  }
  getLoadFactor() {
    return this.size / this.buckets.length;
  }
}
```
 
[完整代码][28] （译者注：其实`has`方法有问题，只是不影响阅读。）
 
注意第99行至第114行（即`rehash`函数），那里是 rehash 魔法发生的地方。我们创造了一个新的 HashMap，它拥有原来 HashMap两倍的容量。
 
测试一下上面的新实现吧：
 
```js
const assert = require('assert');
const hashMap = new HashMap();
assert.equal(hashMap.getLoadFactor(), 0);
hashMap.set('songs', 2);
hashMap.set('pets', 7);
hashMap.set('tests', 1);
hashMap.set('art', 8);
assert.equal(hashMap.getLoadFactor(), 4/16);
hashMap.set('Pineapple', 'Pen Pineapple Apple Pen');
hashMap.set('Despacito', 'Luis Fonsi');
hashMap.set('Bailando', 'Enrique Iglesias');
hashMap.set('Dura', 'Daddy Yankee');
hashMap.set('Lean On', 'Major Lazer');
hashMap.set('Hello', 'Adele');
hashMap.set('All About That Bass', 'Meghan Trainor');
hashMap.set('This Is What You Came For', 'Calvin Harris ');
assert.equal(hashMap.collisions, 2);
assert.equal(hashMap.getLoadFactor(), 0.75);
assert.equal(hashMap.buckets.length, 16);
hashMap.set('Wake Me Up', 'Avicii'); // <--- Trigger REHASH
assert.equal(hashMap.collisions, 0);
assert.equal(hashMap.getLoadFactor(), 0.40625);
assert.equal(hashMap.buckets.length, 32);
```
 
注意，在 HashMap 存储了12项之后，装载因子将超过0.75，因而触发 rehash，HashMap 容量加倍（从16到32）。同时，我们也能看到冲突从2降低为0。
 
这版本实现的 HashMap 能以很低的时间复杂度进行常见的操作，如：插入、查找、删除、编辑等。
 
小结一下，HashMap 的性能取决于：
 
 
* 哈希函数能根据不同的键输出不同的值。 
* HashMap 容量的大小。 
 
 
我们终于处理好了各种问题 :hammer:。有了不错的哈希函数，可以根据不同输入返回不同输出。同时，我们也有`rehash`函数根据需要动态地调整 HashMap的容量。这实在太好了！
 
## HashMap 中插入元素的时间复杂度
 
往一个 HashMap 插入元素需要两样东西：一个键与一个值。可以使用上文开发优化后的 HashMap 或内置的对象进行操作：
 
```js
function insert(object, key, value) {
  object[key] = value;
  return object;
}
const hash = {};
console.log(insert(hash, 'word', 1)); // => { word: 1 }
```
 
在新版的 JavaScript 中，你可以使用 Map。
 
```js
function insertMap(map, key, value) {
  map.set(key, value);
  return map;
}
const map = new Map();
console.log(insertMap(map, 'word', 1)); // Map { 'word' => 1 }
```
 
注意。我们将使用 Map 而不是普通的对象，这是由于 Map 的键可以是任何东西而对象的键只能是字符串或者数字。此外，Map 可以保持插入的顺序。
 
进一步说，`Map.set`只是将元素插入到数组（如上文`DecentHashMap.set`所示），类似于`Array.push`，因此可以说：
 
往 HashMap 中插入元素，时间复杂度是 O(1)  。如果需要 rehash，那么复杂度则是 O(n)  。
 
rehash 能将冲突可能性降至最低。rehash 操作时间复杂度是 O(n)  ，但不是每次插入操作都要执行，仅在需要时执行。
 
## HashMap 中查找或访问元素的时间复杂度
 
这是`HashMap.get`方法，我们通过往里面传递一个键来获取对应的值。让我们回顾一下`DecentHashMap.get`的实现：
 
```js
get（key）{
  const hashIndex = this .getIndex（key）;
  const values = this .array [hashIndex];
  for（let index = 0 ; index <values.length; index ++）{
    const entry = values [index];
    if（entry.key === key）{
      返回 entry.value
    }
  }
}
```
 
如果并未发生冲突，那么`values`只会有一个值，访问的时间复杂度是 O(1)  。但我们也知道，冲突总是会发生的。如果 HashMap 的初始容量太小或哈希函数设计糟糕，那么大多数元素访问的时间复杂度是 O(n)  。
 
HashMap 访问操作的时间复杂度平均是 O(1)  ，最坏情况是 O(n)  。
 
进阶提示：另一个（将访问操作的）时间复杂度从 O(n)  降至 O(log n)  的方法是使用 二叉搜索树  而不是数组进行底层存储。事实上，当存储的 [元素超过8 个][29] 时， [Java HashMap 的底层实现][30] 会从数组转为树。
 
## HashMap 中修改或删除元素的时间复杂度
 
修改(`HashMap.set`)或删除（`HashMap.delete`）键值对，分摊后的时间复杂度是 O(1)  。如果冲突很多，可能面对的就是最坏情况，复杂度为 O(n)  。然而伴随着 rehash 操作，可以大大减少最坏情况的发生的几率。
 
HashMap 修改或删除操作的时间复杂度平均是 O(1)  ，最坏情况是 O(n)  。
 
## HashMap 方法的时间复杂度
 
在下表中，小结了 HashMap（方法）的时间复杂度：
 
#### HashMap 方法的时间复杂度
 
| 操作方法 | 最坏情况 | 平均 | 备注 | 
|-|-|-|-|
| 访问或查找 (`HashMap.get`) | O(n) | O(1) | O(n)  是冲突极多的极端情况 | 
| 插入或修改 (`HashMap.set`) | O(n) | O(1) | O(n)  只发生在装载因子超过0.75，触发 rehash 时 | 
| 删除 (`HashMap.delete`) | O(n) | O(1) | O(n)  是冲突极多的极端情况 | 
 
 
## Sets
 
集合跟数组非常相像。它们的区别是集合中的元素是唯一的。
 
我们该如何实现一个集合呢（也就是没有重复项的数组）？可以使用数组实现，在插入新元素前先检查该元素是否存在。但检查是否存在的时间复杂度是 O(n)  。能对此进行优化吗？之前开发的 Map （插入操作）分摊后时间复杂度度才 O(1)  ！
 
## Set 的实现
 
可以使用 JavaScript 内置的 Set。然而通过自己实现它，可以更直观地了解它的时间复杂度。我们将使用上文优化后带有 rehash 功能的 HashMap 来实现它。
 
```js
const HashMap = require('../hash-maps/hash-map');
class MySet {
  constructor() {
    this.hashMap = new HashMap();
  }
  add(value) {
    this.hashMap.set(value);
  }
  has(value) {
    return this.hashMap.has(value);
  }
  get size() {
    return this.hashMap.size;
  }
  delete(value) {
    return this.hashMap.delete(value);
  }
  entries() {
    return this.hashMap.keys.reduce((acc, key) => {
      if(key !== undefined) {
        acc.push(key.content);
      }
      return acc
    }, []);
  }
}
```
 
（译者注：由于 HashMap 的`has`方法有问题，导致 Set 的`has`方法也有问题）
 
我们使用`HashMap.set`为集合不重复地添加元素。我们将待存储的值作为 HashMap的键，由于哈希函数会将键映射为唯一的索引，因而起到排重的效果。
 
检查一个元素是否已存在于集合中，可以使用`hashMap.has`方法，它的时间复杂度平均是 O(1)  。集合中绝大多数的方法分摊后时间复杂度为 O(1)  ，除了`entries`方法，它的事件复杂度是 O(n)  。
 
注意：使用 JavaScript 内置的集合时，它的`Set.has`方法时间复杂度是 O(n)  。这是由于它的使用了 List 作为内部实现，需要检查每一个元素。你可以在 [这][31] 查阅相关的细节。
 
下面有些例子，说明如何使用这个集合：
 
```js
const assert = require('assert');
// const set = new Set(); // Using the built-in
const set = new MySet(); // Using our own implementation
set.add('one');
set.add('uno');
set.add('one'); // should NOT add this one twice
assert.equal(set.has('one'), true);
assert.equal(set.has('dos'), false);
assert.equal(set.size, 2);
// assert.deepEqual(Array.from(set), ['one', 'uno']);
assert.equal(set.delete('one'), true);
assert.equal(set.delete('one'), false);
assert.equal(set.has('one'), false);
assert.equal(set.size, 1);
```
 
这个例子中，MySet 与 JavaScript 中内置的 Set 均可作为容器。
 
## Set 方法的时间复杂度
 
根据 HashMap 实现的的 Set，可以小结出的时间复杂度如下（与 HashMap 非常相似）：
 
#### Set 方法的时间复杂度
 
| 操作方法 | 最坏情况 | 平均 | 备注 | 
|-|-|-|-|
| 访问或查找 (`Set.has`) | O(n) | O(1) | O(n)  是冲突极多的极端情况 | 
| 插入或修改 (`Set.add`) | O(n) | O(1) | O(n)  只发生在装载因子超过0.75，触发 rehash 时 | 
| 删除 (`Set.delete`) | O(n) | O(1) | O(n)  是冲突极多的极端情况) | 
 
 
## Linked Lists
 
链表是一种一个节点链接到下一个节点的数据结构。
 
![][5]
 
链表是（本文）第一种不用数组（作为底层）实现的数据结构。我们使用节点来实现，节点存储了一个元素，并指向下一个节点（若没有下一个节点，则为空）。
 
```js
class Node {
  constructor(value) {
    this.value = value;
    this.next = null;
  }
}
```
 
当每个节点都指向它的下了一个节点时，我们就拥有了一条由若干节点组成链条，即 **`单向链表`**  。
 
## Singly Linked Lists
 
对于单向链表而言，我们只需关心每个节点都有指向下一个节点的引用。
 
从首个节点或称之为根节点开始构建（单向链表）。
 
```js
class LinkedList {
  constructor() {
    this.root = null;
  }
  // ...
}
```
 
每个链表都有四个基础操作：
 
 
* addLast：将一个元素添加至链表尾部。 
* removeLast：删除链表的最后一个元素。 
* addFirst：将一个元素添加到链表的首部。 
* removeFirst：删除链表的首个元素。 
 
 
#### 向链表末尾添加与删除一个元素
 
（对添加操作而言，）有两种情况。1）如果链表根节点不存在，那么将新节点设置为链表的根节点。2）若存在根节点，则必须不断查询下一个节点，直到链表的末尾，并将新节点添加到最后。
 
```js
addLast(value) { // similar Array.push
  const node = new Node(value);
  if(this.root) {
    let currentNode = this.root;
    while(currentNode && currentNode.next) {
      currentNode = currentNode.next;
    }
    currentNode.next = node;
  } else {
    this.root = node;
  }
}
```
 
上述代码的时间复杂度是多少呢？如果是作为根节点添加进链表，时间复杂度是 O(1)  ，然而寻找最后一个节点的时间复杂度是 O(n)  .。
 
删除末尾的节点与上述代码相差无几。
 
```js
removeLast() {
  let current = this.root;
  let target;
  if(current && current.next) {
    while(current && current.next && current.next.next) {
      current = current.next;
    }
    target = current.next;
    current.next = null;
  } else {
    this.root = null;
    target = current;
  }
  if(target) {
    return target.value;
  }
}
```
 
时间复杂度也是 O(n)  。这是由于我们必须依次往下，直到找到倒数第二个节点，并将它`next`的引用指向`null`。
 
#### 向链表开头添加与删除一个元素
 
往链表开头添加一个元素（的代码）如下所示：
 
```js
addFirst(value) {
  const node = new Node(value);
  node.next = this.first;
  this.first = node;
}
```
 
向链表的开头进行增删操作，所耗费的时间是恒定的，因为我们持有根元素的引用：
 
```js
removeFirst(value) {
  const target = this.first;
  this.first = target ? target.next : null;
  return target.value;
}
```
 
（译者注：作者原文`removeFirst`的代码放错了，上述代码是译者实现的）
 
如你所见，对链表的开头进行增删操作，时间复杂度永远是 O(1)  。
 
#### 从链表的任意位置删除元素
 
删除链表首尾的元素，可以使用`removeFirst`或`removeLast`。然而，如若移除的节点在链表的中间，则需要将待删除节点的前一个节点指向待删除节点的下一个节点，从而从链表中删除该节点：
 
```js
remove(index = 0) {
  if(index === 0) {
    return this.removeFirst();
  }
  let current;
  let target = this.first;
  for (let i = 0; target;  i++, current = target, target = target.next) {
    if(i === index) {
      if(!target.next) { // if it doesn't have next it means that it is the last
        return this.removeLast();
      }
      current.next = target.next;
      this.size--;
      return target.value;
    }
  }
}
```
 
（译者注：原文实现有点问题，译者稍作了点修改。`removeLast`的调用其实浪费了性能，但可读性上增加了，因而此处未作修改。）
 
注意，`index`是从0开始的：0是第一个节点，1是第二个，如此类推。
 
在链表任意一处删除节点，时间复杂度为 O(n)  .
 
#### 在链表中查找元素
 
在链表中查找一个元素与删除元素的代码差不多：
 
```js
contains(value) {
  for (let current = this.first, index = 0; current;  index++, current = current.next) {
    if(current.value === value) {
      return index;
    }
  }
}
```
 
这个方法查找链表中第一个与给定值相等的节点（的索引）。
 
在链表中查找一个元素，时间复杂度是 O(n) 
 
## 单向链表操作方法的时间复杂度
 
在下表中，小结了单向链表（方法）的时间复杂度：
 
| 操作方法 | 时间复杂度 | 注释 | 
|-|-|-|
| addFirst | O(1) | 将元素插入到链表的开头 | 
| addLast | O(n) | 将元素插入到链表的末尾 | 
| add | O(n) | 将元素插入到链表的任意地方 | 
| removeFirst | O(1) | 删除链表的首个元素 | 
| removeLast | O(n) | 删除链表最后一个元素 | 
| remove | O(n) | 删除链表中任意一个元素 | 
| contains | O(n) | 在链表中查找任意元素 | 
 
 
注意，当我们增删链表的最后一个元素时，该操作的时间复杂度是 O(n)  …
 
但只要持有最后一个节点的引用，可以从原来的 O(n)  ，降至与增删首个元素一致，变为 O(1)  ！
 
我们将在下一节实现这功能！
 
## Doubly Linked Lists
 
当我们有一串节点，每一个都有指向下一个节点的引用，也就是拥有了一个 **`单向链表`**  。而当一串节点，每一个既有指向下一个节点的引用，也有指向上一个节点的引用时，这串节点就是 **`双向链表`**  。
 
![][6]
 
双向链表的节点有两个引用（分别指向前一个和后一个节点），因此需要保持追踪首个与最后一个节点。
 
```js
class Node {
  constructor(value) {
    this.value = value;
    this.next = null;
    this.previous = null;
  }
}
class LinkedList {
  constructor() {
    this.first = null; // head/root element
    this.last = null; // last element of the list
    this.size = 0; // total number of elements in the list
  }
  // ...
}
```
 
（双向链表的 [完整代码][32] ）
 
#### 添加或删除链表的首个元素
 
由于持有首个节点的引用，因而添加或删除首个元素的操作是十分简单的：
 
```js
addFirst(value) {
  const node = new Node(value);
  node.next = this.first;
  if(this.first) {
    this.first.previous = node;
  } else {
    this.last = node;
  }
  this.first = node; // update head
  this.size++;
  return node;
}
```
 
（`LinkedList.prototype.addFirst`的 [完整代码][32]
 
注意，我们需要十分谨慎地更新节点的`previous`引用、双向链表的`size`与双向链表最后一个节点的引用。
 
```js
removeFirst() {
  const first = this.first;
  if(first) {
    this.first = first.next;
    if(this.first) {
      this.first.previous = null;
    }
    this.size--;
    return first.value;
  } else {
    this.last = null;
  }
}
```
 
（`LinkedList.prototype.removeFirst`的 [完整代码][32]
 
时间复杂度是什么呢？
 
无论是单向链表还是双向链表，添加与删除首个节点的操作耗费时间都是恒定的，时间复杂度为 O(1)  。
 
#### 添加或删除链表的最后一个元素
  从双向链表的末尾  添加或删除一个元素稍有点麻烦。当你查询单向链表（操作的时间复杂度）时，这两个操作都是 O(n)  ，这是由于需要遍历整条链表，直至找到最后一个元素。然而，双向链表持有最后一个节点的引用：
 
```js
addLast(value) {
  const node = new Node(value);
  if(this.first) {
    node.previous = this.last;
    this.last.next = node;
    this.last = node;
  } else {
    this.first = node;
    this.last = node;
  }
  this.size++;
  return node;
}
```
 
（`LinkedList.prototype.addLast`的 [完整代码][32] ）
 
同样，我们需要小心地更新引用与处理一些特殊情况，如链表中只有一个元素时。
 
```js
removeLast() {
  let current = this.first;
  let target;
  if(current && current.next) {
    target = this.last;
    current = target.previous;
    this.last = current;
    current.next = null;
  } else {
    this.first = null;
    this.last = null;
    target = current;
  }
  if(target) {
    this.size--;
    return target.value;
  }
}
```
 
（`LinkedList.prototype.removeLast`的 [完整代码][32] ）
 
使用了双向链表，我们不再需要遍历整个链表直至找到倒数第二个元素。可以直接使用`this.last.previous`来找到它，时间复杂度是 O(1)  。
 
下文将介绍队列相关的知识，本文中队列是使用两个数组实现的。可以改为使用双向链表实现队列，因为（双向链表）添加首个元素与删除最后一个元素时间复杂度都是 O(1)  。
 
#### 添加一个元素至链表任意一处
 
借助`addFirst`与`addLast`，可以实现将一个元素添加到链表任意一处，实现如下：
 
```js
add(value, index = 0) {
  if(index === 0) {
    return this.addFirst(value);
  }
  for (let current = this.first, i = 0; i <= this.size;  i++, current = (current && current.next)) {
    if(i === index) {
      if(i === this.size) { // if it doesn't have next it means that it is the last
        return this.addLast(value);
      }
      const newNode = new Node(value);
      newNode.previous = current.previous;
      newNode.next = current;
      current.previous.next = newNode;
      if(current.next) { current.next.previous = newNode; }
      this.size++;
      return newNode;
    }
  }
}
```
 
（`LinkedList.prototype.add`的 [完整代码][32] ）
 
如果添加元素的位置是在链表中间，我们就必须更新该元素前后节点的`next`与`previous`引用。
 
添加一个元素至链表任意一处的时间复杂度是 O(n)  .
 
## 双向链表方法的时间复杂度
 
双向链表每个方法的时间复杂度如下表：
 
| 操作方法 | 时间复杂度 | 注释 | 
|-|-|-|
| addFirst | O(1) | 将元素插入到链表的开头 | 
| addLast | O(1) | 将元素插入到链表的末尾 | 
| add | O(n) | 将元素插入到链表的任意地方 | 
| removeFirst | O(1) | 删除链表的首个元素 | 
| removeLast | O(1) | 删除链表最后一个元素 | 
| remove | O(n) | 删除链表中任意一个元素 | 
| contains | O(n) | 在链表中查找任意元素 | 
 
 
与单向链表相比，有了很大的改进（译者注：其实看场景，不要盲目认为双向链表一定比单向链表强）！（`addLast`与`removeLast`）操作时间复杂度从 O(n)  降至 O(1)  ，这是由于：
 
 
* 添加对前一个节点的引用。 
* 持有链表最后一个节点的引用。 
 
 
删除首个或最后一个节点可以在恒定时间内完成，然而删除中间的节点时间复杂度仍然是 O(n)  。
 
## Stacks
 
栈是一种越后被添加的元素，越先被弹出的数据结构。也就是后进先出（LIFO）.
 
![][7]
 
让我们从零开始实现一个栈！
 
```js
class Stack {
  constructor() {
    this.input = [];
  }
  push(element) {
    this.input.push(element);
    return this;
  }
  pop() {
    return this.input.pop();
  }
}
```
 
正如你看到的，如果使用内置的`Array.push`与`Array.pop`实现一个栈，那是十分简单的。两个方法的时间复杂度都是 O(1)  。
 
下面来看看栈的具体使用：
 
```js
const stack = new Stack();
stack.push('a');
stack.push('b');
stack.push('c');
stack.pop(); // c
stack.pop(); // b
stack.pop(); // a
```
 
最先被加入进去的元素 a 直到最后才被弹出。栈也可以通过链表来实现，对应方法的时间复杂度是一样的。
 
这就是栈的全部内容啦！
 
## Queues
 
队列是一种越先被添加的元素，越先被出列的数据结构。也就是先进先出（FIFO）。就如现实中排成一条队的人们一样，先排队的先被服务（也就是出列）。
 
![][8]
 
可以通过数组来实现一个队列，代码与栈的实现相类似。
 
## 通过数组实现队列
 
通过`Array.push`与`Array.shift`可以实现一个简单（译者注：即不是最优的实现方式）的队列：
 
```js
class Queue {
  constructor() {
    this.input = [];
  }
  add(element) {
    this.input.push(element);
  }
  remove() {
    return this.input.shift();
  }
}
```
 `Queue.add`与`Queue.remove`的时间复杂度是什么呢？
 
 
* `Queue.add`使用`Array.push`实现，可以在恒定时间内完成。这非常不错！  
* `Queue.remove`使用`Array.shift`实现，`Array.shift`耗时是线性的（即 O(n)  ）。我们可以减少`Queue.remove`的耗时吗？  
 
 
试想一下，如果只用`Array.push`与`Array.pop`能实现一个队列吗？
 
```js
class Queue {
  constructor() {
    this.input = [];
    this.output = [];
  }
  add(element) {
    this.input.push(element);
  }
  remove() {
    if(!this.output.length) {
      while(this.input.length) {
        this.output.push(this.input.pop());
      }
    }
    return this.output.pop();
  }
}
```
 
现在，我们使用两个而不是一个数组来实现一个队列。
 
```js
const queue = new Queue();
queue.add('a');
queue.add('b');
queue.remove() // a
queue.add('c');
queue.remove() // b
queue.remove() // c
```
 
当我们第一次执行出列操作时，`output`数组是空的，因此将`input`数组的内容反向添加到`output`中，此时`output`的值是`['b', 'a']`。然后再从`output`中弹出元素。正如你所看到的，通过这个技巧实现的队列，元素输出的顺序也是先进先出（FIFO）的。
 
那时间复杂度是什么呢？
 
如果`output`数组已经有元素了，那么出列操作就是恒定的 O(1)  。而当`output`需要被填充（即里面没有元素）时，时间复杂度变为 O(n)  。`output`被填充后，出列操作耗时再次变为恒定。因此分摊后是 O(1)  。
 
也可以通过链表来实现队列，相关操作耗时也是恒定的。下一节将带来具体的实现。
 
## 通过双向链表实现队列
 
如果希望队列有最好的性能，就需要通过双向链表而不是数组来实现（译者注：并非数组实现就完全不好，空间上双向链表就不占优势，还是具体问题具体分析）。
 
```js
const LinkedList = require('../linked-lists/linked-list');
class Queue {
  constructor() {
    this.input = new LinkedList();
  }
  add(element) {
    this.input.addFirst(element);
  }
  remove() {
    return this.input.removeLast();
  }
  get size() {
    return this.input.size;
  }
}
```
 
通过双向链表实现的队列，我们持有（双向链表中）首个与最后一个节点的引用，因此入列与出列的时间复杂度都是 O(1)  。这就是为遇到的问题选择合适数据结构的重要性 :muscle:。
 
## 总结
 
我们讨论了大部分线性的数据结构。可以看出，根据实现方法的不同，相同的数据结构也会有不同的时间复杂度。
 
以下是本文讨论内容的总结：
 
#### 时间复杂度
 
* = 运行时分摊
 
| 数据结构 | 插入 | 访问 | 查找 | 删除 | 备注 | 
|-|-|-|-|-|-|
| **`Array`** | O(n) | O(1) | O(n) | O(n) | 插入最后位置复杂度为 O(1)  。 | 
| (Hash) **`Map`** | O(1)* | O(1)* | O(1)* | O(1)* | 重新计算哈希会影响插入时间。 | 
| **`Map`** | O(log(n)) | - | O(log(n)) | O(log(n)) | 通过二叉搜索树实现 | 
| **`Set`**  （使用 HashMap） | O(1）* | - | O(1)* | O(1)* | 由 HashMap 实现 | 
| **`Set`**  (使用 List) | [O(n)][13] | - | [O(n)][14] ] | [O(n)][15] | 通过 List 实现 | 
| **`Set`**  (使用二叉搜索树) | O(log(n)) | - | O(log(n)) | O(log(n)) | 通过二叉搜索树实现 | 
| **`Linked List`**  (单向) | O(n) | - | O(n) | O(n) | 在起始位置添加或删除元素，复杂度为 O(1) | 
| **`Linked List`**  (双向） | O(n) | - | O(n) | O(n) | 在起始或结尾添加或删除元素，复杂度为 O(1)  。然而在其他位置是 O(n)  。 | 
| **`Stack`**  (由 Array 实现) | O(1) | - | - | O(1)] | 插入与删除都遵循与后进先出（LIFO） | 
| **`Queue`**  (简单地由 Array 实现) | O(n) | - | - | O(1) | 插入（Array.shift）操作的复杂度是 O(n) | 
| **`Queue`**  (由 Array 实现，但进行了改进) | O(1)* | - | - | O(1) | 插入操作的最差情况复杂度是 O(n)  。然而分摊后是 O(1) | 
| **`Queue`**  (由 List 实现) | O(1) | - | - | O(1) | 使用双向链表 | 
 
 
注意： **`二叉搜索树`**  与其他树结构、图结构，将在另一篇文章中讨论。
 


[9]: https://adrianmejia.com/blog/2018/04/04/how-you-can-change-the-world-learning-data-structures-algorithms-free-online-course-tutorial/
[10]: https://adrianmejia.com/blog/2018/04/05/most-popular-algorithms-time-complexity-every-programmer-should-know-free-online-tutorial-course/
[11]: https://adrianmejia.com/blog/2018/05/14/Data-Structures-for-Beginners-Graphs-Time-Complexity-tutorial/
[12]: https://adrianmejia.com/blog/2018/04/24/Analysis-of-Recursive-Algorithms/
[13]: https://www.ecma-international.org/ecma-262/6.0/#sec-set.prototype.add
[14]: https://www.ecma-international.org/ecma-262/6.0/#sec-set.prototype.has
[15]: https://www.ecma-international.org/ecma-262/6.0/#sec-set.prototype.delete
[16]: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/push
[17]: http://devdocs.io/javascript/global_objects/array/pop
[18]: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/shift
[19]: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/unshift
[20]: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/slice
[21]: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/splice
[22]: https://tc39.github.io/ecma262/#sec-array.prototype.push
[23]: https://tc39.github.io/ecma262/#sec-array.prototype.unshift
[24]: https://tc39.github.io/ecma262/#sec-array.prototype.splice
[25]: https://github.com/amejiarosario/algorithms.js/blob/master/lib/data-structures/hash-maps/hash-map-1.js
[26]: https://simple.wikipedia.org/wiki/ASCII
[27]: https://github.com/amejiarosario/algorithms.js/blob/master/lib/data-structures/hash-maps/hash-map-2.js
[28]: https://github.com/amejiarosario/algorithms.js/blob/master/lib/data-structures/hash-maps/hash-map.js
[29]: http://hg.openjdk.java.net/jdk9/jdk9/jdk/file/f08705540498/src/java.base/share/classes/java/util/HashMap.java#l257
[30]: http://hg.openjdk.java.net/jdk9/jdk9/jdk/file/f08705540498/src/java.base/share/classes/java/util/HashMap.java#l145
[31]: https://www.ecma-international.org/ecma-262/6.0/#sec-set.prototype.has
[32]: https://github.com/amejiarosario/algorithms.js/blob/master/lib/data-structures/linked-lists/linked-list.js
[33]: https://github.com/amejiarosario/algorithms.js/blob/master/lib/data-structures/linked-lists/linked-list.js
[34]: https://github.com/amejiarosario/algorithms.js/blob/master/lib/data-structures/linked-lists/linked-list.js
[35]: https://github.com/amejiarosario/algorithms.js/blob/master/lib/data-structures/linked-lists/linked-list.js
[36]: https://github.com/amejiarosario/algorithms.js/blob/master/lib/data-structures/linked-lists/linked-list.js
[37]: https://github.com/amejiarosario/algorithms.js/blob/master/lib/data-structures/linked-lists/linked-list.js
[38]: https://www.ecma-international.org/ecma-262/6.0/#sec-set.prototype.add
[39]: https://www.ecma-international.org/ecma-262/6.0/#sec-set.prototype.has
[40]: https://www.ecma-international.org/ecma-262/6.0/#sec-set.prototype.delete
[0]: ./img/zAfYN3B.jpg 
[1]: ./img/rY7rMbe.jpg 
[2]: ./img/eyaMriJ.jpg 
[3]: ./img/RrARRfR.jpg 
[4]: ./img/RrARRfR.jpg 
[5]: ./img/yUr6z2n.jpg 
[6]: ./img/UvyiMfV.jpg 
[7]: ./img/ymU36rm.jpg 
[8]: ./img/UzUbEzm.jpg 
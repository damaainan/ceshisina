## javascript 链表的实现

来源：[https://juejin.im/post/5a77d5895188257a836c0c0c](https://juejin.im/post/5a77d5895188257a836c0c0c)

时间 2018-02-07 14:09:12

 
一边学习前端，一边通过博客的形式自己总结一些东西，当然也希望帮助一些和我一样开始学前端的小伙伴。
 
如果出现错误，请在评论中指出，我也好自己纠正自己的错误
 
 

![][0]
```
author： thomaszhou

```
 
### 1-单项链表
 
链表定义：

```js
function LinkedList() {
		let Node = function(element) { // 辅助类，表示要添加到链表中的项
		  this.element = element;
		  this.next = null; //next属性是只想链表的下一个节点的指针
		};

		let length = 0,
			first = null;  // 头指针
		this.append = function(element) {}; // 向链表尾部添加一个新的项
		this.insert = function(element, position) {}; // 向指定位置插入一个新的项
		this.removeAt = function(position) {}; // 移除链表中的指定项
		this.remove = function(element) {}; // 移除值为element的项
		this.indexOf = function(element) {}; // 返回元素在链表中的索引，如果没有则返回-1
		this.isEmpty = function() {}; // 判断链表中是否为空
		this.size = function() {}; // 链表的长度
		this.clear = function() {}; // 清空链表
		this.print = function() {}; //输出链表的值
	}
```
 
#### append实现：向链表尾部添加一个新的项

```js
this.append = function(element) { //向链表尾部添加一个新的项
			let node = new Node(element),
				       current;
			if (head === null) { // 链表中的第一个节点
                head = node;
			} else {
			    current = head;
			    while (current.next) { // 循环链表，直到找到最后一项
			      current = current.next;
				}
				current.next = node; // 找到最后一项，将next赋值为node，建立连接
			}
			length += 1; //更新链表长度
		};
```
 
#### print实现：输出链表的值

```js
this.print = function() {
      let current = head;
		  for (let i = 0; i < length; i++) {
		    console.log(`第${i+1}个值:${current.element}`);
		    current = current.next;
			}
		};
```
 
#### insert实现：向指定位置插入一个新的项
 
向指定位置插入一个新的项，步骤如下：
 

* 先进行position的越界判断！！ 
* case1: 当position=0的时候，那就是直接将当前head指向的节点变成新建node的next指向，head指向新建的node上 
*  case2: 除case1外的情况，先遍历到如下图所示的情况，我们设置两个指针，每次遍历都是current将值赋给previous，然后current向后推进，直到current指向position的位置，previous指向position的前一个 
   

![][1]
 
* 然后将新建的node，插入其中，然后改变其中的指针指向，具体看代码 
*  最后将length加一 
   

![][2]
 
 

```js
this.insert = function(element, position) { // 向指定位置插入一个新的项
	  if (position >= 0 && position <= length) { // 检查越界值
	    let node = new Node(element),
        current = head, // 设置两指针
        previous;
        if (position === 0) { // 在第一个位置添加
          node.next = head;
          head = node;
		}else {
          for (let i = 0; i < position; i++) {
            // 使得current定在要插入的位置，previous定在要插入位置的前一个位置
            previous = current; 
            current = current.next;
          }
          // 将新项node插入进去
          node.next = current;
          previous.next = node;
		}
	  }else {
		return 0;
	  }
	  length += 1;
	};
```
 
对于函数参数含有position这类的位置参数，一定要注意越界判断
 
#### removeAt实现：移除链表中的指定位置项
 
removeAt的思想和前面的insert方法很相似，唯一的不同就是移除指定项时，改变指针指向的代码不同`previous.next = current.next;` ， 
 

* 具体思想如图：将current指向的项(其实也就是position指向的项)移除，那就将图中红色叉的指针指向取消，然后用新的代替 
*  然后将lenght减一 
   

![][3] 只有`previous.next = current.next;` 和insert方法不同，其他思想和做法都一样  
 

```js
this.removeaAt = function(position) { // 移除链表中的指定项
		      if (position >= 0 && position <= length) {
				let current = head,
					previous;
				if (position === 0) {
					head = head.next;
				}else {
				  for (let i = 0; i < position; i++) { //此处循环到current指向要移除的位置，同insert方法
				    previous = current;
				    current = current.next;
					}
					// 通过将previous的next变化到current.next,将指定项移除
				  previous.next = current.next;
				}
			  }else {
		        return 0;
			  }
			  length -= 1;
		    };
```
 
#### indexOf实现：返回元素在链表中的索引，如果没有则返回-1

```js
this.indexOf = function(element) { // 返回元素在链表中的索引，如果没有则返回-1
		  let current = head; //
		  for (let i = 0; i < length; i++) {
			if (current.element === element) {
			  return i;
			}
			current = current.next;
		  }
		  return -1;
		};
```
 
#### remove实现：移除值为element的项

```js
this.remove = function(element) { // 移除值为element的项
	// remove方法可以直接通过复用 this.indexOf(element) 和 this.removeAt(position) 方法实现
	let index = this.indexOf(element);
	return this.removeaAt(index);
};
```
 
#### isEmpty、size、clear实现

```js
this.isEmpty = function() { // 判断链表中是否为空
	return length === 0;
};

this.size = function() { // 链表的长度
	return length;
};

this.clear = function() { // 清空链表
	let current = head;
	for (let i = 0; i < length; i++) {
		this.removeAt(i);
	}
	length = 0;
	head = null;
};
```
 
#### 测试函数

```js
(function LinkedListTest() {
	  let linked = new LinkedList();
	  linked.append(2);
	  linked.append(3);
	  linked.append(4);
	  linked.print(); // 第1个值:2  第2个值:3  第3个值:4
	  
	  linked.insert(5, 1); // 位置从0开始
      linked.print(); // 第1个值:2   第2个值:5   第3个值:3   第4个值:4
	  linked.removeAt(1); // 相当于将上面插入的5再删除
	  linked.print(); // 第1个值:2   第2个值:3   第3个值:4
	  
	  console.log(linked.indexOf(3)); // 1
      console.log(linked.indexOf(10)); // -1
	  console.log(linked.size()); // 3
	  console.log(linked.isEmpty()); // false
	  linked.clear();
      console.log(linked.isEmpty()); // true
	})();
```
 
### 2-双向链表

```js
定义双向链表
function DoublyLinkedList() {
  let Node = function(element) {
    this.element = element;
    this.next = null;
    this.prev = null; // 指向前一项的指针
  };
  let length = 0,
    head = null,
    tail = null; // 尾指针

    //此处就是方法，都在下面详细说明
}
```
 
#### insert实现
 
相比较于单向链表，双向链表比较复杂，它有后驱指针next还有前驱指针prev，那插入元素就要考虑的情况也多了
 

* 区别一：我们多了一个prev，也多一个尾部指针tail（永远指向链表的尾部） 
 

#### insert思路：
 

*  case1: 插入时当position为0时要分两种情况 
 

* 链表为空：那就直接将head和tail都指向新建项node 
* 链表不为空：那就将新建项node的next指针指向head，然后将head的prev指向node，以后将head指向node。 
   

 
*  case2: 当position等于length时（从尾部插入）： 
 

* 其实尾部插入比较简单，直接就是将新建项node的prev指向tail指向的项，将tail直线给的项的next指向新建项node，最后将tail指向node 
   

 
*  case3: 当position从中间插入时 
 

*  这个有点难理解，我们标记1234步，按照顺序看代码，相信能理解其中的意思（绿色的表示要插入的项） 
     

![][4]
 
   

 
 

此处还有一个不一样的地方，就是多了一个函数  **`find`**  ，由于双向链表，我们时可以通过一个指针current来找到前驱结点和后驱结点，（单向链表只能访问后驱结点，所以才需要  **`previous指针`**  来作为前驱结点的指针）所以我们在这里取消了  **`previous指针`**  ，然后根据前面的学习，我们可以发现有一部分代码在插入和移除方法中都有，所以我们将这段代码抽离出来创建为  **`find方法`**  

```js
<script>
        this.find = function(position) { // 遍历到position位置
		  let current = head;
          for (let i = 0; i < position; i++) {
            current = current.next;
		  }
		  return current;
		}

		this.insert = function(element, position) { // 指定位置插入值
		  if (position >= 0 && position <= length) {
            let node = new Node(element);
            let current = head,
				previous = null;
			if (position === 0) { // case1: 从链表头部插入
			  if (!head) {
                // 链表为空
				head = node;
			    tail = node;
			  }else {
				// 链表不为空
                node.next = head;
                head.prev = node;
                head = node;
			  }
			}else if (position === length) { // case2: 从链表尾部插入
			  node.prev = tail;
			  tail.next = node;
			  tail = node;
			}else { // case3: 从链表的中间插入
			  current = this.find(position);
			  // 插入元素
              node.next = current;
              node.prev = current.prev;
              current.prev.next = node;
              current.prev = node;
			}
			length += 1;
		  }else {
			return 0;
		  }
		};
```
 
#### removeAt实现
 
思路同insert，分三个阶段进行移除项的操作，我重点说一下中间移除项的操作，有些地方是通过current指针和previous指针一起来遍历，然后进行操作，其实previous可以用current.prev来代替，因为这是双向链表，所以完全不需要多加一个previous指针。
 
 

![][5]
```js
/*
   removeAt思路解析：
   同insert，分三个阶段进行移除项的操作
   */
  this.removeAt = function(position) {
    if (position >= 0 && position < length) {
      let node = new Node(),
        current = head;
      if (position === 0) { // 移除第一项
        head = head.next;
        head.prev = null;
        if (length === 1) { // 链表只有一项
          tail = null;
        }
      }else if (position === length - 1) { // 移除最后一项
        tail = tail.prev;
        tail.next = null
      }else { // 移除中间的项
        current = this.find(position);
        current.prev.next = current.next;
        current.next.prev = current.prev;
      }
      length -= 1;
      return current.element; // 返回被移除的项
    }else {
      return null;
    }
  };
```
 
#### 其他的方法
 
关于其他的方法，我只列出部分，因为其实单向和双向的主要区别就在于有一个prev的前驱指针，还有一个尾指针tail，在其他的方法中，我们只需要注意这两个点就好，以下代码均有注释

```js
this.append = function(element) { // 尾部添加项
    let node = new Node(element),
      current = head;
    if (head === null) {
      head = node;
      tail = node;
    }else {
      // 这里是和单链表不同的地方，也就是添加。
      tail.next = node;
      node.prev = tail;
      tail = node;
    }
    length += 1;
  };
```

```js
this.print = function() { // 输出链表的值-同单向链表
    let current = head;
    for (let i = 0; i < length; i++) {
      console.log(`第${i+1}个值:${current.element}`);
      current = current.next;
    }
  };
  this.clear = function() { // 清空链表
    let current = head;
    for (let i = 0; i < length; i++) {
      this.removeAt(i);
    }
    length = 0;
    head = null;
    tail = null; // 此处需要将tail指针也赋值为null
  };

  this.size = function() { // 链表的长度-同单向链表
    return length;
  };
```
 


[0]: ../img/aM3ueyN.jpg
[1]: ../img/RjmyAn3.png
[2]: ../img/JNv6reY.png
[3]: ../img/QrYbUfv.png
[4]: ../img/RvUFzuV.png
[5]: ../img/iiiuiii.png
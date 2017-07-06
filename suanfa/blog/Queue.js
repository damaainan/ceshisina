     function Queue() {
        this.dataStore = [];
        this.enqueue = enqueue;
        this.dequeue = dequeue;
        this.front = front;
        this.back = back;
        this.toString = toString;
        this.empty = empty;
        this.count = count;
    }
    //enqueue() 方法向队尾添加一个元素:
    function enqueue(element) {
        this
            .dataStore
            .push(element);
    }
    // dequeue() 方法删除队首的元素:
    function dequeue() {
        return this
            .dataStore
            .shift();
    }
    
    // front() 读取队首元素：
    function front() {
        return this.dataStore[0];
    }
    
    // back 读取队尾的元素：
    function back() {
        return this.dataStore[this.dataStore.length - 1];
    }
    
    // toString() 显示队列内的所有元素
    function toString() {
        var retStr = "";
        for (var i = 0; i < this.dataStore.length; ++i) {
            retStr += this.dataStore[i] + "\n";
        }
      return retStr;
    }
    //判断队列是否为空:
      function empty() {
       if (this.dataStore.length == 0) {
        return true;
      } else {
          return false;
      }
    }
    
    //显示元素个数
    function count() {
        return this.dataStore.length;
    }

    module.exports = Queue;
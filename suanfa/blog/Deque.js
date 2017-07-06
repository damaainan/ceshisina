    function Deque() {
      this.dataStore = [];
      this.enterFrontQueue = enterFrontQueue;
      this.enterBackQueue = enterBackQueue;
      this.delFrontQueue = delFrontQueue;
      this.delBackQueue = delBackQueue;
      this.front = front;
      this.back = back;
      this.toString = toString;
      this.empty = empty;
      this.count = count;
    }
    
    // enterFrontQueue 向队列头部添加元素
    function enterFrontQueue(element) {
        this.dataStore.unshift(element);
    }
    
    // enterBackQueue 向队列尾部添加元素
    function enterBackQueue(element) {
      return  this.dataStore.push(element);
    }
    
    // delFrontQueue 从队列头部删除元素
    
    function delFrontQueue() {
        return this.dataStore.shift();
    }
    
    // delBackQueue 从队列尾部删除元素
    function delBackQueue() {
        this.dataStore.pop();
    }
    // front() 读取队首元素：
    function front() {
        return this.dataStore[0];
    }
    // back() 读取队尾元素：
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


    module.exports = Deque;
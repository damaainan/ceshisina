    function Stack(){
        this.dataStore = []; //使用数组保存栈内元素
        this.top=0; //记录栈顶位置，初始化为0
        this.push = push; // 向栈中压入一个新元素
        this.pop = pop; // 从栈顶推出一个元素
        this.peek = peek; // 返回栈顶元素
        this.clear=clear; //清除栈
        this.length=length;//返回栈的元素个数
    }
    //当向栈中压入一个新元素时，需要将其保存在数组中变量 top 所对 应的位置，
    //然后将 top 值加 1，让其指向数组中下一个空位置。
    function push(element){
        this.dataStore[this.top++]=element;
    }
    
    //它返回栈顶元素，同时将变量 top 的值减 1
    function pop(){
        return this.dataStore[--this.top];
    }
    //返回数组的第 top-1 个位置的元素，即栈顶元素:
    function peek(){
        return this.dataStore[this.top-1];
    }
    //通过返回变量 top 值的方式返回栈 内的元素个数
    function length(){
        return this.top;
    }
    //清空一个栈
    function clear(){
        this.top = 0;
    }

    module.exports = Stack;



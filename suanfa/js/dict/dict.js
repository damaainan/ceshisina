//字典的定义有误  应该用对象实现 对象可根据属性排序
function Dictionary() {
    this.add = add;
    this.datastore = [];
    this.find = find;
    this.remove = remove;
    this.showAll = showAll;
}

function add(key, value) {
    this.datastore[key] = value;
}

function find(key) {
    return this.datastore[key];
}

function remove(key) {
    delete this.datastore[key];
}

function showAll() {
    for (var key in Object.keys(this.datastore)) {
        console.log(key + " -> " + this.datastore[key]);
    }
}
module.exports = Dictionary;

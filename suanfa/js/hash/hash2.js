function HashTable2() {
    this.table = new Array(137);
    this.betterHash = betterHash;
    this.showDistro = showDistro;
    this.put = put;
    //this.get = get;
}

function put(data) {
    var pos = this.betterHash(data);
    this.table[pos] = data;
}

function betterHash(string) {
    const H = 31;
    var total = 0;
    for (var i = 0; i < string.length; ++i) {
        total += H * total + string.charCodeAt(i);
    }
    total = total % this.table.length;
    if (total < 0) {
        total += this.table.length - 1;
    }
    console.log("Hash value: " + string + " -> " + total);
    return parseInt(total);
}

function showDistro() {
    var n = 0;
    for (var i = 0; i < this.table.length; ++i) {
        if (this.table[i] !== undefined) {
            console.log(i + ": " + this.table[i]);
        }
    }
}
module.exports = HashTable2;

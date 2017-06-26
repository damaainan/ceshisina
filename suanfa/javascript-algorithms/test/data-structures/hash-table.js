var hash = require('../../src/data-structures/hash-table');
var hashTable = new hash.Hashtable();

hashTable.put(10, 'value');
hashTable.put('key', 10);

console.log(hashTable.get(10)); // 'value'
console.log(hashTable.get('key')); // 10

hashTable.remove(10);
hashTable.remove('key');

console.log(hashTable.get(10)); // undefined
console.log(hashTable.get('key')); // undefined
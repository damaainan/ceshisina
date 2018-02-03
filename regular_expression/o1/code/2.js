
var result = "hello".replace(/^|$/g, '#');

console.log(result); // "#hello#"


var result = "I\nlove\njavascript".replace(/^|$/gm, '#');

console.log(result);

/*

#I#

#love#

#javascript#

*/


var result = "[JS] Lesson_01.mp4".replace(/\b/g, '#');

console.log(result); // "[#JS#] #Lesson_01#.#mp4#"


var result = "[JS] Lesson_01.mp4".replace(/\B/g, '#');

console.log(result); // "#[J#S]# L#e#s#s#o#n#_#0#1.m#p#4"


var result = "hello".replace(/(?=l)/g, '#');

console.log(result); // "he#l#lo"


var result = "hello".replace(/(?!l)/g, '#');

console.log(result); // "#h#ell#o#"


var result = /^^hello$$$/.test("hello");

console.log(result); // true


var result = /(?=he)^^he(?=\w)llo$\b\b$/.test("hello");

console.log(result); // true


var result = "12345678".replace(/(?=\d{3}$)/g, ',')

console.log(result); // "12345,678"


var result = "12345678".replace(/(?=(\d{3})+$)/g, ',')

console.log(result); // "12,345,678"


var result = "123456789".replace(/(?=(\d{3})+$)/g, ',')

console.log(result); // ",123,456,789"


var string1 = "12345678",

string2 = "123456789";

reg = /(?!^)(?=(\d{3})+$)/g;

var result = string1.replace(reg, ',')

console.log(result); // "12,345,678"

result = string2.replace(reg, ',');

console.log(result); // "123,456,789"


var string = "12345678 123456789",

reg = /(?!\b)(?=(\d{3})+\b)/g;

var result = string.replace(reg, ',')

console.log(result); // "12,345,678 123,456,789"


var reg = /((?=.*[0-9])(?=.*[a-z])|(?=.*[0-9])(?=.*[A-Z])|(?=.*[a-z])(?=.*[A-Z]))^[0-9A-Za-z]{6,12}$/;

console.log( reg.test("1234567") ); // false 全是数字

console.log( reg.test("abcdef") ); // false 全是小写字母

console.log( reg.test("ABCDEFGH") ); // false 全是大写字母

console.log( reg.test("ab23C") ); // false 不足6位

console.log( reg.test("ABCDEF234") ); // true 大写字母和数字

console.log( reg.test("abcdEF234") ); // true 三者都有


var reg = /(?!^[0-9]{6,12}$)(?!^[a-z]{6,12}$)(?!^[A-Z]{6,12}$)^[0-9A-Za-z]{6,12}$/;

console.log( reg.test("1234567") ); // false 全是数字

console.log( reg.test("abcdef") ); // false 全是小写字母

console.log( reg.test("ABCDEFGH") ); // false 全是大写字母

console.log( reg.test("ab23C") ); // false 不足6位

console.log( reg.test("ABCDEF234") ); // true 大写字母和数字

console.log( reg.test("abcdEF234") ); // true 三者都有


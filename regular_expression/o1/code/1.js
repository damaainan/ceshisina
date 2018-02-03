
var regex = /hello/;
console.log( regex.test("hello") ); // true


var regex = /ab{2,5}c/g;
var string = "abc abbc abbbc abbbbc abbbbbc abbbbbbc";
console.log( string.match(regex) ); // ["abbc", "abbbc", "abbbbc", "abbbbbc"]


var regex = /a[123]b/g;
var string = "a0b a1b a2b a3b a4b";
console.log( string.match(regex) ); // ["a1b", "a2b", "a3b"]


var regex = /\d{2,5}/g;
var string = "123 1234 12345 123456";
console.log( string.match(regex) ); // ["123", "1234", "12345", "12345"]


var regex = /\d{2,5}?/g;
var string = "123 1234 12345 123456";
console.log( string.match(regex) ); // ["12", "12", "34", "12", "34", "12", "34", "56"]


var regex = /good|nice/g;
var string = "good idea, nice try.";
console.log( string.match(regex) ); // ["good", "nice"]


var regex = /good|goodbye/g;
var string = "goodbye";
console.log( string.match(regex) ); // ["good"]


var regex = /goodbye|good/g;
var string = "goodbye";
console.log( string.match(regex) ); // ["goodbye"]


var regex = /#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})/g;
var string = "#ffbbad #Fc01DF #FFF #ffE";
console.log( string.match(regex) ); // ["#ffbbad", "#Fc01DF", "#FFF", "#ffE"]


var regex = /^([01][0-9]|[2][0-3]):[0-5][0-9]$/;
console.log( regex.test("23:59") ); // true
console.log( regex.test("02:07") ); // true


var regex = /^(0?[0-9]|1[0-9]|[2][0-3]):(0?[0-9]|[1-5][0-9])$/;
console.log( regex.test("23:59") ); // true
console.log( regex.test("02:07") ); // true
console.log( regex.test("7:9") ); // true


var regex = /^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/;
console.log( regex.test("2017-06-10") ); // true


var regex = /^[a-zA-Z]:\\([^\\:*<>|"?\r\n/]+\\)*([^\\:*<>|"?\r\n/]+)?$/;
console.log( regex.test("F:\\study\\javascript\\regex\\regular expression.pdf") ); // true
console.log( regex.test("F:\\study\\javascript\\regex\\") ); // true
console.log( regex.test("F:\\study\\javascript") ); // true
console.log( regex.test("F:\\") ); // true


var regex = /id=".*"/
var string = '<div id="container" class="main"></div>';
console.log(string.match(regex)[0]); // id="container" class="main"


var regex = /id=".*?"/
var string = '<div id="container" class="main"></div>';
console.log(string.match(regex)[0]); // id="container"


var regex = /id="[^"]*"/
var string = '<div id="container" class="main"></div>';
console.log(string.match(regex)[0]); // id="container"



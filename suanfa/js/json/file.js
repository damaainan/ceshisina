var fs = require('fs');

var arr = fs.readFileSync("ti.txt", "utf-8").split("\n");
var ques, str, jarr = [];
for (var i = 0; i < arr.length; i += 2) {
    str = '';
    ques = arr[i].trim().split('.')[1];
    sarr = arr[i + 1].trim().split(/[A-C]\./);
    str = '{"question":"' + ques + '","subject":[{"A":"' + sarr[1] + '","B":"' + sarr[2] + '","C":"' + sarr[3] + '"}],"answer":""}';
    jarr.push(str);
}
var jsdata = "[" + jarr.join(',') + "]";
fs.writeFile("jsdata.json", jsdata);

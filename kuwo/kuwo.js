var cheerio = require("cheerio");
var iconv = require("iconv-lite");
var html2markdown = require('html2markdown');

function loadPage(url) {
    var http = require('http');

    var pm = new Promise(function(resolve, reject) {
        http.get(url, function(res) {
            var html = '';
            res.on('data', function(d) {
                html += d.toString();

            });
            res.on('end', function() {
                resolve(html);
            });
        }).on('error', function(e) {
            reject(e);
        });
    });
    return pm;
}
loadPage('http://www.baidu.com').then(function(d) {
    var $ = cheerio.load(d);
    var str = $("#wrapper").html();
    str = iconv.decode(str, 'utf-8');
    // str=html2markdown(str)
    console.log(str); //编码错误 需要转码
});

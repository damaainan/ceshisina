// ## map妙用

// 题目一：

// > 给定一个字符串，写一个函数，查找出该字符串中每个字符出现的次数，要求区分大小
// 写，且时间复杂度为O(n)。

    var str = 'safaAuasfAJIFjHDWEFHDaAueUEWda';
    var results = {};
    var rs = str.split('');
    
    rs.forEach(function(al){
        if(results[al] === undefined){
            results[al] = 1;
        }else{
            results[al]++;
        }
    })
    
    var keys = Object.keys(results);
    for(var i = 0; i < keys.length; i++){
        console.log(keys[i] + ' : ' + results[keys[i]]);
    }


//     题目二：

// > 在一个字符串中找到第一个只出现一次的字符。如输入abaccdefbf，则输出d。

    var str = 'abaccdefbf';
    var results = {};
    var rs = str.split('');
    
    rs.forEach(function(al){
        if(results[al] === undefined){
            results[al] = 1;
        }else{
            results[al]++;
        }
    })
    
    var keys = Object.keys(results);
    for(var i = 0; i < keys.length; i++){
        if(results[keys[i]] === 1){
            console.log(keys[i]);
            break;
        }
    }

// 题目三：

// > 最近在坛子里的问题，其实活用map，很容易就解决了，问题在这:https://segmentfault.com/q/1010000004891，就是json格式重整。我的解答如下：

    var json1 = { ... };
    var jmap = {};
    var result = [];
    
    json1.forEach(function(al){
        var key = al.menuDate + '_' + al.dinnerTime;
        if(typeof jmap[key] === 'undefined'){
            jmap[key] = [];
        }
        jmap[key].push(al);
    })
    
    var keys = Object.keys(jmap);
    for(var i = 0; i < keys.length; i++){
        var rs = keys[i].split('_');
        result.push({menuDate:rs[0],dinnerTime:rs[1],value:jmap[keys[i]]});
    }
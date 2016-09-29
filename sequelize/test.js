var co = require("co");
var Se = require("./base.js");


var se = new Se();
var ma = se.ma;
var dat = ma.build({
    'type': 1,
    'czid': 222,
    'userid': 333,
    'createtime': '2016-09-28 14:25:25'
});
co(function*() {
    // var mas = yield ma.findAll({
    //     where: {
    //         id: [1, 2, 3]
    //     }
    // });
    var mas = yield ma.findAll();
    var rt, maxid = 0;
    mas.forEach(function(mm) {
        console.log(mm.get({
            'plain': true
        }));
        rt = mm.get({
            'plain': true
        });
        if (rt.id < 5) {
            maxid = rt.id;
        }
    });
    console.log(maxid);
    // console.log(mas);
    if (maxid < 6) {
        var rst = yield dat.save();
        console.log(rst.get({
            'plain': true
        }));
    }

    mas = yield ma.findById(1);
    console.log(mas.get({
        'plain': true
    }));
});

var Sequelize = require("sequelize");
var co = require("co");

var sequelize = new Sequelize('test', 'root', '', {
    host: 'localhost',
    dialect: 'mysql',

});
var ma = sequelize.define('collect', {
    type: {
        type: Sequelize.INTEGER
    },
    czid: {
        type: Sequelize.INTEGER
    },
    userid: {
        type: Sequelize.INTEGER
    },
    createtime: {
        type: Sequelize.DATE
    }
}, {
    tableName: 'collect',
    'createdAt': false,

    // 将updatedAt字段改个名
    'updatedAt': false
});


var dat = ma.build({
    'type': 1,
    'czid': 222,
    'userid': 333,
    'createtime': '2016-09-28 14:25:25'
});
co(function*() {
    var mas = yield ma.findAll({
        where: {
            id: [1, 2, 3]
        }
    });

    mas.forEach(function(mm) {
        console.log(mm.get({
            'plain': true
        }));
    });
    // console.log(mas);

    // var rt = yield dat.save();
    // console.log(rt.get({
    //     'plain': true
    // }));

    // var mas = yield ma.findById(1);
    // console.log(mas.get({
    // 'plain': true
    // }));
});

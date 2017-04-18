var Sequelize = require("sequelize");
var fs = require('fs');
var archiver = require('archiver');

var sequelize = new Sequelize('test', 'root', '', {
    host: 'localhost',
    dialect: 'mysql',

});

// Or you can simply use a connection uri
// sequelize
//   .authenticate()
//   .then(function(err) {
//     console.log('Connection has been established successfully.');
//   })
//   .catch(function (err) {
//     console.log('Unable to connect to the database:', err);
//   });

var ma = sequelize.define('wish_material', {
    mTypeId: {
        type: Sequelize.INTEGER
    }
}, {
    tableName: 'wish_material'
});
// console.log(sequelize.models.wish_material);
ma.findAll({
    where: {
        mTypeId: [2, 31]
    },
    attributes: ["id", "code"]
}).then(function(rst) {
    // console.log(JSON.stringify(rst));
    var datas = JSON.stringify(rst);
    datas = JSON.parse(datas);
    for (var i = 0, len = datas.length; i < len; i++) {
        // console.log(datas[i]['id']+"\r\n");
        // console.log(datas[i]['code']+"\r\n");
        zipA(datas[i].id, datas[i].code);
    }
});

function zipA(id, code) {
    var isDir = fs.existsSync(code); //判断文件夹是否存在
    if (isDir === false) {
        return;
    }
    var output = fs.createWriteStream("./zip/" + id + '.zip');
    var archive = archiver('zip');

    archive.on('error', function(err) {
        throw err;
    });

    archive.pipe(output);
    // archive.bulk([{
    //     src: ['祝福/端午节-改/**'],dest:['']// bulk 原文件路径名压缩
    // }]);
    archive.directory(code, ""); //directort 可以设置保存路径
    archive.finalize();

}

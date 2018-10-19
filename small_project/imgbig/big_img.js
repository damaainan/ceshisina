// bigimg.js 点击放大图片

//  todo
//  1. 根据图片实际大小 决定宽高


$("img").click(function() {
    bigImg(this);
})

function addImgClick() {
    $("#make_img_big img").on("click", function() {
        console.log('点击大图不缩小');
        return false;
    })
    console.log('点击空白缩小');
    $("#make_img_big").on("click", function() {
        smallImg();
    })
}

function smallImg() {
    var img = $("#make_img_big img").attr("src");
    if (img != undefined) {
        $("#make_img_big").remove();
    }
}

function bigImg(obj) {
    var check = $(obj).parent('div').attr("id");
    if (check == "make_img_big") {
        return false; //阻断
    }
    var bigimg = $("#make_img_big img").attr("src");
    // 新建 div 铺满页面 展示图片
    // 点击其他部分 消失
    var src = $(obj).attr("src");
    console.log($(obj).attr("src"));
    var div = "<div id='make_img_big' style='width:100%;position:fixed;top:0;background:gray;overflow: scroll;max-height:100%'><img style='margin:20px 3%;width:94%;'></div>";
    $("body").append(div);
    $("#make_img_big img").attr("src", src);
    addImgClick();

}
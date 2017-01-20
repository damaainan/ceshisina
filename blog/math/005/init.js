function Plot() {  
    //此处封装Canvas API  
    // return obj;
}

/**
 * 初始化函数
 * 重写canvas 原生方法，实现连贯操作
 */
// function init(){

// }
Plot.prototype ={
    // log:function(){
    //     console.log(ctx);
    //     return ctx;
    // },
    
    init:function(){//怎样实现把 canvas 对象传递给 this
        // console.log(ctx);
        for (var key in ctx) {
            this[key]=ctx[key];
        }
         // this.call(this, ctx); 
        // this = ctx;
        return this;
    },
    setStrokeStyle:function(color){
        this.strokeStyle=color;
        return this;
    },
    setFillStyle:function(color){
        this.fillStyle=color;
        return this;
    },
    //.setShadowColor('#CCCCCC')  
    shadowColor:function(color){
        this.shadowColor=color;
        return this;
    },
        //     .setShadowBlur(20)  
    
        //     .setShadowOffsetX(10)  
        //     .setShadowOffsetY(10)  
        //     //直线  
        //     .setLineCap("round")  
        //     .setLineJoin("round")  
        //     .setLineWidth(3)  
        //     .setMiterLimit(10)  
        //     //文字  
        //     .setFont("normal normal normal 18px arial")  
        //     .setTextAlign("left")  
        //     .setTextBaseline("alphabetic")  
        //     .setGlobalCompositeOperation("source-over")  
        //     .setGlobalAlpha(1.0)  
        //     .save();  
};


/** 
* @usage   初始化环境 
* @author  mw 
* @date    2015年11月27日  星期五  08:41:41  
* @param 
* @return 
* 
*/  
var plot = new Plot();
plot.init().setStrokeStyle("dd").setFillStyle("sss");
console.log(2222);
console.log(plot);
// plot.log();
    function setPreference() {  
        // plot.init()  
        //     //颜色  
        //     .setStrokeStyle("black")  
        //     .setFillStyle('#666666')  
        //     //阴影  
        //     .setShadowColor('#CCCCCC')  
        //     .setShadowBlur(20)  
        //     .setShadowOffsetX(10)  
        //     .setShadowOffsetY(10)  
        //     //直线  
        //     .setLineCap("round")  
        //     .setLineJoin("round")  
        //     .setLineWidth(3)  
        //     .setMiterLimit(10)  
        //     //文字  
        //     .setFont("normal normal normal 18px arial")  
        //     .setTextAlign("left")  
        //     .setTextBaseline("alphabetic")  
        //     .setGlobalCompositeOperation("source-over")  
        //     .setGlobalAlpha(1.0)  
        //     .save();  
    }

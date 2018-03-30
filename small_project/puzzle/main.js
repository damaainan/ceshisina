(function() {
    function puzzle() {
        this.unit=100;  //block width
        this.place = [ //坐标位置
            [0, 0],
            [1, 0],
            [2, 0],
            [0, 1],
            [1, 1],
            [2, 1],
            [0, 2],
            [1, 2],
            [2, 2]
        ];
        this.bgimg=[ //全部图片路径
            "https://lc-yee5xnhu.cn-n1.lcfile.com/78cb08ed395dec020a3d.jpg",
            "https://lc-yee5xnhu.cn-n1.lcfile.com/29ad0c993bc331bcc990.jpg",
            "https://lc-yee5xnhu.cn-n1.lcfile.com/163f3fa7d694d59c2a73.jpg",
            "https://lc-yee5xnhu.cn-n1.lcfile.com/363b7e3b7be6a9b8e5e5.jpg",
            "https://lc-yee5xnhu.cn-n1.lcfile.com/f2758e2f348db54ac450.jpg"
        ];
        this.block=document.getElementsByClassName("block");//拼图块
        this.order = document.getElementsByClassName("order"); //数字标识
        this.sucessCover=document.getElementById("success");//游戏结束覆盖
        this.stepCount=document.getElementById("step");     //步数
        this.succStep= document.getElementById("suc_step"); //成功步数
        this.btn={
            changeImg:document.getElementById("change"), //-切换图片
            reset:document.getElementById("reset"),      //-切换图片
            showNum:document.getElementById("showNum"), //-显示序号
            prompt:document.getElementById("prompt"),   //-提示
            agian:document.getElementById("successB")   //再来一次按钮
        }
        this.img_ = 0;          //当前图片序号
        this.step=0;            //记录步数
        this.nowOrder=roa([0, 1, 2, 3, 4, 5, 6, 7, 8]);       //当前块排序
        this.purposeOder=[0, 1, 2, 3, 4, 5, 6, 7, 8];         //目标排序
        this.distanceCount=0;
        this.isShowOrder='显示序号';
        //状态空间搜索
        this.Open=[];
        this.Closed=[];
        //初始化
        this.blockInit();
        this.eventBind();
    }
    //位置初始化
    puzzle.prototype.blockInit=function(){
        var _this=this;
        Array.prototype.forEach.call(this.block,function(ele,i){
            let temp=_this.getCoordinate(_this.nowOrder[i]);
            ele.style.left = temp.x * 100 + "px";
            ele.style.top  = temp.y * 100 + "px";
        })
        _this.stepCount.innerHTML = _this.step;
    }
    //获取i位置坐标（@序号->坐标）
    puzzle.prototype.getCoordinate=function(index){
        return {
            x:this.place[index][0],
            y:this.place[index][1],
        }
    }
    //距离判断(@位置序号->距离)
    puzzle.prototype.distance=function(a,b){
        let aa = this.getCoordinate(a);
        let bb = this.getCoordinate(b);
        return Math.abs(aa.x - bb.x) + Math.abs(aa.y - bb.y);
    }
    //当前状态总距离
    puzzle.prototype.totalDis=function(arr){
        var _this=this;
        var temp=0;
        arr.forEach(function(ele,index){
            temp+=_this.distance(ele,index);
        })
        return temp;
    }
    //事件绑定
    puzzle.prototype.eventBind=function(){
        var _this = this;
        //block
        Array.prototype.forEach.call(this.block,function(ele,i){
            ele.onclick=function(){
                if(_this.distance(_this.nowOrder[i],_this.nowOrder[8])==1){ //判断是否与第八个相邻
                    var temp=_this.nowOrder[8];
                    _this.nowOrder[8]=_this.nowOrder[i];
                    _this.nowOrder[i]=temp;
                    //统计步数
                    _this.step++;
                    _this.blockInit();
                    //剩余距离
                    _this.distanceCount=_this.totalDis(_this.nowOrder);
                    //判断是否完成
                    //console.log(_this.distanceCount);
                    if(_this.distanceCount==0){
                        _this.successShow();
                    }
                }
            }
        })
        //重置&再来一次
        this.btn.reset.onclick = this.btn.agian.onclick=function() {
            _this.nowOrder=roa([0, 1, 2, 3, 4, 5, 6, 7, 8]);
            _this.step=0;
            _this.blockInit();
            _this.eventBind();
            _this.sucessCover.style.display = "none";
            _this.block[8].style.display = "none";
        }
        //显示隐藏数字
        this.btn.showNum.onclick = function() {
            if(_this.isShowOrder=='显示序号'){
                Array.prototype.forEach.call(_this.order,function(ele,i){
                    ele.style.display = "block";
                })
                _this.isShowOrder='隐藏序号';
            }else{
                Array.prototype.forEach.call(_this.order,function(ele,i){
                    ele.style.display = "none";
                })
                _this.isShowOrder='显示序号';
            }
            _this.btn.showNum.innerHTML=_this.isShowOrder;
        }
        //换图
        this.btn.changeImg.onclick = function() {
            if (_this.img_ == 4) _this.img_ = 0;
            else _this.img_++;
            Array.prototype.forEach.call(_this.block,function(ele,i){
                ele.style.backgroundImage = "url(" + _this.bgimg[_this.img_] + ")";
            })
        }
        //A*
        this.btn.prompt.onclick = function(){
            _this.searchA();
        }
    }


    //状态空间搜索
    puzzle.prototype.searchA=function(){
        var _this=this;
        var n=0;
        var lastState;
        var nowAllNode=[];
        var isSuccess=false;
        //估价函数
        var hx=_this.totalDis(_this.nowOrder)+_this.step;
        _this.Open.push({
            h:hx,
            state:_this.nowOrder
        });
        nowAllNode=nowAllNode.concat(_this.Open);
        while (_this.Open.length!==0) {
            var temp;    //当前节点
            var i=0;
            temp=deepCopy(_this.Open[0]);
            _this.Open.forEach(function(el,index){
                if(el.h<temp.h){
                    temp=deepCopy(el);
                    i=index;
                }
                if(_this.Open.length==index+1){
                    _this.Open.splice(i,1);
                }
            })
            _this.Closed.push(temp);
            //是否为目标节点
            if(JSON.stringify(temp.state) == JSON.stringify(_this.purposeOder)){
                alert("成功只差一步之遥，O(∩_∩)O哈哈~");
                isSuccess=true;
                break;
            }
            nowAllNode.push(temp);
            if(nowAllNode.lenght>2){
                splice(0, 1);
            }
            _this.Open=[];
            //扩展节点
            temp.state.forEach(function(el,index){
                var nowA=deepCopy(temp.state);
                if(_this.distance(el,temp.state[8])==1){
                    let s=el;
                    nowA[index]=nowA[8];
                    nowA[8]=s;
                    var kz={
                        h:_this.step+_this.totalDis(nowA),
                        state:nowA
                    }
                    var m=true;
                    for(var i=0;i<nowAllNode.length;i++){   //判断是否走过次节点
                        if(JSON.stringify(kz.state) == JSON.stringify(nowAllNode[i].state)) m=false;
                        if(nowAllNode.length==i+1 && m) _this.Open.push(kz);
                    }
                }
            })
            if(n!=0){
                _this.step++;
                lastState=_this.nowOrder;
                _this.nowOrder=_this.Closed.pop().state;
                console.log(_this.nowOrder.toString());
                _this.blockInit();
            }
            n++;
        }
        if(!isSuccess){
            alert('没有找到，重置一下位置试试看！！！╮(╯﹏╰)╭');
        }

    }
    //执行A*成功路径
    puzzle.prototype.successPro=function(){

    }
    //成功
    puzzle.prototype.successShow=function(){
        this.block[8].style.display = "block";
        this.sucessCover.style.display = "block";
        this.succStep.innerHTML = this.step;
    }
    //数组随机排序
    function roa(ar) {
        var arr = ar;
        var temp = new Array();
        var count = arr.length;
        for (i = 0; i < count; i++) {
            var num = Math.floor(Math.random() * arr.length);
            temp.push(arr[num]);
            arr.splice(num, 1);
        }
        return temp;
    }
    //获取dom属性
    function getStyle(obj, name) {
        if (obj.currentStyle) {
            return obj.currentStyle[name];
        } else {
            return getComputedStyle(obj, false)[name];
        }
    }
    //对象深拷贝
    function deepCopy(o) {
        if (o instanceof Array) {
            var n = [];
            for (var i = 0; i < o.length; ++i) {
                n[i] = deepCopy(o[i]);
            }
            return n;

        } else if (o instanceof Object) {
            var n = {}
            for (var i in o) {
                n[i] = deepCopy(o[i]);
            }
            return n;
        } else {
            return o;
        }

    }

    window.Puzzle=function(){
        new puzzle();
    }

})()


window.onload=function () {
    window.Puzzle();
}
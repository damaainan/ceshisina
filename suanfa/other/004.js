    var chnNumChar = {
        零:0,
        一:1,
        二:2,
        三:3,
        四:4,
        五:5,
        六:6,
        七:7,
        八:8,
        九:9
    };
    var chnNumChar2 = {
        0:'零',
        1:'一',
        2:'二',
        3:'三',
        4:'四',
        5:'五',
        6:'六',
        7:'七',
        8:'八',
        9:'九'
    };
    var chnNameValue = {
        十:{value:10, secUnit:false},
        百:{value:100, secUnit:false},
        千:{value:1000, secUnit:false},
        万:{value:10000, secUnit:true},
        亿:{value:100000000, secUnit:true}
    }


    var chnUnitChar = {
        0:"",1:"十",2:"百",3:"千"
    };

    var chnUnitSection = {
        0:'',
        1:'万',
        2:'亿',
        3:'兆'
    };

    function SectionToChinese(section){
        var strIns = '', chnStr = '';
        var unitPos = 0;
        var zero = true;
        while(section > 0){
            var v = section % 10;
            if(v === 0){
                if(!zero){
                    zero = true;
                    chnStr = chnNumChar2[v] + chnStr;
                }
            }else{
                zero = false;
                strIns = chnNumChar2[v];
                strIns += chnUnitChar[unitPos];
                chnStr = strIns + chnStr;
            }
            unitPos++;
            section = Math.floor(section / 10);
        }
        // console.log('chnStr**'+chnStr);
        return chnStr;

    }

    function NumberToChinese(num){
        var unitPos = 0;
        var strIns = '', chnStr = '';
        var needZero = false;
    
        if(num === 0){
            return chnNumChar[0];
        }
    
        while(num > 0){
            var section = num % 10000;

            // console.log('section'+section);

            if(needZero){
                chnStr = chnNumChar[0] + chnStr;
            }
            strIns = SectionToChinese(section);
            // console.log('strIns1'+strIns);
            strIns += (section !== 0) ? chnUnitSection[unitPos] : chnUnitSection[0];
            // console.log('strIns2'+strIns);

            chnStr = strIns + chnStr;
            // console.log('chnStr'+chnStr);

            needZero = (section < 1000) && (section > 0);
            num = Math.floor(num / 10000);
            unitPos++;
        }
    
        return chnStr;
    }


    function ChineseToNumber(chnStr){
        var rtn = 0;
        var section = 0;
        var number = 0;
        var secUnit = false;
        var str = chnStr.split('');
    
        for(var i = 0; i < str.length; i++){
            var num = chnNumChar[str[i]];
            if(typeof num !== 'undefined'){
                number = num;
                if(i === str.length - 1){
                    section += number;
                }
            }else{
                var unit = chnNameValue[str[i]].value;
                secUnit = chnNameValue[str[i]].secUnit;
                if(secUnit){
                    section = (section + number) * unit;
                    rtn += section;
                    section = 0;
                }else{
                    section += (number * unit);
                }
                number = 0;
            }
        }
        return rtn + section;
    }


    var nn=ChineseToNumber("十二");
    console.log(nn);

    var ss=NumberToChinese(10);
    console.log(ss);

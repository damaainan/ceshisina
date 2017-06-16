    var states = [[3,3,0,0,true]];          //初值，顺序为：本地和尚、妖怪;对岸和尚、妖怪、船在此岸        
    var IsLocal = true;                     //是否在此岸，是为真，在对岸为假

    function CanTakeDumpAction(curr,local,from,to){
        //检测船上，和尚数量大于等于妖怪或者和尚为零且总数为1或2
        if((from >= to || from === 0 && to > 0) && (from + to <= 2) && (from + to > 0)){
            if(local){            //此岸与彼岸是不同的
                //船过岸后，两岸都要满足要么和尚为0,要么和尚数量大于等于妖怪
                if((curr[0] >= from && curr[1] >= to && (curr[0] - from == 0 || curr[0] - from >= curr[1] - to)) && (curr[2] + from == 0 || curr[2] + from >= curr[3] + to)){
                    return true;
                }
            }else{
                if((curr[2] >= from && curr[3] >= to && (curr[2] - from == 0 || curr[2] - from >= curr[3] - to)) && (curr[0] + from == 0 || curr[0] + from >= curr[1] + to)){
                    return true;
                }
            }
        }
        return false;
    }

    function IsStateExist(state){
        for(var i = 0; i < states.length; i++){
            if(state[0] == states[i][0] && state[1] == states[i][1] && state[2] == states[i][2] && state[3] == states[i][3] && state[4] == states[i][4]){
                return true;
            }
        }
        return false;
    }

    function DumpWater(curr,local,from,to){
        var next = curr.slice();       
        if(local){        //此岸与彼岸有不同的操作
            next[0] -= from;
            next[1] -= to;
            next[2] += from;
            next[3] += to;
        }else{
            next[0] += from;
            next[1] += to;
            next[2] -= from;
            next[3] -= to;
        }
        next[4] = !local    //船到对岸
        return next;
    }

    (function SearchState(states,local){
        var curr = states[states.length - 1];              //取初始状态
        if(curr[2] == 3 && curr[3] == 3){                  //找到解   
            var rs = ''
            states.forEach(function(al){
                rs += al.join(',') + ' -> ';
            });
            console.log(rs.substr(0,rs.length - 4))
        }
    
        for(var i = 0; i < 3; i++){                         //i表示乘船的和尚数量，0~2
            for(var j = 0; j < 3; j++){                     //j表示乘船的妖怪数量，0~2
                if(CanTakeDumpAction(curr,local,i,j)){      //乘船安排合理
                    var next = DumpWater(curr,local,i,j);   //过河
                    if(!IsStateExist(next)){       
                        states.push(next);
                        SearchState(states,!local);
                        states.pop();
                    }
                }
            }
        }
    })(states,IsLocal);

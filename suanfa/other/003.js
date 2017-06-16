    var FullBacket = [8,5,3]            //桶的最大容量
    var states = [[8,0,0]];             //状态队列，js的数组已经已经有队列和堆栈的支持

    function CanTakeDumpAction(curr,from,to){
        if(from >= 0 && from < 3 && to >= 0 && to < 3){
            if(from != to && curr[from] > 0 && curr[to] < FullBacket[to]){
                return true;
            }
        }
        return false;
    }

    function DumpWater(curr,from,to){
        var next = curr.slice();        //js对象为引用传值，这里要复制一份
        var dump_water = FullBacket[to] - curr[to] > curr[from] ? curr[from] : FullBacket[to] - curr[to]            //倒水量的计算
        next[from] -= dump_water;
        next[to] += dump_water;
        return next;
    }

    function IsStateExist(state){
        for(var i = 0; i < states.length; i++){
            if(state[0] == states[i][0] && state[1] == states[i][1] && state[2] == states[i][2]){
                return true;
            }
        }
        return false;
    }

    (function SearchState(states){
        var curr = states[states.length - 1];
        if(curr[0] == 4 && curr[1] == 4){            //找到正确解
            var rs = ''
            states.forEach(function(al){
                rs += al.join(',') + ' -> ';
            });
            console.log(rs.substr(0,rs.length - 4))
        }
    
        for(var j = 0; j < 3; j++){                //所有的倒水方案即为桶编号的全排列
            for(var i = 0; i < 3; i++){
                if(CanTakeDumpAction(curr,i,j)){
                    var next = DumpWater(curr,i,j);
                    if(!IsStateExist(next)){        //找到新状态
                        states.push(next);
                        SearchState(states);
                        states.pop();
                    }
                }
            }
        }
    })(states);
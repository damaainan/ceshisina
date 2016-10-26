
function sortNumber(a,b){
    return a - b;
}
function group(){//10000次循环 
    var str='';
    var arr=[];
    var mem=[];
    var submem=[];
    for(var i=1;i<11;i++){
        for(var j=1;j<11;j++){
            if(j===i) continue;
            for(var k=1;k<11;k++){
                if(k===j || k===i) continue;
                for(var l=1;l<11;l++){
                    if(l===k ||l===i || l===j) continue;
                    if(i+j===k+l){
                        submem=[i,j,k,l];
                        submem.sort(sortNumber);
                        str=submem.join(",");
                        mem.push(str);
                    }
                }
            }
        }
    }
    var unique = [...new Set(mem)];
    return unique;
}
function sumAB(m,n){
    var a,b,sum;
    var sum1=[];
    for(var i=0;i<100;i++){
        for(var j=0;j<100;j++){
            a=i*10+Number(m);
            b=j*10+Number(n);
            sum=a*a*a+b*b*b;
            sum1[i*100+j]=sum;
        }
    }
    return sum1;
}

function valueM(group){
    var strarr,a,b,c,d;
    var len=group.length;
    var sum1=[];
    var sum2=[];
    var resu=[];
    var total=[];
    for(var m=0;m<len;m++){//50次循环
        strarr=group[m].split(',');
        // console.log(strarr);//打印满足条件的 a b c d 的尾数
        sum1=sumAB(strarr[0],strarr[3]);//10000次循环
        sum2=sumAB(strarr[1],strarr[2]);//10000次循环
        total=sum1.concat(sum2);
        total.sort(sortNumber);
        var lens=total.length;
        for(var k=0;k<lens-1;k++){//20000次循环
            if(total[k+1]===total[k]){
                // console.log(total[k]);//打印 满足条件的 m
                resu.push(total[k]);
            }
        }
    }
    // console.log(resu);//打印结果数组
    // console.log(resu.length);//打印数组长度
}

var group=group();
console.log(group);
valueM(group);

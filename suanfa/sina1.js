function continueMax(arr){
    var len=arr.length;
    var temp=arr[0];
    var swap,total;
    for(var i=1;i<len;i++){
        for(var j=0;j<len;j+=i){
            swap=arr.slice(j,j+i+1);
            total=swap.reduce((a,b)=>a+b);
            if(total>temp){
                temp=total;
            }
        }
    }
    return temp;
}

var arr1=[3,4];
var arr2=[3,-4,4];
var arr3=[3,-4,4,15,6,-3,2,3,4];
// console.log(continueMax(arr1));
// console.log(continueMax(arr2));
console.log(continueMax(arr3));
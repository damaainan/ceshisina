function quick(arr){
	if(arr.length<=1) return arr;
	var In=arr.splice(Math.floor(arr.length/2),1)[0];//標尺
	var left=[],right=[];
	for(var i=0,len=arr.length;i<len;i++){
		if(arr[i]<In) left.push(arr[i]);
		else right.push(arr[i]);
	}
	return quick(left).concat([In],quick(right));
}

var arr=[1,3,2,6,6,6,6,6,9,6,6,6,7];
arr=quick(arr);
console.log(arr);
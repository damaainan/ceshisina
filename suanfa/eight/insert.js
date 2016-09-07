function insert(arr){
	for(var j=0;j<arr.length;j++){
		var key=arr[j];
		var i=j-1;
		while(i>-1 && arr[i]>key){//前 大于 后   3 大于4  此循环只有一次
			arr[i+1]=arr[i];//大者后移  4 等于 3
			console.log(i);
			i--;
		}
		arr[i+1]=key;//i已减  当前等于  3 等于4 
	}
	return arr;
}

var arr=[1,3,2,6,4,9,6,6,6,7];
arr=insert(arr);
console.log(arr);
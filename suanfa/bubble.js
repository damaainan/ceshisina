function bubble(arr){
	var isSort;
	for(var i=0,len=arr.length;i<len;i++){
		isSort=true;
		for(var j=0,lenj=arr.length-1;j<lenj;j++){
			if(arr[j]>arr[j+1]){
				isSort=false;
				arr[j]=[arr[j+1],arr[j+1]=arr[j]][0];
			}
		}
		if(isSort) break;

	}
	return arr;
}

var arr=[1,3,2,6,4,9,7];
arr=bubble(arr);
console.log(arr);
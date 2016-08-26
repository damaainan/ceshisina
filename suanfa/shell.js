function shell(arr){
	var len=arr.length;
	var h=1;
	while(h<len/3){
		h=3*h+1;
	}
	while(h>=1){
		for(var i=h;i<len;i++){
			for(var j=i;j>=h && arr[j]<arr[j-h];j-=h){
				arr[j]=[arr[j-h],arr[j-h]=arr[j]][0];
			}
		}
		h=(h-1)/3;
	}
	return arr;
}

var arr=[1,3,2,6,4,9,6,6,6,7];
arr=shell(arr);
console.log(arr);
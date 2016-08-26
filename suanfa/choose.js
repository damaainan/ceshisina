function choose(arr){
	for (var i = 0; i < arr.length-1; i++) {
		for (var j = i+1; j < arr.length; j++){
			if(arr[j]<arr[i])  arr[i]=[arr[j],arr[j]=arr[i]][0];
		}
	}
	return arr;
}

var arr=[1,3,2,6,4,9,7];
arr=choose(arr);
console.log(arr);
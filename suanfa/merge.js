/*错误的 */
function isArray(arr){
	if(Object.prototype.toString.call(arr)=='[object Array') return true;
	else return false;
}

function merge(left,right){
	var result=[];
	if(!isArray(left)) left=[left];
	if(!isArray(right)) right=[right];
	while(left.length>0 && right.length>0){
		if(left[0]<right[0]) result.push(right.shift());
		else result.push(left.shift());
	}
	console.log(result);
	return result.concat(left).concat(right);
}

function mergeSort(arr){
	var len=arr.length;
	var lim,i,j,k,work=[];
	if(len==1) return arr;
	for(i=0;i<len;i++){
		work.push(arr[i]);
	}
	work.push([]);
	for(lim=len;lim>1;){
		for(j=0,k=0;k<lim;j++,k+=2){
			work[j]=merge(work[k],work[k+1]);
		}
		work[j]=[];
		lim=Math.floor((lim+1)/2);
	}
	return work[0];

}

var arr=[1,3,2,6,4,9,6,6,6,7];
arr=mergeSort(arr);
// console.log(arr);
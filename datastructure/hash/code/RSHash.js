var MAX_U=4294967295;
function RSHash(str)
{/*OK*/
    	str +=  '';
    	var b = 378551,a = 63689,hash = 0,i = 0;
    	while (i<str.length)
    	{
        	var _hash=hash;
        	hash=hashBy(hash,a);
        	hash=(hash+str.charCodeAt(i++));
        	if(hash>MAX_U){
        	    	hash=hash-MAX_U-1;
        	};
        	a=hashBy(a,b);
        	if(a>MAX_U){
        	    	a=a-MAX_U-1;
        	}
    	};
    return (hash & 0x7FFFFFFF);
}

function hashBy(a,b){
    	if(a>MAX_U){
    	    a=a-MAX_U-1;
    	}
    	if(b>MAX_U){
    	    b=b-MAX_U-1;
    	}
    	var log2Ret=parseInt(Math.log(b)/Math.LN2),_b=b-Math.pow(2,log2Ret),sum=0,_a=a;
    	while(log2Ret>0){
        	while(--log2Ret>=0){
            	a=a*2;
            	if(a>MAX_U){
            	    a=a-MAX_U-1;
            	}
        	}
        	sum+=a,a=_a;
        	if(sum>MAX_U){
        	    sum=sum-MAX_U-1;
        	}
        	if(_b>=2){
        	    log2Ret=parseInt(Math.log(_b)/Math.LN2),_b=_b-Math.pow(2,log2Ret);
        	}
    	}
    	sum+=_b*_a;
    	if(sum>MAX_U){
    	    sum=sum-MAX_U-1;
    	}
    	return sum;
}
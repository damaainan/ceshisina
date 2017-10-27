function JSHash(str)
{//OK
    	str+='';
    	var  hash  =  1315423911,i=0;
    	while  (i<str.length)
    	{
    	    hash^=((hash<<5)+str.charCodeAt(i++)+(hash>>>2));
    	    hash=hash>>>0;
    	}
    	return (hash&0x7FFFFFFF );
}
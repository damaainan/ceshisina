function  DJBHash(str)
{//YES
    	str+='';
    	var hash=5381 ,i=0;
    	while(i<str.length)
    	{
    	    hash+=(hash<<5 )+(str.charCodeAt(i++) );
    	}
    	return (hash&0x7FFFFFFF );
}
function  APHash(str)
{
    str+='';
    var hash=0,i=0;
	for  (i=0;i<str.length; i++ )
	{
	    if((i&1 )==0)
	    {
	        hash^=((hash<<7 )^str.charCodeAt(i)^(hash>>>3));
	    }
    	else
    	{
    	    hash^=(~((hash<<11)^str.charCodeAt(i)^(hash>>>5)));
    	}
	}
	return  (hash  &   0x7FFFFFFF );
}
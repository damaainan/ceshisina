function BKDRHash(str)
{/*YES*/
	str+='';
	var  seed  =   131 ;  //  31 131 1313 13131 131313 etc..
	var  hash  =   0 ,i=0;
	while  (i<str.length)
	{
	    hash  =  parseFloat(((hash  *  seed)&0xFFFFFFFF)>>>0)  +  str.charCodeAt(i++);
	}
	return  (hash  &   0x7FFFFFFF );
}
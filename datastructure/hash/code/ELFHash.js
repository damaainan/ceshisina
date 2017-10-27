function  ELFHash(str)
{/*YES*/
    str+='';
    var  hash  =   0 ;
    var  x     =   0 ,i=0;
    while  (i<str.length)
    {
        hash  =  (hash  <<   4 )  +  str.charCodeAt(i++);
        hash=parseFloat(hash>>>0);
        if  ((x  =  parseFloat((hash&0xF0000000)>>>0))  !=   0 )
        {
        	hash  ^=  (x  >>>   24 );
        	hash  &=   ~ x;
        	hash=hash>>>0;
    	}
    }
    return  (hash  &   0x7FFFFFFF );
}
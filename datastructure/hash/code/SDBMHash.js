function  SDBMHash(str)
{//YES
    str+='';
    var  hash  =   0 ,i=0;
    while  (i<str.length)
    {
    	hash  =  str.charCodeAt(i++)  +  (hash  <<   6 )  +  (hash  <<   16 )  -  hash;
    }
    return  (hash  &   0x7FFFFFFF );
}
For Wampp (Windows)
 -----------------------------------------------------------------------------------
 1.  Find out what is your 'PHP Extension Build' version by using phpinfo(). You can use this - http://localhost/?phpinfo=1

 2.  Download the pthreads that matches your php version (32 bit or 64 bit) and php extension build (currently used VC11). Use this link for download - http://windows.php.net/downloads/pecl/releases/pthreads/ 

 3.  Extract the zip -

```
       Move php_pthreads.dll to the 'bin\php\ext\' directory.  
       Move pthreadVC2.dll to the 'bin\php\' directory.  
       Move pthreadVC2.dll to the 'bin\apache\bin' directory.  
       Move pthreadVC2.dll to the 'C:\windows\system32' directory.  
```

 4.  Open php\php.ini and add
```
       extension=php_pthreads.dll
```


只在 php.ini 中添加扩展 
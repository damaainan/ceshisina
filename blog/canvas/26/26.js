function mytable() {  
        var table = document.getElementsByTagName("table")[0]; 
        // var caption = table.createCaption();  
        // caption.innerHTML="Python标准库模块索引表" +"<p>";  
        //找<tbody>  
        var node = table.firstChild;  
  
        while (null != node) {  
        /* 
            想知道都有哪些子节点，用这个 
                var text = document.createTextNode(node.nodeName); 
                document.body.appendChild(text); 
        */  
                  
            if ("TBODY" == node.nodeName)   
                break;            
  
            node = node.nextSibling;  
        }  
          
  
          
        var arr = new Array(  
"__future__", "__main__", "_dummy_thread", "_thread",  
"abc", "aifc", "argparse", "array", "ast", "asynchat", "asyncore",  
"atexit", "audioop",   
"base64", "bdb", "binascii", "binhex", "bisect", "builtins", "bz2",  
"calendar", "cgi", "cgitb", "chunk", "cmath", "cmd", "code",  
"codecs", "codeop", "collections", "colorsys", "compileall",   
"concurrent", "configparser", "contextlib", "copy", "copyreg", "cprofile",  
"csv", "ctypes",   
"detetime", "dbm", "decimal", "difflib", "dis", "distutils", "doctest",  
"dummy_threading",  
"email", "encoding", "errno",  
"faulthandler", "filecmp", "fileinput", "fnmatch", "formatter", "fractions",  
"ftplib", "functools",   
"gc", "getopt", "getpass", "gettext", "glob", "gzip",  
"hashlib", "heapq", "hmac", "html", "http",  
"imaplib", "imghdr", "imp", "importlib", "inspect", "io", "ipaddress",   
"itertools",   
"json",  
"keyword",  
"lib2to3", "linecache", "locale", "logging", "lzma",   
"macpath", "mailbox", "mailcap", "marshal", "math", "mimetypes", "mmap",  
"modulefinder", "multiprocessing",  
"netrc", "nntplib", "numbers",   
"operator", "optparse", "os",   
"parser", "pdb", "pickle", "pickletools", "pkgutil", "platform", "plistlib",  
"poplib", "pprint", "profile", "pstats", "py_compile", "pyclbr", "pydoc",  
"queue", "quopri",   
"random", "re", "reprlib", "rlcompleter", "runpy",  
"sched", "select", "shelve", "shlex", "shutil", "signal", "site",  
"smtpd", "smtplib", "sndhdr", "socket", "socketserver", "sqlite3",  
"ssl", "stat", "string", "stringprep", "struct", "subprocess",  
"sunau", "symbol", "symtable", "sys", "sysconfig",   
"tabnanny", "tarfile", "telnetlib", "tempfile", "test", "textwrap",  
"threading", "time", "timeit", "tkinter", "token", "tokenize", "trace",  
"traceback", "turtle", "types",   
"unicodedata", "unittest", "urllib", "uu", "uuid",   
"venv",  
"warnings", "wave", "weakref", "webbrower", "wsgiref",  
"xdrlib", "xml", "xmlrpc",   
"zipfile", "zipimport", "zlib"        
          
        );  
  
        //单元格插入       
        var tr = null;  
        var td = null;  
        var cell = null;  
        var col = 0;  
          
        while (arr.length > 0) {  
            //插入<tr>            
            tr = document.createElement("tr");  
              
            for (; col < 6; col++) {  
                //插入<td>   
                td = document.createElement("td");  
                cell = document.createTextNode(arr.shift());  
                td.appendChild(cell);  
                tr.appendChild(td);  
                if (arr.length <=0) break;  
            }     
                          
            node.appendChild(tr);  
            col = 0;  
        }  
                  
    }  
    mytable();
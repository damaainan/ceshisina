# [javascript正则表达式][0]

3月5日发布 

RegExp对象表示正则表达式，它是对字符串执行模式匹配的强大工具。  
RegExp对象用于规定在文本中检索的内容。

创建RegExp对象有两种方式：  
1.直接量语法：/pattern/attributes  
示例：var patt1=/w/g  
2.创建 RegExp 对象的语法：new RegExp(pattern, attributes);  
示例：var patt1=new RegExp("e","g");  
注：  
参数 pattern 是一个字符串，指定了正则表达式的模式或其他正则表达式。  
参数 attributes 是一个可选的字符串，包含属性 "g"、"i" 和 "m"，分别用于指定全局匹配、区分大小写的匹配和多行匹配。ECMAScript 标准化之前，不支持 m 属性。如果 pattern 是正则表达式，而不是字符串，则必须省略该参数。

![][11]

![][12]

![][13]

![][14]

![][15]

RegExp对象方法：  
1.compile()  
compile() 方法用于在脚本执行过程中编译正则表达式。  
compile() 方法也可用于改变和重新编译正则表达式。  
语法：RegExpObject.compile(regexp,modifier)，其中regexp是正则表达式，modifier规定匹配的类型  
示例1：

    var str="Every man in the world! Every woman on earth!";
    patt=/man/g;
    str2=str.replace(patt,"person");
    document.write(str2+"<br />");
    patt=/(wo)?man/g;
    patt.compile(patt);
    str2=str.replace(patt,"person");
    document.write(str2);

输出：  
Every person in the world! Every woperson on earth!  
Every person in the world! Every person on earth!

示例2：

    var str = "abc12456def45646ghi";
    var regExp = new RegExp("[a-z]+");
    document.writeln( regExp.exec(str) ); // abc
    // 更改regExp的正则表达式模式，并进行编译
    // 这与下列语句的作用是相同的：regExp = /\d+/;
    regExp.compile("\\d+");
    document.writeln( regExp.exec(str) ); // 12456
    

2.exec()  
exec() 方法用于检索字符串中的正则表达式的匹配。  
语法：RegExpObject.exec(string) 其中string为要检索的字符串  
tip:该方法返回一个数组，其中存放匹配的结果。如果未找到匹配，则返回值为 null。  
示例1：

    var text ="hello w3cschool";
    var pattern =/(..)cs(.)/g;
    var results=pattern.exec(text);
    console.log(results.index);   
    console.log(results.input);   
    console.log(results[0]);　　
    console.log(results[1]); 　　
    console.log(results[2]);　

示例2：  
引自--[http://sentsin.com/web/142.html][16]

    var s = "javascript";  // 测试使用的字符串直接量
    
        var r = /\w/g;    // 匹配模式
    
        while((a = r.exec(s)) != null){ // 循环执行匹配操作
    
            alert(a[0] + "\n" + a.index  + "\n" +  r.lastIndex); /* 显示每次匹配操作时返回的结果数组信息*/
    
        }
        

tiP:当执行全局匹配模式时，exec的行为就略有变化。这时它会定义lastIndex属性，以指定下一次执行匹配时开始检索字符串的位置。在找到了与表达式相匹配的文本之后，exec方法将把正则表达式的lastIndex属性设置为下一次匹配执行的第一个字符的位置。也就是说，可以通过反复地调用exec方法来遍历字符串中的所有匹配文本。当exec再也找不到匹配的文本时，将返回null，并且把属性lastIndex重置为0。

在下面的这个示例中，定义正则表达式直接量，用来匹配字符串s中每个字符。在循环结构的条件表达式中反复执行匹配模式，并将返回结果的值是否为null作为循环条件。当返回值为null时，说明字符串检测完毕。然后，读取返回数组a中包含的匹配子字符串，并调用该数组的属性index和lastIndex，其中index显示当前匹配子字符串的起始位置，而lastIndex属性显示下一次匹配操作的起始位置。

[0]: /a/1190000008578123
[11]: ./img/bVJ9Hc.png
[12]: ./img/bVJ9Hp.png
[13]: ./img/bVJ9Id.png
[14]: ./img/bVJ9IV.png
[15]: ./img/bVJ91C.png
[16]: http://sentsin.com/web/142.html
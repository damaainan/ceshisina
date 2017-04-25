
开启 `chrome://flags` 背后的 `enable-md-extensions` 实验选项

----


1、管理所有插件
地址栏输入 

    about:plugins
2、管理所有扩展
地址栏输入
    
    chrome://extensions/

3、打造便携版Chrome  
创立一个新目录AAA；  
把 X:\Users\用户名\AppData\Local\Google\Chrome\版本号\ 下面所有文件复制到AAA；  
把 X:\Users\用户名\AppData\Local\Google\Chrome\User Data 复制到AAA；  
把 X:\Users\用户名\AppData\Local\Google\Chrome\chrome.exe 复制到AAA；  
在AAA下面创建快捷方式/批处理，加上参数 “-user-data-dir=User Data” 启动chrome.exe；  
AAA目录就是便携版Chrome，用快捷方式/批处理启动chrome即可。  
要升级只要保留User Data目录和快捷方式/批处理，删除其他文件，从最新版chrome安装目录按照以上操作一次。  


----


#### Google Chrome Canary 安装地址  
[https://www.google.com/chrome/browser/canary.html](https://www.google.com/chrome/browser/canary.html)
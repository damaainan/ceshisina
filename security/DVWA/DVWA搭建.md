DVWA共有十个模块，分别是

**Brute Force（暴力（破解））**  
**Command Injection（命令行注入）**  
**CSRF（跨站请求伪造）**  
**File Inclusion（文件包含）**  
**File Upload（文件上传）**  
**Insecure CAPTCHA（不安全的验证码）**  
**SQL Injection（SQL注入）**  
**SQL Injection（Blind）（SQL盲注）**  
**XSS（Reflected）（反射型跨站脚本）**  
**XSS（Stored）（存储型跨站脚本）**  

需要注意的是，DVWA 1.9的代码分为四种安全级别：Low，Medium，High，Impossible。初学者可以通过比较四种级别的代码，接触到一些PHP代码审计的内容。

## DVWA　PHP7 环境下搭建

需要修改文件中 php_mysql 扩展的方法为 php_mysqli 扩展的方法，需要修改的文件列表见 `file.txt`


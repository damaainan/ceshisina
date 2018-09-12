## php弱类型小结

来源：[https://tom0li.github.io/2018/03/04/php弱类型小结/](https://tom0li.github.io/2018/03/04/php弱类型小结/)

时间 2018-03-04 16:51:42

 
php弱类型一般来说 定义变量不需要声明类型，可以任意类型变量或值赋值给变量。
 
### str -> int rule 
 
==为松散比较，数字和字符串比较时，字符串转为数字(rule：取字符串开头第一个,若为数字,则从第一个数字开始直到不是数字的为转换结果，否则为0)

```php
var_dump(1 == '1');    //true
var_dump(2 == '2ewfs');    //true
var_dump(234 == '23wdf');    //flase
var_dump(234 == '234wdf');   //true
var_dump(234 == '234w5456df');  //true
var_dump(234 == '2w5456df');    //flase
```
```
0 == '0' => true
0 == 'abcd' => true
1 == '1abcd' => true
```
 
若一个为整数则另一个转为整数
 
#### 其他 

```
null == '' == 0 == "0" == array() == false => true (多个松散比较任取2个为true)
```
 
#### 数学计算 

```
"0e132456789"=="0e7124511451155" //true
"0e123456abc"=="0e1dddada"    //false
"0e1abc"=="0"     //true
md5('s878926199a') == 0 => true
这里是 md5('s878926199a') 的值是 0e 开头
```
 
e后全是数字则科学计算后比较

```
'0x001'=='1' => true
```
 
0x开头的16进制转为10进制比较
 
#### 函数 
 
#### switch 

```
$j ="3ab";
switch ($j)
```
 
switch会转为整数即3
 
#### in_array()&array_search() 
 
in_array()函数的解释是bool in_array ( mixed $needle , array $haystack [, bool $strict = FALSE ] ),如果strict参数没有提供
 
则会用松散比较

```php
$array=[0,1,2,'3'];
var_dump(in_array('abc', $array));  //true
var_dump(in_array('2bc', $array));    //true
```
 
#### strcmp() 
 
strcmp()函数是int strcmp ( string $str1 , string $str2 ),需要给strcmp()传递2个string类型的参数。如果str1小于str2,返回-1，相等返回0，否则返回1。strcmp函数是将两个变量转换为ascii，然后进行减法运算，然后根据运算结果来决定返回值。

```php
$array=[1,2,3];
var_dump(strcmp($array,'123')); //null,null在宽松比较是false。
```
 
#### Empty 
 
empty 返回 TRUE的情况：
 
若变量不存在则返回 TRUE
 
若变量存在且其值为””、0、”0”、NULL、、FALSE、array()、var $var; 以及没有任何属性的对象，则返回 TURE
 
### 官方图 
 
  
使用PHP函数对变量$x进行比较：
 
  

![][0]

 
  
松散比较(==)
 
  

![][1]

 
  
严格比较(===)
 
  

![][2]

 
### dede V5.7.72弱类型重置密码 
 
文件位置:dedecms/member/resetpassword.php(75行) 

```php
else if($dopost == "safequestion")
{
    $mid = preg_replace("#[^0-9]#", "", $id);
    $sql = "SELECT safequestion,safeanswer,userid,email FROM #@__member WHERE mid = '$mid'";
    $row = $db->GetOne($sql);
    if(empty($safequestion)) $safequestion = '';
 
    if(empty($safeanswer)) $safeanswer = '';
 
    if($row['safequestion'] == $safequestion && $row['safeanswer'] == $safeanswer)
    {
        sn($mid, $row['userid'], $row['email'], 'N');
        exit();
    }
    else
    {
        ShowMsg("对不起，您的安全问题或答案回答错误","-1");
        exit();
    }
 
}

```
 
系统默认问题是”0”，答案是空. php 弱类型绕过，empty函数在判断0.0，0e2时不为空，”0”==”0.0”为真
 
sn函数(/member/inc/inc_pwd_functions.php) 

```php
function sn($mid,$userid,$mailto, $send = 'Y')
{
    global $db;
    $tptim= (60*10);
    $dtime = time();
    $sql = "SELECT * FROM #@__pwd_tmp WHERE mid = '$mid'";
    $row = $db->GetOne($sql);
    if(!is_array($row))
    {
        //发送新邮件；
        newmail($mid,$userid,$mailto,'INSERT',$send);
    }
    //10分钟后可以再次发送新验证码；
    elseif($dtime - $tptim > $row['mailtime'])
    {
        newmail($mid,$userid,$mailto,'UPDATE',$send);
    }
    //重新发送新的验证码确认邮件；
    else
    {
        return ShowMsg('对不起，请10分钟后再重新申请', 'login.php');
    }
}

```
 
根据id在表中判断是否存在用户数据，第一次忘记密码时 $row为空，进入newmail函数
 
newmail函数(dedecms/member/inc/inc_pwd_functions.php) 

```php
function newmail($mid, $userid, $mailto, $type, $send)
{
    global $db,$cfg_adminemail,$cfg_webname,$cfg_basehost,$cfg_memberurl;
    $mailtime = time();
    $randval = random(8);
    $mailtitle = $cfg_webname.":密码修改";
    $mailto = $mailto;
    $headers = "From: ".$cfg_adminemail."\r\nReply-To: $cfg_adminemail";
    $mailbody = "亲爱的".$userid."：\r\n您好！感谢您使用".$cfg_webname."网。\r\n".$cfg_webname."应您的要求，重新设置密码：（注：如果您没有提出申请，请检查您的信息是否泄漏。）\r\n本次临时登陆密码为：".$randval." 请于三天内登陆下面网址确认修改。\r\n".$cfg_basehost.$cfg_memberurl."/resetpassword.php?dopost=getpasswd&id=".$mid;
    if($type == 'INSERT')
    {
        $key = md5($randval);
        $sql = "INSERT INTO `#@__pwd_tmp` (`mid` ,`membername` ,`pwd` ,`mailtime`)VALUES ('$mid', '$userid',  '$key', '$mailtime');";
        if($db->ExecuteNoneQuery($sql))
        {
            if($send == 'Y')
            {
                sendmail($mailto,$mailtitle,$mailbody,$headers);
                return ShowMsg('EMAIL修改验证码已经发送到原来的邮箱请查收', 'login.php','','5000');
            } else if ($send == 'N')
            {
                return ShowMsg('稍后跳转到修改页', $cfg_basehost.$cfg_memberurl."/resetpassword.php?dopost=getpasswd&id=".$mid."&key=".$randval);
            }
        }
        else
        {
            return ShowMsg('对不起修改失败，请联系管理员', 'login.php');
        }
    }
    elseif($type == 'UPDATE')
    {
        $key = md5($randval);
        $sql = "UPDATE `#@__pwd_tmp` SET `pwd` = '$key',mailtime = '$mailtime'  WHERE `mid` ='$mid';";
        if($db->ExecuteNoneQuery($sql))
        {
            if($send == 'Y')
            {
                sendmail($mailto,$mailtitle,$mailbody,$headers);
                ShowMsg('EMAIL修改验证码已经发送到原来的邮箱请查收', 'login.php');
            }
            elseif($send == 'N')
            {
                return ShowMsg('稍后跳转到修改页', $cfg_basehost.$cfg_memberurl."/resetpassword.php?dopost=getpasswd&id=".$mid."&key=".$randval);
            }
        }
        else
        {
            ShowMsg('对不起修改失败，请与管理员联系', 'login.php');
        }
    }
}

```
 
进入$type == ‘INSERT’中，再次发送验证码进入UPDATE中，然后进入$send == ‘N’(默认为”N”),修改url中mid为用户可控的参数member_id，key也有了
 
重置页面 dedecms/member/resetpassword.php

```php
else if($dopost == "getpasswd")
{
    //修改密码
    if(empty($id))
    {
        ShowMsg("对不起，请不要非法提交","login.php");
        exit();
    }
    $mid = preg_replace("#[^0-9]#", "", $id);
    $row = $db->GetOne("SELECT * FROM #@__pwd_tmp WHERE mid = '$mid'");
    if(empty($row))
    {
        ShowMsg("对不起，请不要非法提交","login.php");
        exit();
    }
    if(empty($setp))
    {
        $tptim= (60*60*24*3);
        $dtime = time();
        if($dtime - $tptim > $row['mailtime'])
        {
            $db->executenonequery("DELETE FROM `#@__pwd_tmp` WHERE `md` = '$id';");
            ShowMsg("对不起，临时密码修改期限已过期","login.php");
            exit();
        }
        require_once(dirname(__FILE__)."/templets/resetpassword2.htm");
    }
    elseif($setp == 2)
    {
        if(isset($key)) $pwdtmp = $key;
 
        $sn = md5(trim($pwdtmp));
        if($row['pwd'] == $sn)
        {
            if($pwd != "")
            {
                if($pwd == $pwdok)
                {
                    $pwdok = md5($pwdok);
                    $sql = "DELETE FROM `#@__pwd_tmp` WHERE `mid` = '$id';";
                    $db->executenonequery($sql);
                    $sql = "UPDATE `#@__member` SET `pwd` = '$pwdok' WHERE `mid` = '$id';";
                    if($db->executenonequery($sql))
                    {
                        showmsg('更改密码成功，请牢记新密码', 'login.php');
                        exit;
                    }
                }
            }
            showmsg('对不起，新密码为空或填写不一致', '-1');
            exit;
        }
        showmsg('对不起，临时密码错误', '-1');
        exit;
    }
}

```
 
首先 empty($id)判断是否重置密码，empty($setp)判断是否超时，然后密码修改，判断md5(key)是否等库中pwd，后修改
 


[0]: https://img0.tuicool.com/MvAZzq7.png
[1]: https://img1.tuicool.com/IrAvqqB.png
[2]: https://img2.tuicool.com/juyuAvV.png
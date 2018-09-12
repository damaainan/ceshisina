## [密码学之DES/AES算法](https://segmentfault.com/a/1190000009558962)

本文示例代码详见：[https://github.com/52fhy/cryp...][0]

## DES

DES全称为Data Encryption Standard，即数据加密标准，是一种使用密钥加密的块算法，1977年被美国联邦政府的国家标准局确定为联邦资料处理标准（FIPS），并授权在非密级政府通信中使用，随后该算法在国际上广泛流传开来。

### DES使用简介

使用DES需要设置加密内容、加密key、加密混淆向量iv、分组密码模式、填充模式。

加密内容：  
给定的加密的数据。如果数据长度不是 n*分组大小，则在其后使用 '0' 补齐。

加密Key：  
加密密钥。 如果密钥长度不是该算法所能够支持的有效长度，需要填充。如果密钥长度过长，需要截取。

加密iv:  
用于CBC, CFB, OFB模式，在ECB模式里不是必须的。

分组密码模式：  
常见的分组密码模式有：CBC， OFB，CFB 和 ECB。

填充模式：  
Pkcs5、Pkcs7。

### 填充算法(Pkcs5、Pkcs7)

PKCS5Padding与PKCS7Padding基本上是可以通用的。在PKCS5Padding中，明确定义Block的大小是8位，而在PKCS7Padding定义中，对于块的大小是不确定的，可以在1-255之间（块长度超出255的尚待研究），填充值的算法都是一样的：

    pad = k - (l mod k)  //k=块大小，l=数据长度，如果k=8， l=9，则需要填充额外的7个byte的7

可以得出：Pkcs5是Pkcs7的特例（Block的大小始终是8位）。当Block的大小始终是8位的时候，Pkcs5和Pkcs7是一样的。（[参考][1]）

填充算法实现：

* PHP
```php
function pkcs5_pad($text) {
    $pad = 8 - (strlen($text) % 8);
    //$pad = 8 - (strlen($text) & 7); //也可以使用这种方法
    return $text . str_repeat(chr($pad), $pad);
}

function pkcs7_pad ($text, $blocksize) {
    $pad = $blocksize - (strlen($text) % $blocksize);
    return $text . str_repeat(chr($pad), $pad);
}
```

反填充（去掉填充的字符）只需要根据解密后内容最后一个字符，就知道填充了什么、填充了几个，然后截取掉即可：
```php
function _unpad($text){
    $pad = ord(substr($text, -1));//取最后一个字符的ASCII 码值 
    if ($pad < 1 || $pad > strlen($text)) {
        $pad = 0;
    }
    return substr($text, 0, (strlen($text) - $pad));
}
```
* Python
```python
from Crypto.Cipher import AES

def pkcs7_pad(str):
    x = AES.block_size - (len(str) % AES.block_size)
    if x != 0:
        str = str + chr(x)*x
    return str
    
def _unpad(msg):
    paddingLen = ord(msg[len(msg)-1])
    return msg[0:-paddingLen]
```
### 加密解密步骤

加密步骤（以PHP的扩展mcrypt为例）：  
1、获得加密算法的分组大小（mcrypt_get_block_size）；   
2、被加密的明文使用Pkcs5或Pkcs7填充；  
3、加密密钥key截取或填充至8位；  
4、加密向量iv设置；  
5、打开指定算法和模式对应的模块，返回加密描述符td（mcrypt_module_open）；  
6、使用td、key、iv初始化加密所需的缓冲区 （mcrypt_generic_init）；  
7、加密数据（mcrypt_generic）；  
8、清理的加密描述符td的缓冲区（mcrypt_generic_deinit）；  
9、释放加密描述符td（mcrypt_module_close）；  
10、返回base64_encode的加密结果，可选。

解密步骤（以PHP的扩展mcrypt为例）：  
1、base64_decode解码，如果加密使用了base64_encode；  
2、加密密钥key截取或填充至8位；  
3、加密向量iv设置；  
4、打开指定算法和模式对应的模块，返回加密描述符td（mcrypt_module_open）；  
5、使用td、key、iv初始化加密所需的缓冲区 （mcrypt_generic_init）；  
6、解密数据（mdecrypt_generic）；  
7、清理的加密描述符td的缓冲区（mcrypt_generic_deinit）；  
8、释放加密描述符td（mcrypt_module_close）；  
9、使用Pkcs5去掉填充的内容，返回解密后的结果。

**使用DES需要注意下面几点：**  
1) 确保都使用DES + ECB；  
2) 确保明文填充都使用的是Pkcs5或者Pkcs7，此时两者效果一致；  
3) 加密key在DES长度必须是8字节(bytes)；如果不够长必须填充，过长必须截取；  
4) 加密向量iv与加密key有同样的约定；  
5) 注意加密结果建议都使用base64编码。

只有以上都保持一样，各个语言里最终加密的密文才能保持一致，否则会出现：  
1) 每次加密的密文不一样，但是能解密；（iv随机生成导致的）  
2) 不同语言加密出来的密文不一致。

### 各种语言实现示例

#### PHP

示例：

* Crypt_DES.php
```php
<?php
include('Crypt_DES.php');
$des = new Crypt_DES();//默认是CBC模式
$plaintext = '123456';
$des->setKey('pwd');
//$des->setIV("\0\0\0\0\0\0\0\0");//默认填0，注意是双引号
$encode = base64_encode($des->encrypt($plaintext));

echo $encode. PHP_EOL;
echo $des->decrypt(base64_decode($encode));
```
注意：Crypt_DES类里默认是MCRYPT_MODE_CBC模式，且默认会把加密向量截取或填充至8位：
```
    str_pad(substr($key, 0, 8), 8, chr(0))
```
也就是如果加密向量大于8位，只会截取前8位；少于则补0。  
另外加密向量iv会被设置成\0\0\0\0\0\0\0\0，CRYPT_DES_MODE_ECB模式该变量则不是必须的。所以，如果使用了其它语言需要注意到这点。加密结果请务必base64_decode。

输出：

    pQSWMWLBGQg=
    123456

* PHP使用Mcrypt扩展
```php
/**
 * DES/AES加密封装
 *
 * 1、默认使用Pkcs7填充加密内容。
 * 2、默认加密向量是"\0\0\0\0\0\0\0\0"
 * 3、默认情况下key做了处理：过长截取，过短填充
 *
 * @author 52fhy
 * @github https://github.com/52fhy/
 * @date 2017-5-13 17:08:57
 * Class Crypt
 */
class Crypt {

    private $key;//加密key：如果密钥长度不是加解密算法能够支持的有效长度，会自动填充"\0"。过长则会截取
    private $iv;//加密向量：这里默认填充"\0"。假设为空，程序会随机产生，导致加密的结果是不确定的。ECB模式下会忽略该变量
    private $mode; //分组密码模式：MCRYPT_MODE_modename 常量中的一个，或以下字符串中的一个："ecb"，"cbc"，"cfb"，"ofb"，"nofb" 和 "stream"。
    private $cipher; //算法名称：MCRYPT_ciphername 常量中的一个，或者是字符串值的算法名称。

    public function __construct($key, $cipher = MCRYPT_RIJNDAEL_128, $mode = MCRYPT_MODE_ECB, $iv = "\0\0\0\0\0\0\0"){
        $this->key = $key;
        $this->iv = $iv;
        $this->mode = $mode;
        $this->cipher = $cipher;
    }

    public function encrypt($input){
        $block_size = mcrypt_get_block_size($this->cipher, $this->mode);
        $key = $this->_pad0($this->key, $block_size);//将key填充至block大小
        $td = mcrypt_module_open($this->cipher, '', $this->mode, '');
        $iv = $this->iv ? $this->_pad0($this->iv, $block_size) : @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

        $input = $this->pkcs7_pad($input, $block_size);

        //加密方法一：
//        @mcrypt_generic_init($td, $key, $iv);//ECB模式下，初始向量iv会被忽略
//        $data = mcrypt_generic($td, $input);
//        mcrypt_generic_deinit($td);
//        mcrypt_module_close($td);

        //加密方法二：
        $data = mcrypt_encrypt(
            $this->cipher,
            $key,
            $input,
            $this->mode,
            $iv  //ECB模式下，向量iv会被忽略
        );

        $data = base64_encode($data);//如需转换二进制可改成  bin2hex 转换
        return $data;
    }

    public function decrypt($encrypted){
        $block_size = mcrypt_get_block_size($this->cipher, $this->mode);
        $key = $this->_pad0($this->key, $block_size);
        $td = mcrypt_module_open($this->cipher, '', $this->mode, '');
        $iv = $this->iv ? $this->_pad0($this->iv, $block_size) : @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

        //解密方法一：
//        $encrypted = base64_decode($encrypted); //如需转换二进制可改成  bin2hex 转换
//        @mcrypt_generic_init($td, $key, $iv);
//        $decrypted = mdecrypt_generic($td, $encrypted);
//        mcrypt_generic_deinit($td);
//        mcrypt_module_close($td);

        //解密方法二：
        $decrypted = mcrypt_decrypt(
            $this->cipher,
            $key,
            base64_decode($encrypted),
            $this->mode,
            $iv  //ECB模式下，向量iv会被忽略
        );

        return $this->_unpad($decrypted);
    }

    /**
     * 当使用“PKCS＃5”或“PKCS5Padding”别名引用该算法时，不应该假定支持8字节以外的块大小。
     * @url http://www.users.zetnet.co.uk/hopwood/crypto/scan/cs.html#pad_PKCSPadding
     * @param $text
     * @return string
     */
    public  function pkcs5_pad($text) {
        $pad = 8 - (strlen($text) % 8);
        //$pad = 8 - (strlen($text) & 7); //也可以使用这种方法
        return $text . str_repeat(chr($pad), $pad);
    }

    public  function pkcs7_pad ($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public  function _unpad($text){
        $pad = ord(substr($text, -1));//取最后一个字符的ASCII 码值 
        if ($pad < 1 || $pad > strlen($text)) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }

    /**
     * 秘钥key和向量iv填充算法：大于block_size则截取，小于则填充"\0"
     * @param $str
     * @param $block_size
     * @return string
     */
    private  function _pad0($str, $block_size) {
        return str_pad(substr($str, 0, $block_size), $block_size, chr(0)); //chr(0) 与 "\0" 等效,因为\0转义后表示空字符，与ASCII表里的0代表的字符一样
    }
}

$key = 'pwd';
$des = new Crypt($key, MCRYPT_DES, MCRYPT_MODE_CBC);//DES
echo $ret = $des->encrypt("123456").PHP_EOL;//加密字符串，结果默认已经base64了
echo $ret = $des->decrypt($ret);//解密结果
```
使用MCRYPT_MODE_CBC + Pkcs7。注意和其它语言联调的时候需要注意加密key已经过处理、加密向量默认值的设置。

输出：

    pQSWMWLBGQg=
    123456

#### JS

* CryptoJS
```js
//字符串重复
function str_repeat(target, n) {return (new Array(n + 1)).join(target);}

//使用"\0"填充秘钥或向量
function _pad0(str, block_size) {
    if(str.length >= block_size){
        return str.substr(0, block_size);
    }else{
        return str + str_repeat("\0", block_size - (str.length % block_size));
    }
}

function des_encrypt(data,key,iv){//加密
    var key  = CryptoJS.enc.Utf8.parse(key);
    var iv   = CryptoJS.enc.Utf8.parse(iv);
    var encrypted = CryptoJS.DES.encrypt(data,key,
            {
                iv:iv,
                mode:CryptoJS.mode.CBC,
                padding:CryptoJS.pad.Pkcs7
            });
    return encrypted.toString();
}

function des_decrypt(encrypted,key,iv){//解密
    var key  = CryptoJS.enc.Utf8.parse(key);
    var iv   = CryptoJS.enc.Utf8.parse(iv);
    var decrypted = CryptoJS.DES.decrypt(encrypted,key,
            {
                iv:iv,
                mode:CryptoJS.mode.CBC,
                padding:CryptoJS.pad.Pkcs7
            });
    return decrypted.toString(CryptoJS.enc.Utf8);
}

var key  = _pad0("pwd", 8);
var iv   = _pad0("\0", 8);
encrypted = des_encrypt("123456",key,iv);//pQSWMWLBGQg=
decryptedStr = des_decrypt(encrypted,key,iv);//123456
```
#### Python

环境：Python 2.7.5，Linux CentOS7

需要先安装：

    pip install pycrypto
    pip install Crypto

```python
# -*- coding=utf-8-*-
from Crypto.Cipher import DES
import base64

"""
des cbc加密算法
padding : PKCS5
"""

class DESUtil:

    __BLOCK_SIZE_8 = BLOCK_SIZE_8 = DES.block_size
    __IV = "\0\0\0\0\0\0\0\0" # __IV = chr(0)*8

    @staticmethod
    def encryt(str, key):
        cipher = DES.new(key, DES.MODE_CBC, DESUtil.__IV)
        x = DESUtil.__BLOCK_SIZE_8 - (len(str) % DESUtil.__BLOCK_SIZE_8)
        if x != 0:
            str = str + chr(x)*x
        msg = cipher.encrypt(str)
        # msg = base64.urlsafe_b64encode(msg).replace('=', '')
        msg = base64.b64encode(msg)
        return msg

    @staticmethod
    def decrypt(enStr, key):
        cipher = DES.new(key, DES.MODE_CBC,DESUtil.__IV)
        # enStr += (len(enStr) % 4)*"="
        # decryptByts = base64.urlsafe_b64decode(enStr)
        decryptByts = base64.b64decode(enStr)
        msg = cipher.decrypt(decryptByts)
        paddingLen = ord(msg[len(msg)-1])
        return msg[0:-paddingLen]

if __name__ == "__main__":
    key = "12345678"
    res = DESUtil.encryt("123456", key)
    print res
    print DESUtil.decrypt(res, key)
```
输出：

    ED5wLgc3Mnw=
    123456

如果加密密钥小于8位，需要填充"\0"，示例：

    key = "pwd" + chr(0)*5

修改运行后输出：

    pQSWMWLBGQg=
    123456

## AES

### AES简介

AES（Advanced Encryption Standard），在密码学中又称Rijndael加密法，是美国联邦政府采用的一种区块加密标准。这个标准用来替代原先的DES，已经被多方分析且广为全世界所使用。经过五年的甄选流程，高级加密标准由美国国家标准与技术研究院（NIST）于2001年11月26日发布于FIPS PUB 197，并在2002年5月26日成为有效的标准。2006年，高级加密标准已然成为对称密钥加密中最流行的算法之一。

ECB模式是将明文按照固定大小的块进行加密的，块大小不足则进行填充。ECB模式没有用到向量。

**使用AES需要注意下面几点：**  
1) 确保都使用AES_128 + ECB；  
2) 确保明文填充都使用的是Pkcs7；  
3) 加密key在AES_128长度必须是16, 24, 或者 32 字节(bytes)；如果不够长必须填充，过长必须截取，建议直接md5；  
4) 加密向量iv与加密key有同样的约定，但在ECB可以忽略该值（用不到）。  
5) 注意加密结果建议都使用base64编码。

只有以上都保持一样，各个语言里最终加密的密文才能保持一致，否则会出现：  
1) 每次加密的密文不一样，但是能解密；（iv随机生成导致的）  
2) 不同语言加密出来的密文不一致。

### 各种语言实现示例

#### PHP

示例：

* PHP使用Mcrypt扩展
这里还是使用上文的Crypt类。
```php
$key = 'pwd';
$des = new Crypt($key);//AES，默认是MCRYPT_RIJNDAEL_128+MCRYPT_MODE_ECB
echo $ret = $des->encrypt("123456").PHP_EOL;//加密字符串，结果默认已经base64了
echo $ret = $des->decrypt($ret);//解密结果
echo PHP_EOL.'--------------'.PHP_EOL;

$key = '1234567812345678';
$des = new Crypt($key);//AES，默认是MCRYPT_RIJNDAEL_128+MCRYPT_MODE_ECB
echo $ret = $des->encrypt("123456").PHP_EOL;//加密字符串，结果默认已经base64了
echo $ret = $des->decrypt($ret);//解密结果
```
使用ECB + Pkcs7。和其它语言联调的时候需要注意加密key已经过处理、加密向量默认值的设置。

输出结果：

    3+WQyhMavuxzPzy40PZhJg==
    123456
    --------------
    mdSm0RmB+xAKrTah3DG31A==
    123456

本例里当key长度不够时，封装的类已经自动帮我们填充好了足够长度；当key长度等于16时，key的值不会改变。

#### JS

* CryptoJS
和DES代码基本一样，只要把DES改为AES即可，CBC改为ECB，块大小改为16。
```js
//字符串重复
function str_repeat(target, n) {return (new Array(n + 1)).join(target);}

//使用"\0"填充秘钥或向量
function _pad0(str, block_size) {
    if(str.length >= block_size){
        return str.substr(0, block_size);
    }else{
        return str + str_repeat("\0", block_size - (str.length % block_size));
    }
}

function aes_encrypt(data,key,iv){//加密
    var key  = CryptoJS.enc.Utf8.parse(key);
    var iv   = CryptoJS.enc.Utf8.parse(iv);
    var encrypted = CryptoJS.AES.encrypt(data,key,
            {
                iv:iv,
                mode:CryptoJS.mode.ECB,
                padding:CryptoJS.pad.Pkcs7
            });
    return encrypted.toString();
}

function aes_decrypt(encrypted,key,iv){//解密
    var key  = CryptoJS.enc.Utf8.parse(key);
    var iv   = CryptoJS.enc.Utf8.parse(iv);
    var decrypted = CryptoJS.AES.decrypt(encrypted,key,
            {
                iv:iv,
                mode:CryptoJS.mode.ECB,
                padding:CryptoJS.pad.Pkcs7
            });
    return decrypted.toString(CryptoJS.enc.Utf8);
}

var key  = _pad0("pwd", 16);
var iv   = _pad0("\0", 16);
encrypted = aes_encrypt("123456",key,iv);//3+WQyhMavuxzPzy40PZhJg==
decryptedStr = aes_decrypt(encrypted,key,iv);//123456
```
ECB模式没有用到向量。本例如果改为CBC，只需要把ECB改为CBC即可，加密结果还是：3+WQyhMavuxzPzy40PZhJg==。换了加密向量则不一样了。

#### Python

环境：Python 2.7.5，Linux CentOS7

需要先安装：

    pip install pycrypto
    pip install Crypto

```python
# -*- coding=utf-8-*-

from Crypto.Cipher import AES
import os
from Crypto import Random
import base64

"""
aes加密算法
padding : PKCS7
"""

class AESUtil:

    __BLOCK_SIZE_16 = BLOCK_SIZE_16 = AES.block_size

    @staticmethod
    def encryt(str, key):
        #cipher = AES.new(key, AES.MODE_ECB,b'0000000000000000') #第三个参数是加密向量iv，ECB模式不需要
        cipher = AES.new(key, AES.MODE_ECB)
        x = AESUtil.__BLOCK_SIZE_16 - (len(str) % AESUtil.__BLOCK_SIZE_16)
        if x != 0:
            str = str + chr(x)*x
        msg = cipher.encrypt(str)
        # msg = base64.urlsafe_b64encode(msg).replace('=', '')
        msg = base64.b64encode(msg)
        return msg

    @staticmethod
    def decrypt(enStr, key):
        cipher = AES.new(key, AES.MODE_ECB)
        # enStr += (len(enStr) % 4)*"="
        # decryptByts = base64.urlsafe_b64decode(enStr)
        decryptByts = base64.b64decode(enStr)
        msg = cipher.decrypt(decryptByts)
        paddingLen = ord(msg[len(msg)-1])
        return msg[0:-paddingLen]

if __name__ == "__main__":
    key = "1234567812345678"
    res = AESUtil.encryt("123456", key)
    print(res)
    print(AESUtil.decrypt(res, key))
```
输出：

    mdSm0RmB+xAKrTah3DG31A==
    123456

这里使用了AES+ECB+PKCS7Padding方法。加密结果和PHP是一致的。

## 服务端/客户端加密选型

### DES/CBC/PKCS7Padding

此时加密块大小都是8字节，PKCS5和PKCS7效果一样。各端实现的时候需要注意：  
1) 使用相同的加密key，注意长度必须是8字节；  
2) 使用相同的向量iv，建议设置成"\0\0\0\0\0\0\0"；  
3) 必须实现相同的PKCS7填充算法和反填充算法；  
4) 加密结果都使用base64编码。

### AES/ECB/PKCS7Padding

使用AES_128加密块大小都是16字节，PKCS5无法使用，请使用PKCS7。各端实现的时候需要注意：  
1) 使用相同的加密key，注意长度必须是16, 24, 或者 32 字节(bytes)；如果不够长必须填充，过长必须截取，建议直接md5；  
2) 使用相同的向量iv，建议设置成"\0\0\0\0\0\0\0\0\0\0\0\0\0\0"；可以和加密key一样使用md5后的值；ECB模式下可以忽略该项；  
3) 必须实现相同的PKCS7填充算法和反填充算法；  
4) 加密结果都使用base64编码。

### AES/CBC/PKCS7Padding

和AES/ECB/PKCS7Padding基本一致，但由于CBC模式用到向量，注意向量长度最少16字节。如果长度不够，请填充"0"。建议随机生成，然后base64后传给前端。

## 常用库介绍

### Mcrypt

Mcrypt 是一个功能强大的加密算法扩展库。

Mcrypt 库提供了对多种块算法的支持， 包括：DES，TripleDES，Blowfish （默认）， 3-WAY，SAFER-SK64，SAFER-SK128，TWOFISH，TEA，RC2 以及 GOST，并且支持 CBC，OFB，CFB 和 ECB 密码模式。

PHP里通过启用 Mcrypt 扩展即可使用（mcrypt_开头的系列函数）。注意的是，要使用该扩展，必须首先安装mcrypt标准类库，而 mcrypt 标准类库依赖 libmcrypt 和 mhash 两个库。从 PHP 5.0.0 开始，需要使用 libcrypt 2.5.6 或更高版本。

### Crypto-JS

[https://github.com/brix/crypt...][2]

CryptoJS (crypto.js) 为 JavaScript 提供了各种各样的加密算法。目前已支持的算法包括：

* MD5
* SHA-1
* SHA-256
* AES
* Rabbit
* MARC4
* HMAC
    * HMAC-MD5
    * HMAC-SHA1
    * HMAC-SHA256
* PBKDF2

### PyCrypto

[https://github.com/dlitz/pycr...][3]

PyCrypto是使用Python编写的加密工具包。支持所有主流算法。

### hashlib

Python的hashlib提供了常见的摘要算法，如MD5，SHA1等等。

### Crypt_DES.php

[https://my.oschina.net/u/9956...][4]

通过纯PHP实现的DES加密。示例：
```php
<?php

include('Crypt_DES.php');

$des = new Crypt_DES();
$des->setKey('abcdefgh');
$plaintext = 'test';
echo $des->decrypt($des->encrypt($plaintext));
```
## 在线工具

1、在线加密解密  
[http://tool.oschina.net/encry...][5]

## 资料

1、Java 加解密技术系列之 DES - 紫羽风的博客 - 博客频道 - CSDN.NET  
[http://blog.csdn.net/happylee...][6]  
2、一个很好的PHP加密算法 DES加密类_PHP基础_UncleToo - 专注PHP技术推广  
[http://www.uncletoo.com/html/...][7]  
3、关于CryptoJS中md5加密以及aes加密的随笔 - 李盈盈的小博客 - 博客园  
[http://www.cnblogs.com/liying...][8]  
4、关于PKCS5Padding与PKCS7Padding的区别 - 在路上... - 博客园  
[http://www.cnblogs.com/midea0...][9]  
5、AES ECB加密实现（java/php/python） - sevenlater的博客 - 博客频道 - CSDN.NET  
[http://blog.csdn.net/sevenlat...][10]

[0]: https://github.com/52fhy/crypt-demo
[1]: http://www.users.zetnet.co.uk/hopwood/crypto/scan/cs.html#pad_PKCSPadding
[2]: https://github.com/brix/crypto-js
[3]: https://github.com/dlitz/pycrypto
[4]: https://my.oschina.net/u/995648/blog/113390
[5]: http://tool.oschina.net/encrypt?type=2
[6]: http://blog.csdn.net/happylee6688/article/details/44455407
[7]: http://www.uncletoo.com/html/base/846.html
[8]: http://www.cnblogs.com/liyingying/p/6259756.html
[9]: http://www.cnblogs.com/midea0978/articles/1437257.html
[10]: http://blog.csdn.net/sevenlater/article/details/50317999
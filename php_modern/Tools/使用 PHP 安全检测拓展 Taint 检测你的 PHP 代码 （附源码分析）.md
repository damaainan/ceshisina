## 使用 PHP 安全检测拓展 Taint 检测你的 PHP 代码 （附源码分析）

来源：[https://www.jianshu.com/p/c6dea66c54f3](https://www.jianshu.com/p/c6dea66c54f3)

时间 2018-04-23 10:10:13


  
## 一.拓展简介

Taint是鸟哥写的一个PHP拓展 支持PHP5.2~PHP7.2。拓展启用后能监控某些关键函数是否直接使用了来源于用户输入($_GET,$_POST,$COOKIE)而没有经过特殊处理的字符串。

举个例子,在你web服务器的根目录下创建一个如下的`taint.php`文件

```php
<?php
// <YOUR_WEB_ROOT/taint.php>
$strA = trim($_GET['test']);
$strB='input a '.sprintf('%s',$strA);
echo $strB;
```

当Taint启动后，访问`http://host/taint.php?test=dog`执行该脚本会收到如下的警告

```php
Warning: main() [echo]: Attempt to echo a string that might be tainted in /YOUR_WEB_ROOT/taint.php on line 5
input a dog
```

这可以帮助你及早潜在的Xss，SQL Inject等攻击点。

  
## 二.拓展搭建

Taint非常轻量级，没有PHP版本以外的任何依赖，使用常规方法即可编译出动态模块

```
$ git clone https://github.com/laruence/taint.git
$ cd ./taint
$ /PHP_PATH/bin/phpize
$ ./configure --with-php-config=/PHP_PATH/bin/php-config
$ make && make install
```

编辑`php.ini`文件

```
$ vim /PHP_INI_PATH/php.ini
```

在末尾添加以下内容

```
[taint]
extension=taint.so

;taint.enable 表示Taint的开关,默认0为关闭，打开需要显式配置为1
taint.enable = 1  

;taint.error_level 表示发现潜在注入问题时抛出错误的等级，一般使用默认值E_WARNING即可。根据实际情况也可以选择为E_NOTICE，E_ERROR等值
taint.error_level = E_WARNING
```

重启你的php-fpm或者apache服务，使用浏览器访问上面的`taint.php`即可看到拓展效果

  
## 三.源码实现

由于这个拓展的文档和其他资料基本没有，这里附上关键源码辅助讲解实现机制。

  
#### 污染标记

Taint定义了3个核心宏

```c
#define TAINT_MARK(str)     (GC_FLAGS((str)) |= IS_STR_TAINT_POSSIBLE)//该宏标记一个字符串为受污染(后续使用污染代替Taint)
#define TAINT_POSSIBLE(str) (GC_FLAGS((str)) & IS_STR_TAINT_POSSIBLE)//该宏返回一个字符串是否是受污染的
#define TAINT_CLEAN(str)    (GC_FLAGS((str)) &= ~IS_STR_TAINT_POSSIBLE)//该宏清除污染标记
```
`GC_FLAGS()`是PHP内核宏`#define GC_FLAGS(p) (p)->gc.u.v.flags`,参数p类型为`zend_value`指针

```c
//代表PHP中的一个值
typedef union _zend_value {
    zend_long lval;             /* long value */
    double dval;             /* double value */
    zend_refcounted *counted;
    zend_string *str;
    zend_array *arr;
    zend_object *obj;
    zend_resource *res;
    zend_reference *ref;
    zend_ast_ref *ast;
    zval *zv;
    void *ptr;
    zend_class_entry *ce;
    zend_function *func;
    struct {
        uint32_t w1;
        uint32_t w2;
    } ww;
} zend_value;

//代表PHP中的一个字符串值
struct _zend_string {
    zend_refcounted_h gc;
    zend_ulong h; /* hash value */
    size_t len;
    char val[1];
};

//zend_value中的成员，存放内存回收相关信息
typedef struct _zend_refcounted_h {
    uint32_t refcount;          /* reference counter 32-bit */
    union {
        struct {
            ZEND_ENDIAN_LOHI_3(
                zend_uchar type,
                zend_uchar flags, /* used for strings & objects */
                uint16_t gc_info) /* keeps GC root number (or 0) and color */
        } v;
        uint32_t type_info;
    } u;
} zend_refcounted_h;
```


污染标记的原理是借助`_zend_string`的内存回收结构的u.v.flags字段的一个未被使用的标记位去记录字符串是否被污染。

基于该原理，Taint可能会和更新版本的PHP或者借用该标记位的其他PHP拓展冲突。

      
#### 初始化外部字符串污染标记

```c
/* {{{ PHP_RINIT_FUNCTION
*/
PHP_RINIT_FUNCTION(taint)
{
    if (SG(sapi_started) || !TAINT_G(enable)) {
        return SUCCESS;
    }

    if (Z_TYPE(PG(http_globals)[TRACK_VARS_POST]) == IS_ARRAY) {
        //php_taint_mark_strings()功能是递归遍历array，对每个字符串调用TAINT_MARK(),标记字符串为受污染的
        php_taint_mark_strings(Z_ARRVAL(PG(http_globals)[TRACK_VARS_POST]));
    }

    if (Z_TYPE(PG(http_globals)[TRACK_VARS_GET]) == IS_ARRAY) {
        php_taint_mark_strings(Z_ARRVAL(PG(http_globals)[TRACK_VARS_GET]));
    }

    if (Z_TYPE(PG(http_globals)[TRACK_VARS_COOKIE]) == IS_ARRAY) {
        php_taint_mark_strings(Z_ARRVAL(PG(http_globals)[TRACK_VARS_COOKIE]));
    }

    return SUCCESS;
}
```

该方法会在REQUEST_INIT阶段调用，即对于每个WEB请求到来后，对$_GET,$_POST,$_COOKIE中所有字符串进行污染标记。

  
#### 污染扩散

Taint通过在MODULE_INIT阶段覆盖PHP内核原生的大量相关的字符串函数和opcode的handler来保证污染字符串的有效扩散。新句柄主要都是代理，在底层委托原本的handler，并附加上Taint的一些处理。

以`sprintf()`作为函数覆盖的示例：

```c
//覆盖原生sprintf()
    php_taint_override_func(f_sprintf, PHP_FN(taint_sprintf), &TAINT_O_FUNC(sprintf));
```

```c
/* {{{ proto string sprintf(string $format, ...)
*/
PHP_FUNCTION(taint_sprintf) {
    zval *args;
    int i, argc, tainted = 0;
    //PHP参数解析,后文略
    if (zend_parse_parameters(ZEND_NUM_ARGS(), "+", &args, &argc) == FAILURE) {
        RETURN_FALSE;
    }
    //检查sprintf()的所有参数，包括模板参数和绑定参数，是否存在污染字符串
    for (i = 0; i < argc; i++) {
        if (IS_STRING == Z_TYPE(args[i]) && TAINT_POSSIBLE(Z_STR(args[i]))) {
            tainted = 1;
            break;
        }
    }
    //调用本来sprintf()的句柄；
    TAINT_O_FUNC(sprintf)(INTERNAL_FUNCTION_PARAM_PASSTHRU);
    //根据参数污染监测的结果对sprintf()的返回字符串进行污染标记
    if (tainted && IS_STRING == Z_TYPE_P(return_value) && Z_STRLEN_P(return_value)) {
        TAINT_MARK(Z_STR_P(return_value));
    }
}
```

以`ZEND_CONCAT`作为函数覆盖的示例：

```c
//覆盖ZEND_CONCAT (如字符串连接'a'.$str)原本的handler
    zend_set_user_opcode_handler(ZEND_CONCAT, php_taint_concat_handler);
```

```c
static int php_taint_concat_handler(zend_execute_data *execute_data) /* {{{ */ {
    const zend_op *opline = execute_data->opline;
    zval *op1, *op2, *result;
    taint_free_op free_op1, free_op2;
    int tainted = 0;

    //提取字符串链接两个操作数以及返回值的zval指针
    op1 = php_taint_get_zval_ptr(execute_data, opline->op1_type, opline->op1, &free_op1, BP_VAR_R, 1);
    op2 = php_taint_get_zval_ptr(execute_data, opline->op2_type, opline->op2, &free_op2, BP_VAR_R, 1);
    result = EX_VAR(opline->result.var);

    //判断源字符串是存在污染字符串
    if ((op1 && IS_STRING == Z_TYPE_P(op1) && TAINT_POSSIBLE(Z_STR_P(op1)))
            || (op2 && IS_STRING == Z_TYPE_P(op2) && TAINT_POSSIBLE(Z_STR_P(op2)))) {
        tainted = 1;
    }

    //字符串拼接
    concat_function(result, op1, op2);

    //结果字符串污染标记
    if (tainted && IS_STRING == Z_TYPE_P(result) && Z_STRLEN_P(result)) {
        TAINT_MARK(Z_STR_P(result));
    }
    
    //其他opcode常规操作(操作数释放，当前opline指针递增并执行后面的opline)
    if ((TAINT_OP1_TYPE(opline) & (IS_VAR|IS_TMP_VAR)) && free_op1) {
        zval_ptr_dtor_nogc(free_op1);
    }

    if ((TAINT_OP2_TYPE(opline) & (IS_VAR|IS_TMP_VAR)) && free_op2) {
        zval_ptr_dtor_nogc(free_op2);
    }

    execute_data->opline++;
    return ZEND_USER_OPCODE_CONTINUE;
} /* }}} */
```

这就是上文hello world中`$strB='input a '.sprintf('%s',$strA);`，为何$strB已经经过修改，却仍然能够被识别出是个被污染的字符串的原因。PHP内核中的字符串相关的处理函数和opcode都被改写了，保证由污染字符串产生的衍生字符串也都会被标记成污染字符串。

  
#### 类似的被覆盖函数有:

  

* join();
* trim();
* split();
* rtrim();
* ltrim();
* strval();
* strstr();
* substr();
* sprintf();
* explode();
* implode();
* str_pad();
* vsprintf();
* str_replace();
* str_ireplace();
* strtolower();
* strtoupper();
* dirname();
* basename();
* pathinfo();
    

类似的被覆盖的Opcode有:

  

* ZEND_CONCAT
* ZEND_FAST_CONCAT
* ZEND_ROPE_END
    

  
#### 污染告警/注入监控

为了在关键点使用了被污染的字符串时能够做出告警,除了污染拓展章节提到的opcode,Taint还覆盖了大量其余opcode的handler。

一方面覆盖了以下Opcode在echo,print,include,require,eval,动态方法调用中直接使用污染字符串时抛出警告

  

* ZEND_ECHO
* ZEND_EXIT
* ZEND_INCLUDE_OR_EVAL
* ZEND_INIT_USER_CALL
* ZEND_INIT_DYNAMIC_CALL
    

一方面覆盖了一下Opcode在函数调用前对特定的参数进行污染检查

  

* ZEND_DO_FCALL
* ZEND_DO_ICALL
* ZEND_DO_FCALL_BY_NAME
    


会在以下内部函数/方法的调用前进行taint参数检查和错误抛出：

注:本文提到的 内部函数 是区别于使用PHP实现的用户函数的函数。内部函数指使用C语言在PHP内核或拓展层面实现的提供给用户在PHP中调用方法或函数，如printf()

      

* print_r();
* fopen();
* unlink();
* file();
* readfile();
* file_get_contents();
* opendir();
* printf();
* vprintf();
* file_put_contents();
* fwrite();
* header();
* unserialize();
* mysqli_query();
* mysqli_prepare();
* mysql_query();
* sqlite_query();
* sqlite_single_query();
* oci_parse();
* preg_replace_callback();
* passthru();
* system();
* exec();
* shell_exec();
* proc_open();
* popen();
* mysqli::query();
* mysqli::prepare();
* PDO::query();
* PDO::prepare();
* SQLite3::query();
* SQLite3::prepare();
* sqlitedatabase::query();
* sqlitedatabase::singlequery();
    

  
#### 提供内部函数

```c
/* {{{ proto bool taint(string $str[, string ...])
*/
PHP_FUNCTION(taint)
{
    zval *args;
    int argc;
    int i;
    //检查拓展是否启用
    if (!TAINT_G(enable)) {
        RETURN_TRUE;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS(), "+", &args, &argc) == FAILURE) {
        return;
    }
   //该方法支持不定参数
    for (i = 0; i <     ; i++) {
        zval *el = &args[i];
        ZVAL_DEREF(el);
        if (IS_STRING == Z_TYPE_P(el) && Z_STRLEN_P(el) && !TAINT_POSSIBLE(Z_STR_P(el))) {
            /* string might be in shared memory */
            //重建字符串并标记新字符串为污染字符串，gc计数更变，变量赋值
            zend_string *str = zend_string_init(Z_STRVAL_P(el), Z_STRLEN_P(el), 0);
            zend_string_release(Z_STR_P(el));
            TAINT_MARK(str);
            ZVAL_STR(el, str);
        }
    }

    RETURN_TRUE;
}
/* }}} */

/* {{{ proto bool untaint(string $str[, string ...])
*/
PHP_FUNCTION(untaint)
{
    //...
    TAINT_CLEAN()
    //...
}
/* }}} */

/* {{{ proto bool is_tainted(string $str)
*/
PHP_FUNCTION(is_tainted)
{
    //.....
    TAINT_POSSIBLE();
    //...
}
```

拓展提供了taint(),untaint(),is_tainted()3个函数作为对`TAINT_MARK()`,`TAINT_POSSIBLE()`,`TAINT_CLEAN()`宏的封装，以便用户可以直接在PHP中利用相关机制对Taint进行拓展。

  
#### 污染标记清理

已知有3种方式可以清理字符串上的污染标记

  
#### 一.使用`htmlentities()`,`addslashes()`,`mysql_escape_string()`等转义方法生成了 **`新的`** 字符串。    


根据实现，这个说法其实并不严谨。实际上Taint并没有在以上转义方法上添加特殊处理，不是Taint对转义函数进行了特殊处理，而是因为Taint对转义函数没有进行处理所以返回的字符串是没有污染标记的。

在污染扩散章节中没有提到的字符串处理内部函数如果生成了新的`zend_string`其实都是没有污染标记的，因此此处我也无法提供一个完整的带有污染标记清理的方法列表。

基于这个原理，可以尝试使用`json_encode()`处理一个Taint的字符串，你会发现虽然`json_encode()`是一个安全无关的方法，但是其返回值都是Taint认可的干净的字符串。

另外有些方法在参数无需处理的情况下是不会生成新的`zend_string`，此时污染标记不会清除，譬如

```php
$str=$_GET['userName'];/
$str2=addslashes($str);
```


预期上你会认为`$str2`总是干净的，实际上并不然。

如果`$str`原来的值是"aa'a",`$srr2`是没有污染标记的

如果`$str`原来的值是"aaa",处理后的字符串`$str2`还是原来的字符串`$str`，污染标记仍然存在，Taint仍然会对该字符串给出警告。

      
#### 二.使用Taint拓展提供的内部方法`untaint(&$str,...)`清理标记    

考虑到方案一的处理原理，手动将字符串标记为干净的`untaint()`是一个更加实用的方案。你可以在类库的相关安全处理方法中添加该方法，标记字符串的污染状态为干净的。

  
#### 三.利用Taint未处理的机制构造字符串（不推荐）

```php
function trick($str){
    return $str;
    $result='';
    $strlen=strlen($str);
    for ($i=0;$i<$strlen;$i++){
        $result.=$str[$i]    ;
    }    
    return $result;
}
```

由于Taint未对`ZEND_FETCH_DIM_*`几个Opcode进行特殊处理，所以虽然上述函数的返回值和参数是同一个字符串，但是返回值永远是干净的。

  
## 四.实践思路


Taint提供了一个很好的思路去监控应用的安全情况。不像人工攻击测试需要昂贵的人力成本投入和安全扫描工具需要大量系统资源消耗，他资源消耗小，而且

然而Taint目前能够处理的问题并不够多，主要在Sql注入，XSS，命令注入几个方面，而且Taint目前并没有对不同类型的污染进行区分，而是共享同一个污染标记位，任何一个方法都会同时标记或者清理所有的污染标记，考虑到这个原因仅仅建议使用Taine作为其他安全手段的补充。

    
考虑到稳定性和性能问题，不建议在生产环境开启Taint。

作为安全监控拓展，在开发测试环境安装并启用，根据警告处理问题字段即可。

对于Taint自带污染标记清理机制不能满足的地方，手动调用以下方法即可。

```php
function markSafe(string &$string){
    if(function_exists('untaint')){
        return untaint($string);
    }else{
        return true;
    }
}
```

  
## 五.拓展阅读

    
[Laruence:《PHP Taint – 一个用来检测XSS/SQL/Shell注入漏洞的扩展》][0]

[<laruence/taint-GitHub Readme File>][1]

      [<PHP: rfc:taint>][2]
    

  

[0]: https://link.jianshu.com?t=http%3A%2F%2Fwww.laruence.com%2F2012%2F02%2F14%2F2544.html
[1]: https://link.jianshu.com?t=https%3A%2F%2Fgithub.com%2Flaruence%2Ftaint%2Fblob%2Fmaster%2FREADME.md
[2]: https://link.jianshu.com?t=https%3A%2F%2Fwiki.php.net%2Frfc%2Ftaint
## 深入理解PHP之isset和array_key_exists对比

来源：[https://juejin.im/post/5bbb27a9e51d450e7c0d9c74](https://juejin.im/post/5bbb27a9e51d450e7c0d9c74)

时间 2018-10-11 10:21:50

 
经常使用`isset`判断变量或数组中的键是否存在, 但是数组中可以使用`array_key_exists`这个函数, 那么这两个谁最优呢?
 
官方文档对两者的定义
 
| - | 分类 | 描述 | 文档 |
| - | - | - | - | 
| isset | 语言构造器 | 检测变量是否已设置并且非 NULL | [php.net/manual/zh/f…][4] | 
| array_key_exists | 函数 | 检查数组里是否有指定的键名或索引 | [php.net/manual/zh/f…][5] | 
 
 `isset()`对于数组中为 NULL 的值不会返回 TRUE，而`array_key_exists()`会。`array_key_exists()`仅仅搜索第一维的键。 多维数组里嵌套的键不会被搜索到。 要检查对象是否有某个属性，应该去用`property_exists()`。
 
## 2、测试
 
### 2.1 测试环境
 
| OS | PHP | PHPUnit |
| - | - | - | 
| MacOS 10.13.6 | PHP 7.2.7 (cli) | PHPUnit 6.5.7 | 
 
 
### 2.2 单元测试

```php
class issetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataArr
     */
    public function testName($arr)
    {
        $this->assertTrue(isset($arr['name']));
        $this->assertFalse(isset($arr['age']));
        $this->assertTrue(isset($arr['sex']));
        $this->assertTrue(array_key_exists('name', $arr));
        $this->assertTrue(array_key_exists('age', $arr));
        $this->assertTrue(array_key_exists('sex', $arr));
        $this->assertFalse(empty($arr['name']));
        $this->assertTrue(empty($arr['age']));
        $this->assertTrue(empty($arr['sex']));
    }

    public function dataArr()
    {
        return [
            [
                ['name' => 123, 'age' => null, 'sex' => 0]
            ]
        ];
    }
}

/*
PHPUnit 6.5.7 by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: 113 ms, Memory: 8.00MB

OK (1 test, 9 assertions)
*/
```
 
### 2.3 性能-执行时间
 
如上,`php cli`环境下, 执行`10000000`次, 测试代码和执行时间如下:

```php
<?php

$arr = [
    'name' => 123,
    'age' => null
];

$max = 10000000;

testFunc($arr, 'name', $max);
testFunc($arr, 'age', $max);

function testFunc($arr, $key, $max = 1000)
{
    echo '`$arr[\'', $key, '\']` | - | -', PHP_EOL;

    $startTime = microtime(true);
    for ($i = 0; $i <= $max; $i++) {
        isset($arr[$key]);
    }
    echo '^ | isset |  ', microtime(true) - $startTime, PHP_EOL;

    $startTime = microtime(true);
    for ($i = 0; $i <= $max; $i++) {
        array_key_exists($key, $arr);
    }
    echo '^ | array_key_exists | ', microtime(true) - $startTime, PHP_EOL;

    $startTime = microtime(true);
    for ($i = 0; $i <= $max; $i++) {
        isset($arr[$key]) || array_key_exists($key, $arr);
    }
    echo '^ | isset or array_key_exists | ', microtime(true) - $startTime, PHP_EOL;
}
```
 
PHP 5.6 -|函数|执行时间(s) 
---|---|---
`$arr['name']`| - | - 
^ | isset | 0.64719796180725 
^ | array_key_exists | 2.5713651180267 
^ | isset or array_key_exists | 1.1359150409698
`$arr['age']`| - | - 
^ | isset | 0.53988218307495 
^ | array_key_exists | 2.7240340709686 
^ | isset or array_key_exists | 2.9613540172577
 
PHP 7.2.4 -|函数|执行时间(s) 
---|---|---
`$arr['name']`| - | - 
^ | isset | 0.24308800697327 
^ | array_key_exists | 0.3645191192627 
^ | isset or array_key_exists | 0.28933310508728
`$arr['age']`| - | - 
^ | isset | 0.23279714584351 
^ | array_key_exists | 0.33850502967834 
^ | isset or array_key_exists | 0.54935812950134
 
### 2.4 性能-使用VLD查看opcode

```
/usr/local/Cellar/php/7.2.7/bin/php -d vld.active=1 -dvld.verbosity=3 vld.php
```
 
| 描述 | isset | array_key_exists |
| - | - | - | 
| code | `$arr = ['name' => 'li']; isset($arr['name']);` | `$arr = ['name' => 'li']; array_key_exists('name', $arr);` | 
| -dvld.active=1 | ![][0] | ![][1] | 
| -dvld.verbosity=3 | ![][2] | ![][3] | 
 
 
## 3、源码
 
### 3.1 isset 源码分析
 
#### Zend/zend_language_scanner.l (Scanning阶段)
 
Scanning阶段，程序会扫描zend_language_scanner.l文件将代码文件转换成语言片段。

```c
<ST_IN_SCRIPTING>"isset" {
	RETURN_TOKEN(T_ISSET);
}
```
 
可见 isset 生成对应的token为 T_ISSET
 
#### 3.1.2 Zend/zend_language_parser.y (Parsing阶段)
 
当执行PHP源码，会先进行语法分析，isset的yacc如下： 接下来就到了Parsing阶段，这个阶段，程序将`T_ISSET`等Tokens转换成有意义的表达式，此时会做语法分析，Tokens的yacc保存在`zend_language_parser.y`文件中。`isset`的yacc如下(`T_ISSET`)：

```c
internal_functions_in_yacc:
		T_ISSET '(' isset_variables ')' { $$ = $3; }
	|	T_EMPTY '(' expr ')' { $$ = zend_ast_create(ZEND_AST_EMPTY, $3); }
	|	T_INCLUDE expr
			{ $$ = zend_ast_create_ex(ZEND_AST_INCLUDE_OR_EVAL, ZEND_INCLUDE, $2); }
	|	T_INCLUDE_ONCE expr
			{ $$ = zend_ast_create_ex(ZEND_AST_INCLUDE_OR_EVAL, ZEND_INCLUDE_ONCE, $2); }
	|	T_EVAL '(' expr ')'
			{ $$ = zend_ast_create_ex(ZEND_AST_INCLUDE_OR_EVAL, ZEND_EVAL, $3); }
	|	T_REQUIRE expr
			{ $$ = zend_ast_create_ex(ZEND_AST_INCLUDE_OR_EVAL, ZEND_REQUIRE, $2); }
	|	T_REQUIRE_ONCE expr
			{ $$ = zend_ast_create_ex(ZEND_AST_INCLUDE_OR_EVAL, ZEND_REQUIRE_ONCE, $2); }
;

isset_variables:
		isset_variable { $$ = $1; }
	|	isset_variables ',' isset_variable
			{ $$ = zend_ast_create(ZEND_AST_AND, $1, $3); }
;

isset_variable:
		expr { $$ = zend_ast_create(ZEND_AST_ISSET, $1); }
;

%%
```

```c
/* Zend/zend_ast.c */
# zend_ast_export_ex
case ZEND_AST_EMPTY:
	FUNC_OP("empty");
case ZEND_AST_ISSET:
	FUNC_OP("isset");
```
 
最终执行了`zend_ast_create(ZEND_AST_ISSET, $1);`我们知道, PHP7开始, 语法解析过程的产物保存于CG(AST)，接着zend引擎会把AST进一步编译为 zend_op_array ，它是编译阶段最终的产物，也是执行阶段的输入
 
#### 3.1.3 Zend/zend_compile.c(将表达式编译成opcodes)
 
将表达式编译成opcodes，可见`isset`对应的opcodes为`ZEND_AST_ISSET`。打开`zend_compile.c`文件

```c
# void zend_compile_expr(znode *result, zend_ast *ast) /* {{{ */
	
case ZEND_AST_ISSET:
case ZEND_AST_EMPTY:
	zend_compile_isset_or_empty(result, ast);
	return;
```
 
最终执行了`zend_compile_isset_or_empty`函数，在源码目录中查找, 可以发现，此函数也在 zend_compile.c 文件中定义。

```c
void zend_compile_isset_or_empty(znode *result, zend_ast *ast) /* {{{ */
{
	zend_ast *var_ast = ast->child[0];

	znode var_node;
	zend_op *opline = NULL;

	ZEND_ASSERT(ast->kind == ZEND_AST_ISSET || ast->kind == ZEND_AST_EMPTY);

	if (!zend_is_variable(var_ast) || zend_is_call(var_ast)) {
		if (ast->kind == ZEND_AST_EMPTY) {
			/* empty(expr) can be transformed to !expr */
			zend_ast *not_ast = zend_ast_create_ex(ZEND_AST_UNARY_OP, ZEND_BOOL_NOT, var_ast);
			zend_compile_expr(result, not_ast);
			return;
		} else {
			zend_error_noreturn(E_COMPILE_ERROR,
				"Cannot use isset() on the result of an expression "
				"(you can use \"null !== expression\" instead)");
		}
	}

	switch (var_ast->kind) {
		case ZEND_AST_VAR:
			if (is_this_fetch(var_ast)) {
				opline = zend_emit_op(result, ZEND_ISSET_ISEMPTY_THIS, NULL, NULL);
			} else if (zend_try_compile_cv(&var_node, var_ast) == SUCCESS) {
				opline = zend_emit_op(result, ZEND_ISSET_ISEMPTY_VAR, &var_node, NULL);
				opline->extended_value = ZEND_FETCH_LOCAL | ZEND_QUICK_SET;
			} else {
				opline = zend_compile_simple_var_no_cv(result, var_ast, BP_VAR_IS, 0);
				opline->opcode = ZEND_ISSET_ISEMPTY_VAR;
			}
			break;
		case ZEND_AST_DIM:
			opline = zend_compile_dim_common(result, var_ast, BP_VAR_IS);
			opline->opcode = ZEND_ISSET_ISEMPTY_DIM_OBJ;
			break;
		case ZEND_AST_PROP:
			opline = zend_compile_prop_common(result, var_ast, BP_VAR_IS);
			opline->opcode = ZEND_ISSET_ISEMPTY_PROP_OBJ;
			break;
		case ZEND_AST_STATIC_PROP:
			opline = zend_compile_static_prop_common(result, var_ast, BP_VAR_IS, 0);
			opline->opcode = ZEND_ISSET_ISEMPTY_STATIC_PROP;
			break;
		EMPTY_SWITCH_DEFAULT_CASE()
	}

	result->op_type = opline->result_type = IS_TMP_VAR;
	opline->extended_value |= ast->kind == ZEND_AST_ISSET ? ZEND_ISSET : ZEND_ISEMPTY;
}
/* }}} */
```
 
从这个函数最后一行可以看出，最终执行的还是`ZEND_ISSET`, 根据不同的用法会使用不同的opcode处理, 此处以`ZEND_ISSET_ISEMPTY_DIM_OBJ`为例。
 
#### 3.1.4 Zend/zend_vm_execute.h (执行opcodes)
 
opcode 对应处理函数的命名规律：
 
ZEND_[opcode] SPEC  (变量类型1)_(变量类型2)_HANDLER
 
变量类型1和变量类型2是可选的，如果同时存在，那就是左值和右值，归纳有下几类： VAR TMP CV UNUSED CONST 这样可以根据相关的执行场景来判定。

```c
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_CONST_CONST_HANDLER,
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_CONST_TMPVAR_HANDLER,
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_CONST_CV_HANDLER,
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_TMPVAR_CONST_HANDLER,
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_TMPVAR_TMPVAR_HANDLER,
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_TMPVAR_CV_HANDLER,
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_TMPVAR_CONST_HANDLER,
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_TMPVAR_TMPVAR_HANDLER,
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_TMPVAR_CV_HANDLER,
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_CV_CONST_HANDLER,
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_CV_TMPVAR_HANDLER,
zend_vm_execute.h: ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_CV_CV_HANDLER,
             
```
 
我们看下`ZEND_ISSET_ISEMPTY_DIM_OBJ_SPEC_CV_CV_HANDLER`这个处理函数

```c
if (opline->extended_value & ZEND_ISSET) {
	/* > IS_NULL means not IS_UNDEF and not IS_NULL */
	result = value != NULL && Z_TYPE_P(value) > IS_NULL &&
	    (!Z_ISREF_P(value) || Z_TYPE_P(Z_REFVAL_P(value)) != IS_NULL);
} else /* if (opline->extended_value & ZEND_ISEMPTY) */ {
	result = (value == NULL || !i_zend_is_true(value));
}
```
 
上面的 if ... else 就是判断是isset，还是empty，然后做不同处理，Z_TYPE_P, i_zend_is_true 不同判断。
 
可见，`isset`的最终实现是通过 Z_TYPE_P 获取变量类型，然后再进行判断的。
 
函数的完整定义请查看`Zend/zend_vm_execute.h`，以下是`i_zend_is_true`和`Z_TYPE_P`的定义：

 
* [Zend/zend_operators.h 中的i_zend_is_true()][6]
  
* [Zend/zend_types.h 中的 Z_TYPE_P()][7]

 
### 3.2 array_key_exists 源码分析
 
#### 3.2.1 ext/standard/array.c (数组扩展中实现)
 `array_key_exists`是php内置函数，通过扩展方式实现的。打开php源码，ext/standard/目录下

```
// ➜  standard git:(master) ✗ grep -r 'PHP_FUNCTION(array_key_exists)' *

array.c: PHP_FUNCTION(array_key_exists)
php_array.h: PHP_FUNCTION(array_key_exists);
```
 
具体实现如下：

```c
/* {{{ proto bool array_key_exists(mixed key, array search)
   Checks if the given key or index exists in the array */
PHP_FUNCTION(array_key_exists)
{
	zval *key;					/* key to check for */
	HashTable *array;			/* array to check in */

#ifndef FAST_ZPP
	if (zend_parse_parameters(ZEND_NUM_ARGS(), "zH", &key, &array) == FAILURE) {
		return;
	}
#else
	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_ZVAL(key)
		Z_PARAM_ARRAY_OR_OBJECT_HT(array)
	ZEND_PARSE_PARAMETERS_END();
#endif

	switch (Z_TYPE_P(key)) {
		case IS_STRING:
			if (zend_symtable_exists_ind(array, Z_STR_P(key))) {
				RETURN_TRUE;
			}
			RETURN_FALSE;
		case IS_LONG:
			if (zend_hash_index_exists(array, Z_LVAL_P(key))) {
				RETURN_TRUE;
			}
			RETURN_FALSE;
		case IS_NULL:
			if (zend_hash_exists_ind(array, ZSTR_EMPTY_ALLOC())) {
				RETURN_TRUE;
			}
			RETURN_FALSE;

		default:
			php_error_docref(NULL, E_WARNING, "The first argument should be either a string or an integer");
			RETURN_FALSE;
	}
}
/* }}} */
```
 
可以看到, 是通过`Z_TYPE_P`宏获取变量类型, 通过`zend_hash`相关函数判断`key`是否存在。以`key`为字符串为例，在`Zend/zend_hash.h`追踪具体实现：
 
#### 3.2.2 Zend/zend_hash.h

```c
ZEND_API zval* ZEND_FASTCALL zend_hash_find(const HashTable *ht, zend_string *key);

...

static zend_always_inline int zend_symtable_exists_ind(HashTable *ht, zend_string *key)
{
	zend_ulong idx;

	if (ZEND_HANDLE_NUMERIC(key, idx)) {
		return zend_hash_index_exists(ht, idx);
	} else {
		return zend_hash_exists_ind(ht, key);
	}
}

static zend_always_inline int zend_hash_exists_ind(const HashTable *ht, zend_string *key)
{
	zval *zv;

	zv = zend_hash_find(ht, key);
	return zv && (Z_TYPE_P(zv) != IS_INDIRECT ||
			Z_TYPE_P(Z_INDIRECT_P(zv)) != IS_UNDEF);
}

```
 
再次先通过函数`ZEND_HANDLE_NUMERIC`对key做判断，看这个字符串是不是数字类型的, 当`key`为数字时执行`zend_hash_index_exists`, 实现如下:
 
#### 3.2.3 Zend/zend_hash.c
 
#### 3.2.3.1 zend_hash_index_exists()

```c
/**
 * 这里有一个宏HASH_FLAG_PACKED，为真就代表当前数组的key都是系统生成的，也就是说是按从0到1，2，3等等按序排列的，所以判读键为key的是否存在，直接检查arData数组中第idx个元素是否有定义就行了，这里不涉及什么hash查找，冲突解决等一系列问题。
 *  
 * 但如果HASH_FLAG_PACKED为假，那么肯定就需要先计算idx的hash值，找到key为idx的数据应该在arData的第几位才行。这就要通过函数zend_hash_index_find_bucket了。
 */
ZEND_API zend_bool ZEND_FASTCALL zend_hash_index_exists(const HashTable *ht, zend_ulong h)
{
	Bucket *p;

	IS_CONSISTENT(ht);

	if (ht->u.flags & HASH_FLAG_PACKED) {
		if (h < ht->nNumUsed) {
			if (Z_TYPE(ht->arData[h].val) != IS_UNDEF) {
				return 1;
			}
		}
		return 0;
	}

	p = zend_hash_index_find_bucket(ht, h);
	return p ? 1 : 0;
}
```
 
#### 3.2.3.2 zend_hash_find()
 
在`Zend/zend_hash.c`中有`zend_hash_find()`的实现, code如下:

```c
/*++-- zend_hash_find --++*/
/* Returns the hash table data if found and NULL if not. */
ZEND_API zval* ZEND_FASTCALL zend_hash_find(const HashTable *ht, zend_string *key)
{
	Bucket *p;

	IS_CONSISTENT(ht);

	p = zend_hash_find_bucket(ht, key);
	return p ? &p->val : NULL;
}
```
 
#### 3.2.3.3 zend_hash_index_find_bucket()

```c
static zend_always_inline Bucket *zend_hash_index_find_bucket(const HashTable *ht, zend_ulong h)
{
	uint32_t nIndex;
	uint32_t idx;
	Bucket *p, *arData;

	arData = ht->arData;
	nIndex = h | ht->nTableMask;
	idx = HT_HASH_EX(arData, nIndex);
	while (idx != HT_INVALID_IDX) {
		ZEND_ASSERT(idx < HT_IDX_TO_HASH(ht->nTableSize));
		p = HT_HASH_TO_BUCKET_EX(arData, idx);
		if (p->h == h && !p->key) {
			return p;
		}
		idx = Z_NEXT(p->val);
	}
	return NULL;
}
```
 
#### 3.2.3.4 zend_hash_find_bucket()

```c
static zend_always_inline Bucket *zend_hash_find_bucket(const HashTable *ht, zend_string *key)
{
	zend_ulong h;
	uint32_t nIndex;
	uint32_t idx;
	Bucket *p, *arData;

	h = zend_string_hash_val(key);
	arData = ht->arData;
	nIndex = h | ht->nTableMask;
	idx = HT_HASH_EX(arData, nIndex);
	while (EXPECTED(idx != HT_INVALID_IDX)) {
		p = HT_HASH_TO_BUCKET_EX(arData, idx);
		if (EXPECTED(p->key == key)) { /* check for the same interned string */
			return p;
		} else if (EXPECTED(p->h == h) &&
		     EXPECTED(p->key) &&
		     EXPECTED(ZSTR_LEN(p->key) == ZSTR_LEN(key)) &&
		     EXPECTED(memcmp(ZSTR_VAL(p->key), ZSTR_VAL(key), ZSTR_LEN(key)) == 0)) {
			return p;
		}
		idx = Z_NEXT(p->val);
	}
	return NULL;
}
```
 
这里需要明白一点，数字的哈希值就等于他本身，所以才有不计算h的哈希值，就执行h | ht->nTableMask。
 
然后处理一下冲突，最后得出key为idx的数据是否存在于数组中。
 
如果idx确确实实是字符串，那么思路更简单一点，最后通过zen_hash_find_bucket来判断是否存在，与上面zend_hash_index_find_bucket不同的是，函数中要先计算字符串key的哈希值，然后再执行h | ht->nTableMask。
 
如下,

```c
zend_symtable_exists_ind -->ZEND_HANDLE_NUMERIC{ZEND_HANDLE_NUMERIC}
    ZEND_HANDLE_NUMERIC --> zend_hash_index_exists
    ZEND_HANDLE_NUMERIC --> zend_hash_exists_ind
    zend_hash_index_exists-->zend_hash_index_find_bucket
    zend_hash_exists_ind-->zend_hash_find
    zend_hash_find-->zend_hash_find_bucket
```


[4]: https://link.juejin.im?target=http%3A%2F%2Fphp.net%2Fmanual%2Fzh%2Ffunction.isset.php
[5]: https://link.juejin.im?target=http%3A%2F%2Fphp.net%2Fmanual%2Fzh%2Ffunction.array-key-exists.php
[6]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fphp%2Fphp-src%2Fblob%2FPHP-7.2.4%2FZend%2Fzend_operators.h%23L290
[7]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fphp%2Fphp-src%2Fblob%2FPHP-7.2.4%2FZend%2Fzend_types.h%23L400
[0]: ../img/BjyeUzN.png
[1]: ../img/VvMrYnv.png
[2]: ../img/nuu6ze2.png
[3]: ../img/iyy2Yrj.png
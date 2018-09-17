## PHP/Composer是如何加载一个类的

来源：[https://zhuanlan.zhihu.com/p/37085149](https://zhuanlan.zhihu.com/p/37085149)

时间：发布于 2018-05-21

![][0]

`PHP/composer开发中，我们只需要require 'vendor/autoload.php'，然后就可以直接使用各种类了。那么这些类是如何加载的呢？其中有没有什么可以优化的点呢？`

## 概览

PHP/composer下，类的加载主要到如下部分（还没有包括各个部分的初始化逻辑）：

```
PHP中zend_lookup_class_ex
    |-> EG(class_table)
    |-> spl_autoload_call
        |-> Composer\Autoload\ClassLoader::loadClass
            |-> findFile
                |-> class map lookup
                |-> PSR-4 lookup
                |-> PSR-0 lookup
```

## PHP的类加载

首先，PHP在运行的时候，需要一个类，是通过`zend_lookup_class_ex`来找到这个类的相关信息的。

[`zend_lookup_class_ex`][2]查找类的主要逻辑如下（假设类名字放到变量lc_name中）：

```c

ZEND_API zend_class_entry *zend_lookup_class_ex(zend_string *name, const zval *key, int use_autoload) /* {{{ */
{
  // 1. 类名字转化为小写
    if (ZSTR_VAL(name)[0] == '\\') {
      lc_name = zend_string_alloc(ZSTR_LEN(name) - 1, 0);
      zend_str_tolower_copy(ZSTR_VAL(lc_name), ZSTR_VAL(name) + 1, ZSTR_LEN(name) - 1);
    } else {
      lc_name = zend_string_tolower(name);
    }

  // 2. 直接在class_table中查找
  ce = zend_hash_find_ptr(EG(class_table), lc_name);
  if (ce) {
    if (!key) {
      zend_string_release(lc_name);
    }
    return ce;
  }
  // 3. 如果没有autoload_func，则注册默认的__autoload
  if (!EG(autoload_func)) {
    zend_function *func = zend_hash_str_find_ptr(EG(function_table), ZEND_AUTOLOAD_FUNC_NAME, sizeof(ZEND_AUTOLOAD_FUNC_NAME) - 1);
    if (func) {
      EG(autoload_func) = func;
    } else {
      if (!key) {
        zend_string_release(lc_name);
      }
      return NULL;
    }

  }

  // 4. 加载ACLASS的过程中，又加载ACLASS，递归加载，直接找不到类
  if (zend_hash_add_empty_element(EG(in_autoload), lc_name) == NULL) {
    if (!key) {
      zend_string_release(lc_name);
    }
    return NULL;
  }

  // 5. 调用autoload_func
  ZVAL_STR_COPY(&fcall_info.function_name, EG(autoload_func)->common.function_name);
  fcall_info.symbol_table = NULL;

  zend_exception_save();
  if ((zend_call_function(&fcall_info, &fcall_cache) == SUCCESS) && !EG(exception)) {
    ce = zend_hash_find_ptr(EG(class_table), lc_name);
  }
  zend_exception_restore();

  if (!key) {
    zend_string_release(lc_name);
  }
  return ce;
}

```

* lc_name转化成小写（这说明PHP中类名字不区分大小写）
* 然后在EG(class_table)找，如果找到，直接返回（我们自己注册的类，扩展注册的类都是这样找到的）
* 然后查看EG(autoload_func) ，如果没有则将[__autoload][3]注册上（值得注意的是，如果注册了EG(autoload_func)，则不会走__autoload）
* 通过EG(in_autoload)判断是否递归加载了（EG(in_autoload)是一个栈，记载了那些类正在被autoload加载）
* 然后调用EG(autoload_func)，并返回类信息

## SPL扩展注册

刚刚可以看到，PHP只会调用EG(autoload_func)，根本没有什么SPL的事情，那么SPL是如何让PHP调用自己的类加机制的呢？

首先，我去找SPL扩展的MINIT过程，结果发现其中并没有相关的逻辑。

出乎我的意料，这个注册过程在`[spl_autoload_register][4]`中完成：

```c

PHP_FUNCTION(spl_autoload_register)
{
    // 已经将SPL注册到PHP了，且当前用户要注册到spl的autoload函数已经注册，则跳过
    if (SPL_G(autoload_functions) && zend_hash_exists(SPL_G(autoload_functions), lc_name)) {
      if (!Z_ISUNDEF(alfi.closure)) {
        Z_DELREF_P(&alfi.closure);
      }
      goto skip;
    }

    // 如果必要的话，初始化SPL_G(autoload_functions)
    if (!SPL_G(autoload_functions)) {
      ALLOC_HASHTABLE(SPL_G(autoload_functions));
      zend_hash_init(SPL_G(autoload_functions), 1, NULL, autoload_func_info_dtor, 0);
    }

    // 如果之前已经注册了spl_autoload，那就将spl_autoload转移到autoload_functions中
    spl_func_ptr = zend_hash_str_find_ptr(EG(function_table), "spl_autoload", sizeof("spl_autoload") - 1);
    if (EG(autoload_func) == spl_func_ptr) { /* registered already, so we insert that first */
      autoload_func_info spl_alfi;

      spl_alfi.func_ptr = spl_func_ptr;
      ZVAL_UNDEF(&spl_alfi.obj);
      ZVAL_UNDEF(&spl_alfi.closure);
      spl_alfi.ce = NULL;
      zend_hash_str_add_mem(SPL_G(autoload_functions), "spl_autoload", sizeof("spl_autoload") - 1,
          &spl_alfi, sizeof(autoload_func_info));
      if (prepend && SPL_G(autoload_functions)->nNumOfElements > 1) {
        /* Move the newly created element to the head of the hashtable */
        HT_MOVE_TAIL_TO_HEAD(SPL_G(autoload_functions));
      }
    }

    // 将用户要注册的函数，即lc_name，放到autoload_functions中
    if (zend_hash_add_mem(SPL_G(autoload_functions), lc_name, &alfi, sizeof(autoload_func_info)) == NULL) {
      if (obj_ptr && !(alfi.func_ptr->common.fn_flags & ZEND_ACC_STATIC)) {
        Z_DELREF(alfi.obj);
      }
      if (!Z_ISUNDEF(alfi.closure)) {
        Z_DELREF(alfi.closure);
      }
      if (UNEXPECTED(alfi.func_ptr->common.fn_flags & ZEND_ACC_CALL_VIA_TRAMPOLINE)) {
        zend_string_release(alfi.func_ptr->common.function_name);
        zend_free_trampoline(alfi.func_ptr);
      }
    }
    if (prepend && SPL_G(autoload_functions)->nNumOfElements > 1) {
      /* Move the newly created element to the head of the hashtable */
      HT_MOVE_TAIL_TO_HEAD(SPL_G(autoload_functions));
    }
skip:
    zend_string_release(lc_name);
  }

  // 根据autoload_functions的值，决定向PHP注册spl_autoload_call还是spl_autoload
  if (SPL_G(autoload_functions)) {
    EG(autoload_func) = zend_hash_str_find_ptr(EG(function_table), "spl_autoload_call", sizeof("spl_autoload_call") - 1);
  } else {
    EG(autoload_func) =	zend_hash_str_find_ptr(EG(function_table), "spl_autoload", sizeof("spl_autoload") - 1);
  }

  RETURN_TRUE;
}

```

在composer环境下，这个函数的功能就是，将用户的autoload函数放到SPL_G(autoload_functions)中，且将spl_autoload_call注册到PHP中。

这样，PHP在找一个类的时候，就会调用spl_autoload_call了。

## spl_autoload_call逻辑

`spl_autoload_call`的逻辑很简单：

```c

PHP_FUNCTION(spl_autoload_call)
{
  if (SPL_G(autoload_functions)) {
    HashPosition pos;
    zend_ulong num_idx;
    int l_autoload_running = SPL_G(autoload_running);
    SPL_G(autoload_running) = 1;
    lc_name = zend_string_alloc(Z_STRLEN_P(class_name), 0);
    zend_str_tolower_copy(ZSTR_VAL(lc_name), Z_STRVAL_P(class_name), Z_STRLEN_P(class_name));
    zend_hash_internal_pointer_reset_ex(SPL_G(autoload_functions), &pos);
    // 遍历之前注册的autoload_functions
    while (zend_hash_get_current_key_ex(SPL_G(autoload_functions), &func_name, &num_idx, &pos) == HASH_KEY_IS_STRING) {
      alfi = zend_hash_get_current_data_ptr_ex(SPL_G(autoload_functions), &pos);
      if (UNEXPECTED(alfi->func_ptr->common.fn_flags & ZEND_ACC_CALL_VIA_TRAMPOLINE)) {
        zend_function *copy = emalloc(sizeof(zend_op_array));

        memcpy(copy, alfi->func_ptr, sizeof(zend_op_array));
        copy->op_array.function_name = zend_string_copy(alfi->func_ptr->op_array.function_name);
        // 调用autoload_function
        zend_call_method(Z_ISUNDEF(alfi->obj)? NULL : &alfi->obj, alfi->ce, &copy, ZSTR_VAL(func_name), ZSTR_LEN(func_name), retval, 1, class_name, NULL);
      } else {
        zend_call_method(Z_ISUNDEF(alfi->obj)? NULL : &alfi->obj, alfi->ce, &alfi->func_ptr, ZSTR_VAL(func_name), ZSTR_LEN(func_name), retval, 1, class_name, NULL);
      }
      zend_exception_save();
      if (retval) {
        zval_ptr_dtor(retval);
        retval = NULL;
      }
      // 如果调用结束之后，能在class_table找到类，则返回
      if (zend_hash_exists(EG(class_table), lc_name)) {
        break;
      }
      zend_hash_move_forward_ex(SPL_G(autoload_functions), &pos);
    }
    zend_exception_restore();
    zend_string_free(lc_name);
    SPL_G(autoload_running) = l_autoload_running;
  } else {
    /* do not use or overwrite &EG(autoload_func) here */
    zend_call_method_with_1_params(NULL, NULL, NULL, "spl_autoload", NULL, class_name);
  }
}

```

* 判断SPL_G(autoload_functions)存在
* 依次调用autoload_functions
* 如果调用完成后，这个类存在了，那就返回

至此，SPL的部分已经讲完了。我们来看看composer做了什么。

## composer注册autoload

composer的autoload注册在 'vendor/autoload.php' 中完成，这个文件完成了两件事：

* include`vendor/composer/autoload_real.php`
* 调用`ComposerAutoloaderInit<rand_id>::getLoader()`

而`vendor/composer/autoload_real.php`仅仅定义了`ComposerAutoloaderInit<rand_id>`类和`composerRequire<rand_id>`函数。

``<rand_id>`是类似id一样的东西，确保要加载多个composer的autoload的时候不会冲突。`composerRequire<rand_id>`则是为了避免`ComposerAutoloader`require文件的时候，文件修改了`ComposerAutoloader`的东西。`

接下来我们关注下`ComposerAutoloaderInit<rand_id>::getLoader()`做了哪些事情。

这个类的loader只会初始化一次，第二次是直接返回已经存在的loader了：

```php

if (null !== self::$loader) {
    return self::$loader;
}

```

如果是第一次调用，先注册`['ComposerAutoloaderInit<rand_id>', 'loadClassLoader']`，然后new一个`\Composer\Autoload\ClassLoader`作为`$loader`，然后立马取消注册`loadClassLoader`。

也就是说`['ComposerAutoloaderInit<rand_id>', 'loadClassLoader']`的唯一作用就是加载`\Composer\Autoload\ClassLoader`。

接下来就是在`ComposerAutoloaderInit<rand_id>::getLoader()`初始刚刚拿到的`$loader`了：

```php

// autoload_namespaces.php里面放的是PSR-0
$map = require __DIR__ . '/autoload_namespaces.php';
foreach ($map as $namespace => $path) {
    $loader->set($namespace, $path);
}
// autoload_psr4.php里面放的是PSR-4注册的
$map = require __DIR__ . '/autoload_psr4.php';
foreach ($map as $namespace => $path) {
    $loader->setPsr4($namespace, $path);
}
// autoload_classmap.php放的是classmap注册的
$classMap = require __DIR__ . '/autoload_classmap.php';
if ($classMap) {
    $loader->addClassMap($classMap);
}
// ……
// 将[$loader, 'loadClass']注册到spl中
$loader->register(true);
// ……
// autoload_files.php是file声明的autoload
$includeFiles = require __DIR__ . '/autoload_files.php';
foreach ($includeFiles as $fileIdentifier => $file) {
    composerRequire32715bcfade9cdfcb6edf37194a34c36($fileIdentifier, $file);
}
return $loader;

```

* `autoload_namespaces.php`返回的是各个包里面声明的PSR-0加载规则，是一个数组。key为namespace，有可能为空字符串；value为路径的数组。
* $loader->set，如果$namespace/$prefix为空，直接放到$loader->fallbackDirsPsr0数组中。如果不为空，则放到`$loader->prefixesPsr0[$prefix[0]][$prefix]`中（这可能是为了减少PHP内部的hash表冲突，加快查找速度）。
* `autoload_psr4.php`返回的是各个包里面声明的PSR-4加载规则，是一个数组。key为namespace，有可能为空字符串；value为路径的数组。
* $loader->setPsr4，如果`$namespace/$prefix`为空，直接放到`$loader->fallbackDirsPsr4`数组中。如果不为空，则将`$namespace/$prefix`的长度放到`$loader->prefixLengthsPsr4[$prefix[0]][$prefix]`中，将路径放到`$loader->prefixDirsPsr4[$prefix]`中。
* `autoload_classmap.php`返回的是各个包里面声明的classmap加载规则，是一个数组。key为class全名，value为文件路径。（这个信息是composer扫描全部文件得到的）
* $loader->addClassMap，则将这些信息array_merge到$loader->classMap中。
* `autoload_files.php`返回的是各个包里面声明的file加载规则，是一个数组。key为每个文件的id/hash，value是每个文件的路径。
* 注意，autoload_files.php里面的文件，在`getLoader`中就已经被include了。

到这儿，我们的$loader已经初始化好了，而且也已经注册到SPL中了

## composer加载类

我们之前是将[$loader, 'loadClass']注册到了SPL中，那就看看它的逻辑吧：

```php

public function loadClass($class)
{
    if ($file = $this->findFile($class)) {
        includeFile($file);
        // 根据我们刚刚的分析，此处返回值是根本没有用
        return true;
    }
}

```

所以看下来，重点在findFile函数里面：

```php

public function findFile($class)
{
    // 通过classmap找这个类
    if (isset($this->classMap[$class])) {
        return $this->classMap[$class];
    }
    // 这里涉及到一个composer的性能优化：
    // https://getcomposer.org/doc/articles/autoloader-optimization.md#optimization-level-2-a-authoritative-class-maps
    if ($this->classMapAuthoritative || isset($this->missingClasses[$class])) {
        return false;
    }
    // 这里同样也涉及到性能优化：
    // https://getcomposer.org/doc/articles/autoloader-optimization.md#optimization-level-2-b-apcu-cache
    if (null !== $this->apcuPrefix) {
        $file = apcu_fetch($this->apcuPrefix.$class, $hit);
        if ($hit) {
            return $file;
        }
    }
    // 这个函数处理了PSR-0和PSR-4的加载规则
    $file = $this->findFileWithExtension($class, '.php');

    // ……
    return $file;
}

```

如果是classmap的加载规则，那就会在这儿加载成功。如果是PSR-0或者PSR-4，则需要看看findFileWithExtension的逻辑了：

```php

private function findFileWithExtension($class, $ext)
{
    // PSR-4 lookup
    $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;

    // $prefix不为空的PSR-4加载规则
    $first = $class[0];
    if (isset($this->prefixLengthsPsr4[$first])) {
        $subPath = $class;
        while (false !== $lastPos = strrpos($subPath, '\\')) {
            $subPath = substr($subPath, 0, $lastPos);
            $search = $subPath.'\\';
            if (isset($this->prefixDirsPsr4[$search])) {
                $pathEnd = DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $lastPos + 1);
                foreach ($this->prefixDirsPsr4[$search] as $dir) {
                    if (file_exists($file = $dir . $pathEnd)) {
                        return $file;
                    }
                }
            }
        }
    }

    // $prefix为空的PSR-4加载规则
    foreach ($this->fallbackDirsPsr4 as $dir) {
        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4)) {
            return $file;
        }
    }

    // PSR-0 lookup
    if (false !== $pos = strrpos($class, '\\')) {
        // namespaced class name
        $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
            . strtr(substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);
    } else {
        // PEAR-like class name
        $logicalPathPsr0 = strtr($class, '_', DIRECTORY_SEPARATOR) . $ext;
    }

    // $prefix不为空的PSR-0加载规则
    if (isset($this->prefixesPsr0[$first])) {
        foreach ($this->prefixesPsr0[$first] as $prefix => $dirs) {
            if (0 === strpos($class, $prefix)) {
                foreach ($dirs as $dir) {
                    if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
                        return $file;
                    }
                }
            }
        }
    }

    // $prefix为空的PSR-0加载规则
    foreach ($this->fallbackDirsPsr0 as $dir) {
        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
            return $file;
        }
    }

    // 从include path中找文件
    if ($this->useIncludePath && $file = stream_resolve_include_path($logicalPathPsr0)) {
        return $file;
    }

    return false;
}

```

* $prefix不为空的PSR-4加载规则:

* 比如类A\B\C，先找A\B\对应目录下面的C.php；再找A\对应目录下面的B\C.php；以此类推

* $prefix为空的PSR-4加载规则

* 如果找不到，那就在fallbackDirsPsr4下找A\B\C.php文件

* `$prefix`不为空的PSR-0加载规则

* PSR-0支持namespace和下划线分隔的类（PEAR-like class name）；这点对一些需要向namespace迁移的旧仓库很有用
* 对于类A\B\C或者A_B_C，先找A\B\对应目录下面的C.php；再找A\对应目录下面的B\C.php；以此类推

* $prefix为空的PSR-0加载规则

* 如果找不到，直接在prefixesPsr0中找A\B\C.php文件

* 如果还没有找到，在条件允许的状态下，可以到include path中找A\B\C.php文件

这样，composer就找到了这个类对应的文件，并且include了。

我的博客：

[Robert's Blog - Yet another developer.​www.robberphex.com][5]

[2]: https://link.zhihu.com/?target=https%3A//github.com/php/php-src/blob/php-7.0.30/Zend/zend_execute_API.c%23L949
[3]: https://link.zhihu.com/?target=https%3A//github.com/php/php-src/blob/php-7.0.30/Zend/zend_compile.h%23L976
[4]: https://link.zhihu.com/?target=https%3A//github.com/php/php-src/blob/php-7.0.30/ext/spl/php_spl.c%23L453
[5]: https://link.zhihu.com/?target=https%3A//www.robberphex.com/

[0]: https://pic2.zhimg.com/v2-0e8c85a2ef1bd1643ae831de364078ef_1200x500.jpg
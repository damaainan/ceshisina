## Yaf_Session

### 简介
Yaf_Session是Yaf对Session的包装, 实现了Iterator, ArrayAccess, Countable接口, 方便使用.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Session

```php
final Yaf_Session implements Iterator , ArrayAccess , Countable {
public static Yaf_Session getInstance ( void );
public Yaf_Session start ( void );
public mixed get ( string $name = NULL );
public boolean set ( string $name ,
mixed $value );
public mixed __get ( string $name );
public boolean __set ( string $name ,
mixed $value );
public boolean has ( string $name );
public boolean del ( string $name );
public boolean __isset ( string $name );
public boolean __unset ( string $name );
}
```
# 全面的防sql注入方法（转） 

[2015-11-20][0]

```php
    function inject_check($sql_str) {
        return eregi ( 'select|insert|and|or|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile', $sql_str );
    }
    function verify_id($id = null) {
        if (! $id) {
            exit ( '没有提交参数！' );
        } elseif (inject_check ( $id )) {
            exit ( '提交的参数非法！' );
        } elseif (! is_numeric ( $id )) {
            exit ( '提交的参数非法！' );
        }
        $id = intval ( $id );
    
        return $id;
    }
    function str_check($str) {
        if (! get_magic_quotes_gpc ()) {
            $str = addslashes ( $str ); // 进行过滤
        }
        $str = str_replace ( "_", "\_", $str );
        $str = str_replace ( "%", "\%", $str );
    
        return $str;
    }
    function post_check($post) {
        if (! get_magic_quotes_gpc ()) {
            $post = addslashes ( $post );
        }
        $post = str_replace ( "_", "\_", $post );
        $post = str_replace ( "%", "\%", $post );
        $post = nl2br ( $post );
        $post = htmlspecialchars ( $post );
    
        return $post;
    }
    
```
转自[http://www.phpddt.com/php/228.html][1]

[0]: https://www.jwlchina.cn/2015/11/20/全面的防sql注入方法（转）/
[1]: http://www.phpddt.com/php/228.html
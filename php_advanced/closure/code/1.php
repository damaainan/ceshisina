<?php
/*
Closure::bind

public static Closure Closure::bind ( Closure $closure , object $newthis [, mixed $newscope = 'static' ] )

参数说明：
closure
需要绑定的匿名函数。

newthis
需要绑定到匿名函数的对象，或者 NULL 创建未绑定的闭包。

newscope
想要绑定给闭包的类作用域，或者 'static' 表示不改变。如果传入一个对象，则使用这个对象的类型名。 类作用域用来决定在闭包中 $this 对象的 
私有、保护方法 的可见性。 The class scope to which associate the closure is to be associated, or 'static' to keep the current one. If an object is given, the type of the object will be used instead. This determines the visibility of protected and private methods of the bound object.
 */


class T {
    private function show()
    {
        echo "我是T里面的私有函数：show\n";
    }

    protected  function who()
    {
        echo "我是T里面的保护函数：who\n";
    }

    public function name()
    {
        echo "我是T里面的公共函数：name\n";
    }
}

$test = new T();

$func = Closure::bind(function(){
    $this->who();
    $this->name();
    $this->show();
}, $test, new T());

$func();
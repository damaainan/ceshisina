**`字节码缓存组件`** Zend Optimizer+ 现在更改名字为 **`Zend opcache`**了。且在php 5.5版本后，会集成到php的官方组件中，也就没有必要安装其他的APC,eAccelerator等了。。
APC与Opcache都是字节码缓存也就是，PHP在被编译的时候，首先会把php代码转换为字节码，字节码然后被执行。

但是在本地开发是，建议不要开启opcache，否则就得不到最新的值
### 策略模式
策略模式(Strategy)属于对象的行为模式。**其用意是针对一组算法，将每一个算法封装到具有共同接口的独立的类中，从而使得它们可以相互替换。** 策略模式使得算法可以在不影响到客户端的情况下发生变化。

**策略模式的重心不是如何实现算法，而是如何组织、调用这些算法，从而让程序结构更灵活，具有更好的维护性和扩展性。**

**策略模式一个很大的特点就是各个策略算法的平等性。** 对于一系列具体的策略算法，大家的地位是完全一样的，正因为这个平等性，才能实现算法之间可以相互替换。所有的策略算法在实现上也是相互独立的，相互之间是没有依赖的。

使用过Python的同学知道md5是这么用的：
``` python
# python md5
import hashlib

md5 = hashlib.md5()
md5.update('123'.encode('utf-8'))
md5.update('456'.encode('utf-8'))
print(md5.hexdigest())
```

我们使用PHP结合策略模式实现该流程。首先需要准备接口类：
``` php
namespace Yjc\Strategy;

interface IHashLib
{
    public function update($str);
    public function hexdigest();
}
```
然后实现一个md5算法：
``` php
namespace Yjc\Strategy;

class Md5 implements IHashLib
{
    private $str;

    public function update($str)
    {
        $this->str .= $str;
    }

    public function hexdigest()
    {
        return md5($this->str);
    }
}
```
实现一个sha1算法：
``` php
namespace Yjc\Strategy;

class Sha1 implements IHashLib
{
    private $str;

    public function update($str)
    {
        $this->str .= $str;
    }

    public function hexdigest()
    {
        return sha1($this->str);
    }
}
```

使用：
``` php
$md5 = new Md5();
$md5->update('123');
$md5->update('456');
echo $md5->hexdigest();
```
这里只是用作策略模式流程示例，实际你是不会这么用md5的。

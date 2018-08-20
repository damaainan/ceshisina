## 写Laravel测试代码(四)

来源：[https://segmentfault.com/a/1190000010551238](https://segmentfault.com/a/1190000010551238)

在写单元测试时，有时候需要测试`A class 的 protected or private method`，可以使用`Class Reflection`来做，`而不是去改成public，破坏封装`。

在`laravel 的 abstract TestCase class 中添加一个方法就行：`


```php
    /**
     * Call protected or private method of a class.
     *
     * @param object $object      instantiated object that we will run method on.
     * @param string $method_name method name to call
     * @param array  $parameters  array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invokeNonPublicMethod($object, string $method_name, ...$parameters)
    {
        $reflection = new ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($method_name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

```

然后在`test case`中这样写测试就行：

```php
final AccountTest extends TestCase
{
    public function testValue()
    {
        $account = new Account()
        
        // actual
        $values = $this->invokeNonPublicMethod($account, 'privateMethod', [1, 2, 3]);
        // $values = $this->invokeNonPublicMethod($account, 'protectedMethod', [2, 3, 4]);
        
        // assert
        ...
    }

}

```

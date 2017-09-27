# ThinkPHP快捷方法总结 

    发表于 2014-06-09   |   分类于  [后端开发][0]   |  | 阅读次数 : 16

ThinkPHP框架中，定义了许多的快捷方法，在这里总结下快捷方法。

#### A方法:

跨项目调用  
调用格式:A('[项目名://][分组名/]模块名')

    //A方法示例
    A('User')              //表示调用当前项目的User模块
    A('Admin://User')      //表示调用Admin项目的User模块
    A('Admin/User')        //表示调用Admin分组的User模块
    A('Admin://Tool/User') //表示调用Admin项目Tool分组的User模块

#### R方法：

调用一个模块的某它操作方法  
调用格式:R('[项目名://][分组名/]模块名/操作名',array('参数1','参数2'…))

    //R方法示例
    R('User/info')              //表示调用当前项目的User模块的info操作方法
    R('Admin/User/info')        //表示调用Admin分组的User模块的info操作方法
    R('Admin://Tool/User/info') //表示调用Admin项目Tool分组的User模块的info操作方法

#### B方法:

调用应用行为类库方法

#### C方法:

1)读取配置 => C('参数名称')//获取已经设置的参数值  
2)动态配置 => C('参数名称','新的参数值');

#### D方法:

实例化自定义模型

#### M方法:

实例化基础模型类 => M('user')->select(); 查询user表中所有的数据

#### I方法:

获取表单数据

#### F方法:

专门用于文件方式的快速缓存方法,。F方法只能用于缓存简单数据类型，不支持有效期和缓存对象

    //F方法示例
    //快速缓存Data数据，默认保存在DATA_PATH目录下面
    F('data',$Data);
    //快速缓存Data数据，保存到指定的目录
    F('data',$Data,TEMP_PATH);
    //获取缓存数据
    $Data = F('data');
    //删除缓存数据
    F('data',NULL);
    //在DATA_PATH目录下面缓存data数据，如果User子目录不存在，则自动创建
    F('User/data',$Data);

#### S方法:

为了简化缓存存取操作，ThinkPHP把所有的缓存机制统一成一个S方法来进行操作

    //S方法示例  
    // 使用data标识缓存$Data数据  
    S('data',$Data);  
    // 缓存$Data数据3600秒  
    S('data',$Data,3600);  
    // 获取缓存数据  
    $Data = S('data');  
    // 删除缓存数据  
    S('data',NULL);

  
#### U方法:

1)设置跳转链接 => 在Tpl下的Home下有文件test.html,跳转到该文件则: U('test');  
2)设置跳转方法 => 在testAction.class.php下有删除方法delete()，跳转该方法则: U('test/delete');

#### G方法:

用于记录和统计时间  
用法 G($start,$end=',$dec=4)  
参数 start（必须）：起始位置标识  
end（可选）：记录结束标记并统计时间  
dec（可选）：调试时间的统计精度，默认为小数点后4位  
返回值 如果end为空或者是一个浮点数， 无返回值。  
如果end是一个字符串，则返回从start到end位置的使用时间。

    //G方法示例  
    G('run');  
    $blog = D(“Blog”);  
    $blog->select();  
    echo G('run','end').'s';

  
#### L方法:

获取语言变量(国际化支持,可切换语言)

#### W方法:

调用Widget类库

[0]: /categories/后端开发/
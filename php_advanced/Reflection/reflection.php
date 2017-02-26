<?php
//反射查找插件
//
echo '<pre>';
 
//定义一个测试反射的类
class CQH
{
    public $name = 'cqh';
    private $country= 'china';
    const gender = 'man';
    public function say()
    {
        echo 'hello,world';
    }
    private function eat()
    {
        echo 'eat';
    }
    public static function drink()
    {
        echo 'drink';
    }
}
 
/*  //打印所有的反射接口
    Reflection::export(new ReflectionExtension('reflection')); */
 
/*  //反射创建所有的PHP类的导出结果,get_declared_classes可以获取所有已声明的类
    foreach(get_declared_classes() as $class)
    {
        Reflection::export(new ReflectionClass($class));
    } */
 
/*  //只反射用户自己定义的类
    foreach(get_declared_classes() as $class)
    {
        $reflectionClass = new ReflectionClass($class);
        if($reflectionClass->isUserDefined())
        {
            Reflection::export($reflectionClass);
        }
    } */
 
    /********************************使用反射查找插件********************************/
    //定义一个接口
    interface IPlugin
    {
        public static function getName();
    }
 
    //查到所有实现了IPlugin接口的类
    function findPlugins()
    {
        $plugins = array();
        foreach(get_declared_classes() as $class)
        {
            $reflectionClass = new ReflectionClass($class);
            if($reflectionClass->implementsInterface('IPlugin'))
            {
                $plugins[] = $reflectionClass;
            }
        }
        return $plugins;
    }
    //确定用于菜单的类的成员
    function computeMenu()
    {
        $menu = array();
        foreach(findPlugins() as $plugins)
        {
            $reflectionMethod = $plugins->getMethod('getMenuItems');
            if($reflectionMethod->isStatic())
            {
                $items = $reflectionMethod->invoke(null);
            }
            else
            {
                //如果这个方法不是静态的，我们需要一个实例
                $pluginsInstance = $plugins->newInstance();
                $items = $reflectionMethod->invoke($pluginsInstance);
            }
            $menu = array_merge($menu,$items);
        }
        return $menu;
    }
    //确定用于文章的侧边栏的类的成员
    function computeArticles()
    {
        $articles = array();
        foreach(findPlugins() as $plugin)
        {
            if($plugin->hasMethod('getArticles'))
            {
                $reflectionMethod = $plugin->getMethod('getArticles');
                if($reflectionMethod->isStatic())
                {
                    $items = $reflectionMethod->invoke(null);
                }
                else
                {
                    $pluginInstance = $plugin->newInstance();
                    $items = $reflectionMethod->invoke($pluginInstance);
                }
                $articles = array_merge($articles,$items);
            }
        }
        return $articles;
    }
    //确定侧边栏的的类的成员
    function computeSidebars()
    {
        $sidebars = array();
        foreach(findPlugins() as $plugin)
        {
            if($plugin->hasMethod('getSidebars'))
            {
                $reflectionMethod = $plugin->getMethod('getSidebars');
                if($reflectionMethod->isStatic())
                {
                    $items = $reflectionMethod->invoke(null);
                }
                else
                {
                    $pluginInstance = $plugin->newInstance();
                    $items = $reflectionMethod->invoke($pluginInstance);
                }
                $sidebars = array_merge($sidebars,$items);
            }
        }
        return $sidebars;
    }
    //创建一个实现了Iplugin接口的类
    class MyCoolPlugin implements IPlugin
    {
        public static function getName()
        {
            return 'MyCoolPlugin';
        }
 
        public static function getMenuItems()
        {
            //菜单项的数字索引数组
            return array(array(
                'description' => 'MyCoolPlugin',
                'link' => '/MyCoolPlugin'
            ));
        }
        public static function getArticles()
        {
            //文章的数字索引数组
            return array(array(
                'path' => './MyCoolPlugin',
                'title' => 'This is a really cool article',
                'text' => 'This article is cool because...'
            ));
        }
    }
 
    $menu = computeMenu();
    $sidebars = computeSidebars();
    $articles = computeArticles();
    print_r($menu);
    print_r($sidebars);
    print_r($articles);
 
    echo '</pre>';
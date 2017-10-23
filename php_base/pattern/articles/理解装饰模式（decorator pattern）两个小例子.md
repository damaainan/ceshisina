# 理解装饰模式（decorator pattern）两个小例子 

[分享][0] ⋅ [maimai][1] ⋅ 于 1年前 ⋅ 841 阅读 

装饰、包装，重点是方法返回的不是返回值而是一个对象。当然laravel的比这复杂，这里只是小例子（个人愚见）。

装饰模式是在不必改变原类文件和使用继承的情况下，动态地扩展一个对象的功能。它是通过创建一个包装对象，也就是装饰来包裹真实的对象。  
装饰的时候不必考虑先后顺序。这让组合对象变得灵活。

- - -

## Demo1 #[#][2]

```php
    <?php 
    interface QueryInterface {
        public function where($field, $opt, $value);
        public function leftJoin($tab, $field1, $opt, $field2);
        public function whereIn($field, $ids);
    }
    
    class Query implements QueryInterface {
        protected $sql;
        protected $queryObj;
    
        public function __construct() {
            $this->queryObj = $this;
        }
    
        public  function getSql() {
            return $this->sql;
        }
    
        public  function where($field, $opt, $value) {
            $this->queryObj->sql .= "AND $field $opt '$value'";
            return $this->queryObj;
        }
    
        public function leftJoin($tab, $field1, $opt, $field2) {
            $this->queryObj->sql .= " LEFT JOIN $tab ON $field1 $opt $field2";
            return $this->queryObj;
        }
        public function whereIn($field, $ids) {
            $this->queryObj->sql .= " AND in($ids)";
            return $this->queryObj;
        }
    
    }
    
    $queryObj = new Query;
    $queryObj->where('id', '>', '2')->whereIn('id', '3,4,5')->leftJoin('test', 'id', '=', 'test_id');
    echo $queryObj->getSql();
```
输出：

> AND id > '2' AND in(3,4,5) LEFT JOIN test ON id = test_id

- - -

## Demo2 #[#][3]

```php
    <?php 
    /**
     * 装饰模式
     * 文章要经过小编添加摘要、seo人员优化、广告部添加广告
     * 处理顺序可以换
     *
     */
    
    /**
     * 文章基础类
     */
    class ArtBase {
        protected $content;
        protected $artObj;
        public function __construct($content) {
            $this->content = $content;
        }
    
        //装饰文章
        public function decorator() {
            return $this->content;
        }
    }
    
    /**
     * 小编文章类
     * 
     */
    class BianArt extends ArtBase {
        public function __construct($artObj) {
            $this->artObj = $artObj;
        }
    
        public function decorator() {
            //echo '进来了..';
            return $this->content = $this->artObj->decorator() . "加上了摘要.";
        }
    }
    
    /**
     * SEO文章类
     * 
     */
    class SEOArt extends ArtBase {
       public function __construct($artObj) {
            $this->artObj = $artObj;
        }
    
        public function decorator() {
            return $this->content = $this->artObj->decorator() . "加上了seo.";
        }
    
    }
    
    /**
     * 广告文章类
     * 
     */
    class ADArt extends ArtBase {
       public function __construct($artObj) {
            $this->artObj = $artObj;
        }
    
        public function decorator() {
            return $this->content = $this->artObj->decorator() . "加上了广告.";
        }
    }
    
    //装饰模式做法↓
    $art = new ADArt(new SEOArt(new BianArt(new ArtBase('好好学习天天向上'))));
    echo $art->decorator();
```

输出

> 好好学习天天向上加上了摘要.加上了seo.加上了广告.

ADArt()里面的对象可以随意调换顺序，先加广告再加摘要，先加seo再加广告...

- - -

## End #[#][4]

Laravel框架设计其实运用了很多设计模式，最核心的模式是Ioc(Inversion of Contro 控制反转)  
搞技术的总是喜欢吓唬人弄一堆听得云里雾里的词汇显得很好像很牛逼的样子。。。  
然后让人不明觉厉。╮(╯▽╰)╭  
Ioc就是一个超级工厂用来放类，甚至闭包，用到某个类的时候需要事先注册绑定，当然这个类要符合接口规范（什么契约、约定、合同等名词，无非就是接口），然后就可以通过容器make出来，并且可以让一个类注入另一个类的实例，使得我们不用在一个类里面去new另一个类的实例吗，这对庞大的项目来说大大减少了维护难度和时间。  
一句话说就是：由外部（Ioc）负责其依赖需求(需要某个工具类)的行为就叫做控制反转原文：[http://blog.csdn.net/w786572258/article/details/52829481][5]

[0]: https://laravel-china.org/categories/5
[1]: https://laravel-china.org/users/6129
[2]: #Demo1-
[3]: #Demo2-
[4]: #End-
[5]: http://blog.csdn.net/w786572258/article/details/52829481
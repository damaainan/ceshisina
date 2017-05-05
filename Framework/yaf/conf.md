## 配置 yaf 框架环境

##### PHP 环境配置

安装 yaf 扩展，在 php.ini 中开启 yaf 使用命名空间

```
extension=yaf.so
yaf.use_namespace=1 ;开启命名空间
yaf.use_spl_autoload=1 ;开启自动加载
```

其他配置项（有系统配置项，脚本配置项之分）

##### 集成 smarty 模板引擎

1.下载好 Smarty
2.将 Smarty 文件夹放在 library 目录下面
3.在 Smarty 目录下面添加 Adapter.php，代码如下：

    <?php
    Yaf_Loader::import( "Smarty/Smarty.class.php");
    Yaf_Loader::import( "Smarty/sysplugins/smarty_internal_templatecompilerbase.php");
    Yaf_Loader::import( "Smarty/sysplugins/smarty_internal_templatelexer.php");
    Yaf_Loader::import( "Smarty/sysplugins/smarty_internal_templateparser.php");
    Yaf_Loader::import( "Smarty/sysplugins/smarty_internal_compilebase.php");
    Yaf_Loader::import( "Smarty/sysplugins/smarty_internal_write_file.php");
    
    class Smarty_Adapter implements Yaf_View_Interface
    {
        /**
         * Smarty object
         * @var Smarty
         */
        public $_smarty;
     
        /**
         * Constructor
         *
         * @param string $tmplPath
         * @param array $extraParams
         * @return void
         */
        public function __construct($tmplPath = null, $extraParams = array()) {
            $this->_smarty = new Smarty; 
     
            if (null !== $tmplPath) {
                $this->setScriptPath($tmplPath);
            }       
            // var_dump($extraParams);
            foreach ($extraParams as $key => $value) {
                $this->_smarty->$key = $value; 
            }       
        }
     
        /**
         * Return the template engine object
         *
         * @return Smarty
         */
        public function getEngine() {
            return $this->_smarty;
        }
     
        /**
         * Set the path to the templates
         *
         * @param string $path The directory to set as the path.
         * @return void
         */
        public function setScriptPath($path)
        {
            if (is_readable($path)) {
                $this->_smarty->template_dir = $path;
                return; 
            }       
     
            throw new Exception('Invalid path provided');
        }
     /**
         * Retrieve the current template directory
         *
         * @return string
         */
        public function getScriptPath()
        {
            return $this->_smarty->template_dir;
        }
    
        /**
         * Alias for setScriptPath
         *
         * @param string $path
         * @param string $prefix Unused
         * @return void
         */
        public function setBasePath($path, $prefix = 'Zend_View')
        {
            return $this->setScriptPath($path);
        }
    
        /**
         * Alias for setScriptPath
         *
         * @param string $path
         * @param string $prefix Unused
         * @return void
         */
        public function addBasePath($path, $prefix = 'Zend_View')
        {
            return $this->setScriptPath($path);
        }
    
        /**
         * Assign a variable to the template
         *
         * @param string $key The variable name.
         * @param mixed $val The variable value.
         * @return void
         */
        public function __set($key, $val)
        {
            $this->_smarty->assign($key, $val);
        }
    
        /**
         * Allows testing with empty() and isset() to work
         *
         * @param string $key
         * @return boolean
         */
        public function __isset($key)
        {
            return (null !== $this->_smarty->get_template_vars($key));
        }
        /**
         * Allows unset() on object properties to work
         *
         * @param string $key
         * @return void
         */
        public function __unset($key)
        {
            $this->_smarty->clear_assign($key);
        }
    
        /**
         * Assign variables to the template
         *
         * Allows setting a specific key to the specified value, OR passing
         * an array of key => value pairs to set en masse.
         *
         * @see __set()
         * @param string|array $spec The assignment strategy to use (key or
         * array of key => value pairs)
         * @param mixed $value (Optional) If assigning a named variable,
         * use this as the value.
         * @return void
         */
        public function assign($spec, $value = null) {
            if (is_array($spec)) {
                $this->_smarty->assign($spec);
                return;
            }
            $this->_smarty->assign($spec, $value);
        }
    
        /**
         * Clear all assigned variables
         *
         * Clears all variables assigned to Zend_View either via
         * {@link assign()} or property overloading
         * ({@link __get()}/{@link __set()}).
         *
         * @return void
         */
        public function clearVars() {
            $this->_smarty->clear_all_assign();
        }
    
        /**
         * Processes a template and returns the output.
         *
         * @param string $name The template to process.
         * @return string The output.
         */
        public function render($name, $value = NULL) {
            return $this->_smarty->fetch($name);
        }
    
        public function display($name, $value = NULL) {
            echo $this->_smarty->fetch($name);
        }
    
    }

  
4.在 application.ini 中添加配置。如下： 

    smarty.left_delimiter   = "<{"  
    smarty.right_delimiter  = "}>"  
    smarty.template_dir     = APP_PATH "/application/views/"
    smarty.compile_dir      = APP_PATH "/application/cache/compile"
    smarty.cache_dir        = APP_PATH "/application/cache/"

  
5.在入口文件中使用 Bootstrap.php 文件 ：

    $app->bootstrap()->run();

然后编辑Bootstrap.php文件。如下： 

```php 
/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
public function _initSmarty(\Yaf\Dispatcher $dispatcher){  
        $smarty = new Smarty_Adapter(null , \Yaf\Application::app()->getConfig()->smarty);  
        \Yaf\Dispatcher::getInstance()->setView($smarty);  
    } 
```


##### 集成 Eloquent 数据库映射


##### yaf 多模块


##### 集成 PHPUnit 单元测试
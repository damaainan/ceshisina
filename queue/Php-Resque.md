Php-Resque 是 [Resque][0] 的PHP语言实现版。

使用示例：

1、定义一个任务队列：

    // Required if redis is located elsewhere
    Resque::setBackend('localhost:6379');
    $args = array(
            'name' => 'Chris'
            );
    Resque::enqueue('default', 'My_Job', $args);

2、定义一个任务：

每个任务要定义一个class，并且要包含一个perform方法

    class My_Job {      
        public function setUp()  {       
        // ... 设置任务的运行环境     
        }        
        public function perform()  {       
        // .. Run job     }        
        public function tearDown()  {      // ... 删除任务运行环境     
        }  }

3、从任务队列中删除任务： 

    // Removes job class 'My_Job' of queue 'default'
    Resque::dequeue('default', ['My_Job']);
    // Removes job class 'My_Job' with Job ID '087df5819a790ac666c9608e2234b21e' of queue 'default'
    Resuque::dequeue('default', ['My_Job' => '087df5819a790ac666c9608e2234b21e']);
    // Removes job class 'My_Job' with arguments of queue 'default'
    Resque::dequeue('default', ['My_Job' => array('foo' => 1, 'bar' => 2)]);
    // Removes multiple jobs
    Resque::dequeue('default', ['My_Job', 'My_Job2']);

    // Removes all jobs of queue 'default' 
    Resque::dequeue('default');

[0]: http://www.oschina.net/p/resque
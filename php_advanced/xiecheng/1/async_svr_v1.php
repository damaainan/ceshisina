<?php  


///以同步方式书写异步代码！
  //代码解读：

// 为了便于说明问题，这里所有底层通讯基于UDP，省略了TCP的connect等繁琐细节
// AsyncServer为底层框架类，封装了网络通讯细节以及协程切换细节，通过socket进行coroutine绑定
// RequestHandler为业务处理函数，通过yield new AsyncTask()实现异步网络交互
class AsyncServer {  
    protected $handler;  
    protected $socket;  
    protected $tasks = [];  
  
    public function __construct($handler) {  
        $this->handler = $handler;  
  
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);  
        if(!$this->socket) {  
            die(socket_strerror(socket_last_error())."\n");  
        }  
        if (!socket_set_nonblock($this->socket)) {  
            die(socket_strerror(socket_last_error())."\n");  
        }  
        if(!socket_bind($this->socket, "0.0.0.0", 1234)) {  
            die(socket_strerror(socket_last_error())."\n");  
        }  
    }  
  
    public function Run() {  
        while (true) {  
            $reads = array($this->socket);  
            foreach ($this->tasks as list($socket)) {  
                $reads[] = $socket;  
            }  
            $writes = NULL;  
            $excepts= NULL;  
            if (!socket_select($reads, $writes, $excepts, 0, 1000)) {  
                continue;  
            }  
  
            foreach ($reads as $one) {  
                $len = socket_recvfrom($one, $data, 65535, 0, $ip, $port);  
                if (!$len) {  
                    //echo "socket_recvfrom fail.\n";  
                    continue;  
                }  
                if ($one == $this->socket) {  
                    //echo "[Run]request recvfrom succ. data=$data ip=$ip port=$port\n";  
                    $handler = $this->handler;  
                    $coroutine = $handler($one, $data, $len, $ip, $port);  
                    $task = $coroutine->current();  
                    //echo "[Run]AsyncTask recv. data=$task->data ip=$task->ip port=$task->port timeout=$task->timeout\n";  
                    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);  
                    if(!$socket) {  
                        //echo socket_strerror(socket_last_error())."\n";  
                        $coroutine->throw(new Exception(socket_strerror(socket_last_error()), socket_last_error()));  
                        continue;  
                    }  
                    if (!socket_set_nonblock($socket)) {  
                        //echo socket_strerror(socket_last_error())."\n";  
                        $coroutine->throw(new Exception(socket_strerror(socket_last_error()), socket_last_error()));  
                        continue;  
                    }  
                    socket_sendto($socket, $task->data, $task->len, 0, $task->ip, $task->port);  
                    $this->tasks[$socket] = [$socket, $coroutine];  
                } else {  
                    //echo "[Run]response recvfrom succ. data=$data ip=$ip port=$port\n";  
                    if (!isset($this->tasks[$one])) {  
                        //echo "no async_task found.\n";  
                    } else {  
                        list($socket, $coroutine) = $this->tasks[$one];  
                        unset($this->tasks[$one]);  
                        socket_close($socket);  
                        $coroutine->send(array($data, $len));  
                    }  
                }  
            }  
        }  
    }  
}  
  
class AsyncTask {  
    public $data;  
    public $len;  
    public $ip;  
    public $port;  
    public $timeout;  
  
    public function __construct($data, $len, $ip, $port, $timeout) {  
        $this->data = $data;  
        $this->len = $len;  
        $this->ip = $ip;  
        $this->port = $port;  
        $this->timeout = $timeout;  
    }  
}  
  
function RequestHandler($socket, $req_buf, $req_len, $ip, $port) {  
    //echo "[RequestHandler] before yield AsyncTask. REQ=$req_buf\n";  
    list($rsp_buf, $rsp_len) = (yield new AsyncTask($req_buf, $req_len, "127.0.0.1", 2345, 1000));  
    //echo "[RequestHandler] after yield AsyncTask. RSP=$rsp_buf\n";  
    socket_sendto($socket, $rsp_buf, $rsp_len, 0, $ip, $port);  
}  
  
$server = new AsyncServer(RequestHandler);  
$server->Run();  
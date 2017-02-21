<?php  
//第一版遗留问题：

// 异步网络交互的timeout未实现，仅预留了接口参数
// yield new AsyncTask()调用方式不够自然，略感别扭

  //代码解读：

// 借助PHP内置array能力，实现简单的“超时管理”，以毫秒为精度作为时间分片
// 封装AsyncSendRecv接口，调用形如yield AsyncSendRecv()，更加自然
// 添加Exception作为错误处理机制，添加ret_code亦可，仅为展示之用


class AsyncServer {  
    protected $handler;  
    protected $socket;  
    protected $tasks = [];  
    protected $timers = [];  
  
    public function __construct(callable $handler) {  
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
            $now = microtime(true) * 1000;  
            foreach ($this->timers as $time => $sockets) {  
                if ($time > $now) break;  
                foreach ($sockets as $one) {  
                    list($socket, $coroutine) = $this->tasks[$one];  
                    unset($this->tasks[$one]);  
                    socket_close($socket);  
                    $coroutine->throw(new Exception("Timeout"));  
                }  
                unset($this->timers[$time]);  
            }  
  
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
                    if (!$coroutine) {  
                        //echo "[Run]everything is done.\n";  
                        continue;  
                    }  
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
                    $deadline = $now + $task->timeout;  
                    $this->tasks[$socket] = [$socket, $coroutine, $deadline];  
                    $this->timers[$deadline][$socket] = $socket;  
                } else {  
                    //echo "[Run]response recvfrom succ. data=$data ip=$ip port=$port\n";  
                    list($socket, $coroutine, $deadline) = $this->tasks[$one];  
                    unset($this->tasks[$one]);  
                    unset($this->timers[$deadline][$one]);  
                    socket_close($socket);  
                    $coroutine->send(array($data, $len));  
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
  
function AsyncSendRecv($req_buf, $req_len, $ip, $port, $timeout) {  
    return new AsyncTask($req_buf, $req_len, $ip, $port, $timeout);  
}  
  
function RequestHandler($socket, $req_buf, $req_len, $ip, $port) {  
    //echo "[RequestHandler] before yield AsyncTask. REQ=$req_buf\n";  
    try {  
        list($rsp_buf, $rsp_len) = (yield AsyncSendRecv($req_buf, $req_len, "127.0.0.1", 2345, 3000));  
    } catch (Exception $ex) {  
        $rsp_buf = $ex->getMessage();  
        $rsp_len = strlen($rsp_buf);  
        //echo "[Exception]$rsp_buf\n";  
    }  
    //echo "[RequestHandler] after yield AsyncTask. RSP=$rsp_buf\n";  
    socket_sendto($socket, $rsp_buf, $rsp_len, 0, $ip, $port);  
}  
  
$server = new AsyncServer(RequestHandler);  
$server->Run();  
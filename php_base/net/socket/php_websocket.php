<?php
 
 
class Socket
{
    const BIND_NUM = 20;

    private $master;
    private $sockets = [];
    private $handshake = false; // 握手

    public function __construct($address, $port)
    {
        try {
            // 创建
            $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            // 参数
            socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_set_nonblock($this->master);
            // 绑定
            socket_bind($this->master, $address, $port);
            // 监听
            socket_listen($this->master, static::BIND_NUM);

            $this->sockets[] = $this->master;


            $pid = posix_getpid();
            // 输出
            $this->say("Server Started : " . date('Y-m-d H:i:s'));
            $this->say("Listening on   : " . $address . " port " . $port);
            $this->say("Pid   : " . $pid);
            $this->say("Master socket  : " . $this->master . PHP_EOL);
        } catch (\Exception $e) {
            $this->error();
        }


        while (true) {
            try {
                // 慢点
                usleep(200000);
                $this->doServer();
            } catch (\Exception $e) {
                $this->error();
            }
        }

    }

    /**
     * 开始服务
     */
    public function doServer()
    {
        $write = $except = NULL;
        socket_select($this->sockets, $write, $except, NULL);  //自动选择来消息的socket 如果是握手 自动选择主机

        foreach ($this->sockets as $socket) {
            // 主机
            if ($this->master == $socket) {
                $client = socket_accept($this->master);
                if ($client < 0) {
                    $this->notice("socket_accept() failed");
                    continue;
                } else {
                    $this->connect($client);
                }
            } else {
                // 非主机
                $bytes = socket_recv($socket, $buffer, 2048, 0);
                if ($bytes == 0) {
                    // 断开连接
                    $this->disConnect($socket);
                } else {
                    if (!$this->handshake) {
                        // 准备握手
                        $this->doHandShake($socket, $buffer);
                    } else {
                        // 发送消息
                        $buffer = $this->decode($buffer);
                        $buffer='server say:'.$buffer;
                        $this->send($socket, $buffer);
                    }
                }
            }
        }
    }

    /**
     * 连接
     *
     * @param $socket
     */
    public function connect($socket)
    {
        array_push($this->sockets, $socket);
        $this->say("\n" . $socket . " CONNECTED!");
        $this->say(date("Y-n-d H:i:s"));
    }

    /**
     * 断开连接
     *
     * @param $socket
     */
    public function disConnect($socket)
    {
        $index = array_search($socket, $this->sockets);
        socket_close($socket);
        $this->say($socket . " DISCONNECTED!");
        if ($index >= 0) {
            array_splice($this->sockets, $index, 1);
        }
    }

    /**
     * 握手
     *
     * @param $socket
     * @param $buffer
     * @return bool
     */
    function doHandShake($socket, $buffer)
    {
        $this->say("\nRequesting handshake...");
        $this->say($buffer);
        $key = '';
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $buffer, $match)) {
            $key = $match[1];
        }
        $this->say("Handshaking...");
        $upgrade = "HTTP/1.1 101 Switching Protocol\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: " . $this->calcKey($key) . "\r\n\r\n";  //必须以两个回车结尾
        $this->say($upgrade);
        socket_write($socket, $upgrade, strlen($upgrade));
        $this->handshake = true;
        $this->say($key);
        $this->say("Done handshaking...");
        return true;
    }

    /**
     * 基于websocket version 13
     *
     * @param $key
     * @return string
     */
    function calcKey($key)
    {
        $accept = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        return $accept;
    }

    /**
     * 解密
     *
     * @param $buffer
     * @return null|string
     */
    function decode($buffer)
    {
        $len = $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;

        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        } else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }

    /**
     * 发送消息
     *
     * @param $client
     * @param $msg
     */
    function send($client, $msg)
    {
        $this->say("> " . $msg);
        $msg = $this->frame($msg);
        socket_write($client, $msg, strlen($msg));
        $this->say("! " . strlen($msg));
    }

    /**
     * 数据帧
     *
     * @param $s
     * @return string
     */
    function frame($s)
    {
        $a = str_split($s, 125);
        if (count($a) == 1) {
            return "\x81" . chr(strlen($a[0])) . $a[0];
        }
        $ns = "";
        foreach ($a as $o) {
            $ns .= "\x81" . chr(strlen($o)) . $o;
        }
        return $ns;
    }

    /**
     * 标准输出
     *
     * @param string $msg
     */
    public function say($msg = "")
    {
        echo $msg . PHP_EOL;
    }

    /**
     * 异常错误输出
     */
    public function error()
    {
        $error = socket_last_error();
        $error_msg = socket_strerror($error);
        echo $error_msg . PHP_EOL;
    }

    /**
     * 普通错误输出
     *
     * @param string $notice
     */
    public function notice($notice = "")
    {
        echo $notice . PHP_EOL;
    }


}

new Socket('127.0.0.1', 9777);
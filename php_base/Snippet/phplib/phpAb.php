<?php
/**
 *
 * php实现的压测工具
 *
 * @author: rudy
 * @date: 2016/07/25
 */


class PHPAb {
    protected $concurrency = 1;
    protected $number = 1;

    protected $requestList = array();

    protected function getCurlObject($url,$postData=array(),$header=array()){
        $options = array();
        $url = trim($url);
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_TIMEOUT] = 10;
        $options[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36';
        $options[CURLOPT_RETURNTRANSFER] = true;
//        $options[CURLOPT_PROXY] = '127.0.0.1:8888';

        foreach($header as $key=>$value){
            $options[$key] =$value;
        }
        if(!empty($postData) && is_array($postData)){
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($postData);
        }
        if(stripos($url,'https') === 0){
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }
        $ch = curl_init();
        curl_setopt_array($ch,$options);

        return $ch;
    }

    protected function runOneRequest($request){
        $downloader = curl_multi_init();
        $startTime = time();
        $downloaderCounter = 0;
        $requestCounter = 0;
        $requestCompletedCounter = 0;
        $requestErrorCounter = 0;
        $lastErrorInfo = '';

        $printRateCounter = 0;
        
        echo "Benchmarking url: {$request[0]}\n";

        // 初始化下载器
        for($i=0; $i < $this->concurrency; $i++){
            $downloaderCounter ++;
            $requestCounter ++;
            $requestCurlObject = $this->getCurlObject($request[0],$request[1],$request[2]);
            curl_multi_add_handle($downloader,$requestCurlObject);
        }

        // 轮询
        do {
            while (($execrun = curl_multi_exec($downloader, $running)) == CURLM_CALL_MULTI_PERFORM) ;
            if ($execrun != CURLM_OK) {
                break;
            }
            
            // 一旦有一个请求完成，找出来，处理,因为curl底层是select，所以最大受限于1024
            while ($done = curl_multi_info_read($downloader,$rel))
            {
                // 从请求中获取信息、内容、错误
                $info = curl_getinfo($done['handle']);
                $output = curl_multi_getcontent($done['handle']);
                $error = curl_error($done['handle']);

                // 将请求结果保存,我这里是打印出来
//                print "一个请求下载完成!\n";
                
                if (!isset($info['http_code']) || $info['http_code'] != 200){
                    $requestErrorCounter ++;
                    $lastErrorInfo = "({$info['http_code']})".$error;
                }

                // 把请求已经完成了得 curl handle 删除
                curl_multi_remove_handle($downloader, $done['handle']);
                $downloaderCounter -- ;
                $requestCompletedCounter ++;
                $running = 1;
            }

            while ($requestCounter < $this->number){
                if ($requestCounter == $this->number){
                    break;
                }

                if ($downloaderCounter < $this->concurrency){
                    $downloaderCounter ++;
                    $requestCounter ++;
                    $requestCurlObject = $this->getCurlObject($request[0],$request[1],$request[2]);
                    curl_multi_add_handle($downloader,$requestCurlObject);
                }else{
                    break;
                }
            }



            if ((int)($requestCompletedCounter/$this->number*100) >= $printRateCounter*10){
                $completedRate = round($requestCompletedCounter/$this->number*100,2);
                $printRateCounter ++;
                echo "completed {$completedRate}%\n";
            }

            // 当没有数据的时候进行堵塞，把 CPU 使用权交出来，避免上面 do 死循环空跑数据导致 CPU 100%
            if ($running) {
                $rel = curl_multi_select($downloader, 1);
                if($rel == -1){
                    usleep(1000);
                }
            }

            if( $running == false){
                break;
            }
        } while (true);

        curl_multi_close($downloader);
        $endTime = time();
        $takeTime = $endTime - $startTime;
        $requestSec = $takeTime?(round($requestCounter/$takeTime,2)):$requestCounter;
        $oneRequestTime = (int)($takeTime/$requestCompletedCounter*1000);

        $completedRate = round($requestCompletedCounter/$this->number*100,2);
        echo "Completed {$completedRate}% requests \n";
        echo "Concurrency Level: {$this->concurrency}\n";
        echo "Time taken for tests: {$takeTime}\n";
        echo "Complete requests: {$requestCompletedCounter}\n";
        echo "Error requests: {$requestErrorCounter}\n";
        echo "Requests per second: {$requestSec}/sec\n";
        echo "Time taken for one request: {$oneRequestTime}ms\n";
        echo "Last error info: {$lastErrorInfo}\n";
    }

    public function __construct($number=1,$concurrency=1) {
        $this->concurrency = (int)$concurrency;
        $this->number = (int)$number;
        if ($this->number < $this->concurrency){
            $this->number = $this->concurrency;
        }
        if ($this->concurrency == 0){
            $this->concurrency = 1;
        }

        if ($this->number == 0){
            $this->number = $number;
        }
    }

    public function addRequest($url,$postData=array(),$header=array()){
        array_push($this->requestList,array($url,$postData,$header));
    }

    public function run(){
        if (empty($this->requestList)){
            exit("There is no request!\n");
        }
        foreach ($this->requestList as $request){
            echo "*************************************\n";
            $this->runOneRequest($request);
        }
    }
}

//测试
if(strtolower(PHP_SAPI) == 'cli' && isset($argv) && basename(__FILE__) == basename($argv[0])){
    $ab = new PHPAb(5000,100);
    $ab->addRequest('http://www.jianshu.com/users/5a327aab786a/latest_articles');
    $ab->addRequest('https://www.baidu.com');
    $ab->run();
}
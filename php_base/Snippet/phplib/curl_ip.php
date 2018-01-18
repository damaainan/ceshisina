<?php 

function task() {
        $url = "url";
        $headers = randIp();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url); 
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);  
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_USERAGENT,  "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0");      //模拟浏览器类型
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);                               // 设置超时限制防止死循环    
        curl_setopt($curl, CURLOPT_HEADER, 0);                                  // 显示返回的Header区域内容    
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);                          // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) { 
            print "Error: " . curl_error($curl); 
        } else { 
            curl_close($curl); 
        } 
    }
//此函数提供了国内的IP地址
function randIP(){
       $ip_long = array(
           array('607649792', '608174079'), //36.56.0.0-36.63.255.255
           array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
           array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
           array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
           array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
           array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
           array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
           array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
           array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
           array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
       );
       $rand_key = mt_rand(0, 9);
       $ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
       $headers['CLIENT-IP'] = $ip; 
       $headers['X-FORWARDED-FOR'] = $ip; 

       $headerArr = array(); 
       foreach( $headers as $n => $v ) { 
           $headerArr[] = $n .':' . $v;  
       }
       return $headerArr;    
   }


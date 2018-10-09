使用 curl.sh  进行下载 需要伪装 refer 和 user-agent

    curl -e https://blog.csdn.net/juyin2015/article/details/79056687/ -A "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3251.0 Safari/537.36" https://img-blog.csdn.net/20180114212555461?watermark/2/text/aHR0cDovL2Jsb2cuY3Nkbi5uZXQvanV5aW4yMDE1/font/5a6L5L2T/fontsize/400/fill/I0JBQkFCMA==/dissolve/70/gravity/SouthEast -o 20180114212555461.png




    awk -F': ' '/img-blog/{print $2}' csdn79056687.md | awk -F'[/?]' '{system("curl -e https://blog.csdn.net/ -A \"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3251.0 Safari/537.36\" "$0" -o "$4".png")}'
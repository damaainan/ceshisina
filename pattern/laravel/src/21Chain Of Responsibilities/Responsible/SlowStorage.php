<?php
    
    namespace ChainOfResponsibilities\Responsible;
    
    use ChainOfResponsibilities\Handler;
    use ChainOfResponsibilities\Request;
    
    /**
     * 该类和FastStorage基本相同，但也有所不同
     *
     * 责任链模式的一个重要特性是: 责任链中的每个处理器都不知道自己在责任链中的位置，
     * 如果请求没有被处理，那么责任链也就不能被称作责任链，除非在请求到达的时候抛出异常
     *
     * 为了实现真正的扩展性，每一个处理器都不知道后面是否还有处理器
     *
     */
    class SlowStorage extends Handler
    {
        /**
         * @var array
         */
        protected $data = array();
    
        /**
         * @param array $data
         */
        public function __construct($data = array())
        {
            $this->data = $data;
        }
    
        protected function processing(Request $req)
        {
            if ('get' === $req->verb) {
                if (array_key_exists($req->key, $this->data)) {
                    $req->response = $this->data[$req->key];
                    return true;
                }
            }
    
            return false;
        }
    }
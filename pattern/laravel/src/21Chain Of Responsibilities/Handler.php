<?php
    
    namespace ChainOfResponsibilities;
    
    /**
     * 责任链的通用处理器类Handler（通常是一个接口或抽象类）
     *
     * 当然你可以通过一个更简单的处理器实现更加轻量级的责任链，
     * 但是如果你想让你的责任链拥有更好的扩展性和松耦合，
     * 那么就需要模拟一个更加真实的场景：通常一个责任链每时每刻都会被修改，
     * 这也是为什么我们在这里将其切分成好几个部分来完成。
     */
    abstract class Handler
    {
        /**
         * @var Handler
         */
        private $successor = null;
    
        /**
         * 追加处理类到责任链
         * 通过这个方法可以追加多个处理类到责任链
         *
         * @param Handler $handler
         */
        final public function append(Handler $handler)
        {
            if (is_null($this->successor)) {
                $this->successor = $handler;
            } else {
                $this->successor->append($handler);
            }
        }
    
        /**
         * 处理请求
         *
         * 这里我们使用模板方法模式以确保每个子类都不会忘记调用successor
         * 此外，返回的布尔值表明请求是否被处理
         * 
         * @param Request $req
         *
         * @return bool
         */
        final public function handle(Request $req)
        {
            $req->forDebugOnly = get_called_class();
            $processed = $this->processing($req);
            if (!$processed) {
                // the request has not been processed by this handler => see the next
                if (!is_null($this->successor)) {
                    $processed = $this->successor->handle($req);
                }
            }
    
            return $processed;
        }
    
        /**
         * 每个处理器具体实现类都要实现这个方法对请求进行处理
         *
         * @param Request $req
         *
         * @return bool true if the request has been processed
         */
        abstract protected function processing(Request $req);
    }
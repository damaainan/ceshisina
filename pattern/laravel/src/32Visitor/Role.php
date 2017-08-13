<?php
    
    namespace Visitor;
    
    /**
     * Role 类
     */
    abstract class Role
    {
        /**
         * 该方法基于Visitor的类名判断调用Visitor的方法
         *
         * 如果必须调用其它方法，重写本方法即可
         *
         * @param \Visitor\RoleVisitorInterface $visitor
         *
         * @throws \InvalidArgumentException
         */
        public function accept(RoleVisitorInterface $visitor)
        {
            $klass = get_called_class();
            preg_match('#([^\\\\]+)$#', $klass, $extract);
            $visitingMethod = 'visit' . $extract[1];
    
            if (!method_exists(__NAMESPACE__ . '\RoleVisitorInterface', $visitingMethod)) {
                throw new \InvalidArgumentException("The visitor you provide cannot visit a $klass instance");
            }
    
            call_user_func(array($visitor, $visitingMethod), $this);
        }
    }
<?php
    
    namespace Visitor;
    
    /**
     * Visitor接口的具体实现
     */
    class RolePrintVisitor implements RoleVisitorInterface
    {
        /**
         * {@inheritdoc}
         */
        public function visitGroup(Group $role)
        {
            echo "Role: " . $role->getName();
        }
    
        /**
         * {@inheritdoc}
         */
        public function visitUser(User $role)
        {
            echo "Role: " . $role->getName();
        }
    }
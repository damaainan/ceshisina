<?php
class HashableObject implements \Ds\Hashable
{
    private $name;
    private $email;

    public function __construct($name, $email)
    {
        $this->name  = $name;
        $this->email = $email;
    }

    /**
     * Should return the same value for all equal objects, but doesn't have to
     * be unique. This value will not be used to determine equality.
     */
    public function hash()
    {
        return $this->email;
    }

    /**
     * This determines equality, usually during a hash table lookup to determine
     * if the bucket's key matches the lookup key. The hash has to be equal if
     * the objects are equal, otherwise this determination wouldn't be reached.
     */
    public function equals($obj): bool
    {
        return $this->name  === $obj->name
            && $this->email === $obj->email;
    }
}

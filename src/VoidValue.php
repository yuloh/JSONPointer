<?php

namespace gamringer\JSONPointer;

class VoidValue
{
    protected $owner;
    protected $target;

    public function __construct(&$owner = null, $target = null)
    {
        $this->owner = $owner;
        $this->target = $target;
    }

    public function &getOwner()
    {
        return $this->owner;
    }

    public function getTarget()
    {
        return $this->target;
    }
}

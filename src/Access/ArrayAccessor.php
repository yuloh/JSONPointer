<?php

namespace gamringer\JSONPointer\Access;

use gamringer\JSONPointer\VoidValue;

class ArrayAccessor implements Accesses
{
    public function &getValue(&$target, $token)
    {
        $pointedValue = new VoidValue();
        
        if ($this->hasValue($target, $token)) {
            $pointedValue = &$target[$token];
        }
        
        return $pointedValue;
    }
    
    public function &setValue(&$target, $token, &$value)
    {
        $target[$token] = &$value;
        
        return $this->getValue($target, $token);
    }

    public function unsetValue(&$target, $token)
    {
        unset($target[$token]);
    }
    
    public function hasValue(&$target, $token)
    {
        return array_key_exists($token, $target);
    }
}

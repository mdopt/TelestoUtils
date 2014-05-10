<?php

namespace Telesto\Utils\Arrays\Factories;

use Telesto\Utils\ArrayUtil;

class PrototypeFactory implements Factory
{
    /**
     * @var array|\ArrayPrototype
     */
    protected $prototype;
    
    /**
     * @param   array|\ArrayAccess  $prototype
     */
    public function __construct($prototype)
    {
        ArrayUtil::ensureArrayOrArrayAccess($prototype, '$prototype');
        
        $this->prototype = $prototype;
    }
    
    /**
     * @return array|\ArrayAccess
     */
    public function createArray()
    {
        return is_object($this->prototype)? clone $this->prototype : $this->prototype;
    }
}

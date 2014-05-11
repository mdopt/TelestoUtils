<?php

namespace Telesto\Utils\Arrays;

/**
 * Enum-like interface for return modes of elements of an array
 */
interface ReturnMode
{
    /**
     * Return only element of an array
     */
    const ELEMENT_ONLY  = 0;
    
    /**
     * Return only bool containing info whether element was found
     */
    const EXISTS_ONLY   = 1;
    
    /**
     * Return both in form of an array: [element, exists]
     *
     * Can be useful when you want to know if the returned element really 
     * exists in the array or the default value was returned
     */
    const BOTH          = 2;
}

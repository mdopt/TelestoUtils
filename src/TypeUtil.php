<?php

namespace Telesto\Utils;

use InvalidArgumentException;
use RuntimeException;

abstract class TypeUtil
{
    /**
     * Tells if a class or an interface with given name exists.
     *
     * @param   string      $typeName       Name of a class or an interface
     *
     * @return  bool
     *
     * @throws  InvalidArgumentException    if $typeName is not a string
     */
    public static function objectTypeExists($typeName)
    {
        if (!is_string($typeName)) {
            throw new InvalidArgumentException(sprintf('Object type name must be a string, %s given.', gettype($typeName)));
        }
        
        return (
            class_exists($typeName)
            || interface_exists($typeName)
        );
    }
    
    /**
     * @param   mixed   $input
     *
     * @return  string
     */
    public static function getType($input)
    {
        return is_object($input)? get_class($input) : gettype($input);
    }
}

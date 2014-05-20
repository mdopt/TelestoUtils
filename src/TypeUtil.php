<?php

namespace Telesto\Utils;

use InvalidArgumentException;
use RuntimeException;

abstract class TypeUtil
{
    /**
     * Types that can be checked easily with is_* function
     */
    protected static $basicTypes = array(
        'bool', 'int', 'integer', 'float', 'string', 'array', 'scalar',
        'null', 'object', 'callable', 'resource'
    );
    
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
    
    /**
     * @param   mixed       $input
     * @param   string      $type       Type name or type names separated by '|'
     *                                  Example 'string|null'
     * @return  bool
     */
    public static function isType($input, $type)
    {
        $allowedTypes = explode('|', $type);
        $isObject = is_object($input);
        
        foreach ($allowedTypes as $allowedType) {
            $typeTest = in_array($allowedType, static::$basicTypes, true)? 'is_' . $allowedType : null;
            
            if ($typeTest && call_user_func($typeTest, $input)) {
                return true;
            }
            else if ($isObject && is_a($input, $allowedType)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param   mixed       $input
     * @param   string      $type           Type name or type names separated by '|'
     *                                      Example 'string|null'
     *
     * @param   string      $argumentName
     * @param   array       $options
     *
     * @return  void
     *
     * @throws  InvalidArgumentException    by default
     * @throws  Exception                   any other type of exception if specified in $options
     */
    public static function requireType($input, $type, $argumentName, array $options = array())
    {
        if (static::isType($input, $type)) {
            return;
        }
        
        $actualType     = static::getType($input);
        $exceptionClass = isset($options['exceptionClass'])? $options['exceptionClass'] : 'InvalidArgumentException';
        $messageFormat  = isset($options['messageFormat'])? $options['messageFormat'] : 'Invalid argument type for %1$s: %2$s expected, got %3$s.';
        $code           = isset($options['exceptionCode'])? $options['exceptionCode'] : 0;
        
        throw new $exceptionClass(
            sprintf($messageFormat, $argumentName, $type, $actualType),
            $code
        );
    }
}

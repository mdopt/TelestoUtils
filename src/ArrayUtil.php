<?php

namespace Telesto\Utils;

use Telesto\Utils\Arrays\ReturnMode;
use Telesto\Utils\Arrays\ValidationUtil;

use ArrayAccess;

use InvalidArgumentException;
use RuntimeException;
use LengthException;

abstract class ArrayUtil
{
    /**
     * $options:
     * - default                [mixed]     default: null.
     * - keySeparator           [string]    used only if $keyPath is a string, default: '.'
     * - throwOnNonExisting     [bool]      default: false
     * - returnMode             [int]       one of ReturnMode constants
     * - omitValidation         [bool]      default: false
     *
     * @param   array|ArrayAccess           $input
     * @param   string|array                $keyPath    'users.0.id' or ['users', '0', 'id']
     *                                                  returns $input['users']['0']['id']
     * @param   array                       $options
     *
     * @return  mixed
     *
     * @throws  LogicException              on invalid arguments
     * @throws  RuntimeException            when element does not exist
     *                                      and 'throwOnNonExisting' option is set to true
     */
    public static function getElementByKeyPath(
        $input,
        $keyPath,
        array $options = array()
    )
    {
        if (empty($options['omitValidation'])) {
            ValidationUtil::requireArrayOrArrayAccess($input, '$input');
            ValidationUtil::requireValidKeyPath($keyPath);
            ValidationUtil::requireValidOptions($options, array('keySeparator', 'returnMode'));
        }
        
        $throwOnNonExisting = !empty($options['throwOnNonExisting']);
        $defaultValue       = isset($options['default'])? $options['default'] : null;
        $returnMode         = isset($options['returnMode'])? $options['returnMode'] : ReturnMode::ELEMENT_ONLY;
        $keyPath            = static::getKeyPathAsArray($keyPath, $options);
        
        $visitedKeys = array();
        $value = &$input;
        
        foreach ($keyPath as $key) {
            $visitedKeys[] = $key;
            
            $isArrayOrArrayAccess = ValidationUtil::isArrayOrArrayAccess($value);
            $offsetExists = (
                $isArrayOrArrayAccess
                && (
                    is_array($value)? array_key_exists($key, $value) : $value->offsetExists($key) 
                )
            );
            
            if (!$offsetExists) {
                if ($throwOnNonExisting) {
                    throw new RuntimeException(sprintf('Element at %s does not exist.', json_encode($visitedKeys)));
                }
                else {
                    return static::getReturnValue(false, $defaultValue, $returnMode);
                }
            }
            
            $value = &$value[$key];
        }
        
        return static::getReturnValue(true, $value, $returnMode);
    }
    
    /**
     * $options:
     * - keySeparator           [string]    used only if $keyPath is a string, default: '.'
     * - omitValidation         [bool]      default: false
     *
     * @param   array|ArrayAccess           $input
     * @param   string|array                $keyPath
     * @param   array                       $options
     *
     * @return  bool
     *
     * @throws  LogicException              on invalid arguments
     */
    public static function hasElementAtKeyPath(
        $input,
        $keyPath,
        array $options = array()
    )
    {
        if (empty($options['omitValidation'])) {
            ValidationUtil::requireArrayOrArrayAccess($input, '$input');
            ValidationUtil::requireValidKeyPath($keyPath);
            ValidationUtil::requireValidOptions($options, array('keySeparator'));
        }
        
        $keyPath        = static::getKeyPathAsArray($keyPath, $options);
        $newOptions     = array(
            'returnMode'    => ReturnMode::EXISTS_ONLY,
            'omitValidation'=> true
        );
        
        return static::getElementByKeyPath($input, $keyPath, $newOptions);
    }
    
    /**
     * $options:
     * - keySeparator           [string]                see getElementByKeyPath
     * - throwOnCollision       [bool]                  default: false
     * - arrayPrototype         [array|ArrayAccess]     default: empty array
     * - omitValidation         [bool]                  default: false
     *
     * Collision is a situation in which the method wants to create new depth
     * in place of non-array type. Example:
     *
     * <code>
     *  $array = ['x'=> 10];
     *  ArrayUtil::setElementByKeyPath($array, 'x.y', 10);
     * </code>
     *
     * Array prototype is a value used to create new depth.
     *
     * @param   array|ArrayAccess           $input
     * @param   string|array                $keyPath
     * @param   mixed                       $element
     * @param   array                       $options
     *
     * @return  void
     *
     * @throws  LogicException              on invalid arguments
     * @throws  RuntimeException            when collision occurs
     *                                      and 'throwOnCollision' option is set to true
     */
    public static function setElementByKeyPath(
        &$input,
        $keyPath,
        $element,
        array $options = array()
    )
    {
        if (empty($options['omitValidation'])) {
            ValidationUtil::requireArrayOrArrayAccess($input, '$input');
            ValidationUtil::requireValidKeyPath($keyPath);
            ValidationUtil::requireValidOptions($options, array('keySeparator', 'arrayPrototype'));
        }
        
        $arrayPrototype     = isset($options['arrayPrototype'])? $options['arrayPrototype'] : array();
        $throwOnCollision   = !empty($options['throwOnCollision']);
        $keyPath            = static::getKeyPathAsArray($keyPath, $options);
        
        $keysCount = count($keyPath);
        $visitedKeys = array();
        $visitedKeysCount = 0;
        
        $value = &$input;
        
        foreach ($keyPath as $key) {
            $visitedKeys[] = $key;
            ++$visitedKeysCount;
            
            $isLastKey = ($visitedKeysCount === $keysCount);
            $offsetExists = is_array($value)? array_key_exists($key, $value) : $value->offsetExists($key);
            $isArrayOrArrayAccess = ($offsetExists && ValidationUtil::isArrayOrArrayAccess($value[$key]));
            
            $isCollision = (
                !$isLastKey &&
                $offsetExists &&
                !$isArrayOrArrayAccess
            );
            
            $makeNewDepth = (!$isLastKey && !$isArrayOrArrayAccess);
            
            if ($isCollision && $throwOnCollision) {
                throw new RuntimeException(
                    sprintf(
                        'Collision at %s: Element should not exist, be an array or an instance of ArrayAccess, %s given.',
                        json_encode($visitedKeys),
                        TypeUtil::getType($value[$key])
                    )
                );
            }
            
            if ($makeNewDepth) {
                $value[$key] = is_object($arrayPrototype)? clone $arrayPrototype : $arrayPrototype;
            }
            
            $value = &$value[$key];
        }
        
        $value = $element;
    }
    
    /**
     * $options:
     * - keySeparator           [string]    see getElementByKeyPath
     * - throwOnNonExisting     [bool]      see getElementByKeyPath
     * - omitValidation         [bool]      default: false
     *
     * @param   array|ArrayAccess           $input
     * @param   string|array                $keyPath
     * @param   array                       $options
     *
     * @return  void
     *
     * @throws  LogicException              on invalid arguments
     * @throws  RuntimeException            when element already does not exist
     *                                      and 'throwOnNonExisting' option is set to true
     */
    public static function unsetElementByKeyPath(
        &$input,
        $keyPath,
        array $options = array()
    )
    {
        if (empty($options['omitValidation'])) {
            ValidationUtil::requireArrayOrArrayAccess($input, '$input');
            ValidationUtil::requireValidKeyPath($keyPath);
            ValidationUtil::requireValidOptions($options, array('keySeparator'));
        }
        
        $throwOnNonExisting = !empty($options['throwOnNonExisting']);
        $keyPath            = static::getKeyPathAsArray($keyPath, $options);
        
        $keysCount = count($keyPath);
        $visitedKeys = array();
        $visitedKeysCount = 0;
        
        $value = &$input;
        
        foreach ($keyPath as $key) {
            $visitedKeys[] = $key;
            ++$visitedKeysCount;
            
            $isLastKey = ($visitedKeysCount === $keysCount);
            
            $isArrayOrArrayAccess = ValidationUtil::isArrayOrArrayAccess($value);
            $offsetExists = (
                $isArrayOrArrayAccess
                && (
                    is_array($value)? array_key_exists($key, $value) : $value->offsetExists($key)
                )
            );
            
            if (!$offsetExists) {
                if ($throwOnNonExisting) {
                    throw new RuntimeException(sprintf('Element at %s does not exist.', json_encode($visitedKeys)));
                }
                else {
                    return;
                }
            }
            
            if ($isLastKey) {
                unset($value[$key]);
            }
            else {
                $value = &$value[$key];
            }
        }
    }
    
    protected static function getReturnValue($exists, $element, $returnMode)
    {
        if ($returnMode === ReturnMode::ELEMENT_ONLY) {
            return $element;
        }
        else if ($returnMode === ReturnMode::EXISTS_ONLY) {
            return $exists;
        }
        
        return array($element, $exists);
    }
    
    protected static function getKeyPathAsArray($keyPath, array $options)
    {
        if (!is_array($keyPath)) {
            $keySeparator   = isset($options['keySeparator'])? $options['keySeparator'] : '.';
            $keyPath        = StringUtil::explode($keySeparator, $keyPath, null, array('escapeChar'=> '\\'));
        }
        
        return $keyPath;
    }
}

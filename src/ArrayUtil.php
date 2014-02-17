<?php

namespace Telesto\Utils;

use ArrayAccess;
use InvalidArgumentException;
use RuntimeException;

abstract class ArrayUtil
{
    /**
     * $options:
     * - default                [mixed]     default: null.
     * - keySeparator           [string]    used only if $pathKey is a string, default: '.'
     * - throwOnNonExisting     [bool]      default: false
     *
     * @param   array|ArrayAccess           $input
     * @param   string|array                $keyPath    'users.0.id' or ['users', '0', 'id']
     *                                                  returns $input['users']['0']['id']
     * @param   array                       $options
     *
     * @return  mixed
     *
     * @throws  InvalidArgumentException    on invalid arguments
     * @throws  RuntimeException            when element does not exist
     *                                      and 'throwOnNonExisting' option is set to true
     */
    public static function getElementByKeyPath(
        $input,
        $keyPath,
        array $options = array()
    )
    {
        static::validateArrayOrArrayAccess($input);
        static::validateKeyPath($keyPath);
        static::validateKeySeparatorOption($options);
        static::validateArrayPrototypeOption($options);
        
        $throwOnNonExisting = !empty($options['throwOnNonExisting']);
        $defaultValue = array_key_exists('default', $options)? $options['default'] : null;
        $keySeparator = array_key_exists('keySeparator', $options)? $options['keySeparator'] : '.';
        
        $keyPath = is_array($keyPath)? $keyPath : explode($keySeparator, $keyPath);
        
        $visitedKeys = array();
        $value = &$input;
        
        foreach ($keyPath as $key) {
            $visitedKeys[] = $key;
            
            $isArrayOrArrayAccess = static::isArrayOrArrayAccess($value);
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
                    return $defaultValue;
                }
            }
            
            $value = &$value[$key];
        }
        
        return $value;
    }
    
    /**
     * $options:
     * - keySeparator           [string]                see 'getElementByKeyPath'
     * - throwOnCollision       [bool]                  default: false
     * - arrayPrototype         [array|ArrayAccess]     default: empty array
     *
     * Collision is a situation in which the method wants to create new depth
     * in place of non-array type. Example:
     *
     * <code>
     *  $array = array('x'=> 10);
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
     * @throws  InvalidArgumentException    on invalid arguments
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
        static::validateArrayOrArrayAccess($input);
        static::validateKeyPath($keyPath);
        static::validateKeySeparatorOption($options);
        static::validateArrayPrototypeOption($options);
        
        $arrayPrototype = array_key_exists('arrayPrototype', $options)? $options['arrayPrototype'] : array();
        $throwOnCollision = !empty($options['throwOnCollision']);
        $keySeparator = array_key_exists('keySeparator', $options)? $options['keySeparator'] : '.';
        
        $keyPath = is_array($keyPath)? $keyPath : explode($keySeparator, $keyPath);
        
        $keysCount = count($keyPath);
        $visitedKeys = array();
        $visitedKeysCount = 0;
        
        $value = &$input;
        
        foreach ($keyPath as $key) {
            $visitedKeys[] = $key;
            ++$visitedKeysCount;
            
            $isLastKey = ($visitedKeysCount === $keysCount);
            $offsetExists = is_array($value)? array_key_exists($key, $value) : $value->offsetExists($key);
            $isArrayOrArrayAccess = ($offsetExists && static::isArrayOrArrayAccess($value[$key]));
            
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
     * - keySeparator           [string]    see 'getElementByKeyPath'
     * - throwOnNonExisting     [bool]      see 'getElementByKeyPath'
     *
     * @param   array|ArrayAccess           $input
     * @param   string|array                $keyPath
     * @param   array                       $options
     *
     * @return  void
     *
     * @throws  InvalidArgumentException    on invalid arguments
     * @throws  RuntimeException            when element already does not exist
     *                                      and 'throwOnNonExisting' option is set to true
     */
    public static function unsetElementByKeyPath(
        &$input,
        $keyPath,
        array $options = array()
    )
    {
        static::validateArrayOrArrayAccess($input);
        static::validateKeyPath($keyPath);
        static::validateKeySeparatorOption($options);
        
        $throwOnNonExisting = !empty($options['throwOnNonExisting']);
        $keySeparator = array_key_exists('keySeparator', $options)? $options['keySeparator'] : '.';
        
        $keyPath = is_array($keyPath)? $keyPath : explode($keySeparator, $keyPath);
        
        $keysCount = count($keyPath);
        $visitedKeys = array();
        $visitedKeysCount = 0;
        
        $value = &$input;
        
        foreach ($keyPath as $key) {
            $visitedKeys[] = $key;
            ++$visitedKeysCount;
            
            $isLastKey = ($visitedKeysCount === $keysCount);
            
            $isArrayOrArrayAccess = static::isArrayOrArrayAccess($value);
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
    
    /**
     * Transforms one array or ArrayAccess instance to another using a transformation map.
     *
     * Only values at requested keys are used, others are ignored.
     *
     * Values at destination keys can get overwritten or collision
     * might occur (see 'setElementByKeyPath')
     *
     * $options:
     * - default                [mixed]                 see 'getElementByKeyPath'
     * - keySeparator           [string]                see 'getElementByKeyPath'
     * - throwOnNonExisting     [bool]                  see 'getElementByKeyPath'
     * - throwOnCollision       [bool]                  see 'setElementByKeyPath'
     * - arrayPrototype         [array|ArrayAccess]     see 'setElementByKeyPath'
     *
     * @param   array|ArrayAccess           $input
     * @param   array                       $keyPathMap         a sourceKey => destinationKey array
     * @param   array                       $options
     *
     * @return  array|ArrayAccess
     *
     * @throws  InvalidArgumentException    on invalid arguments
     * @throws  RuntimeException
     */
    public static function transformByPathKeyMap(
        $input,
        array $keyPathMap,
        array $options = array()
    )
    {
        static::validateArrayPrototypeOption($options);
        $arrayPrototype = array_key_exists('arrayPrototype', $options)? $options['arrayPrototype'] : array();
        
        $output = is_object($arrayPrototype)? clone $arrayPrototype : $arrayPrototype;
        static::doCopyByKeyPathMap($input, $output, $keyPathMap, $options);
        
        return $output;
    }
    
    /**
     * Works similarly to 'transformByKeyPathMap', but copies values to
     * an existing array|ArrayAccess $output instead of returning new one.
     */
    public static function copyByKeyPathMap(
        $input,
        &$output,
        array $keyPathMap,
        array $options = array()
    )
    {
        if (!static::isArrayOrArrayAccess($output)) {
            throw new InvalidArgumentException(
                sprintf('Output must be an array or an instance of ArrayAccess, %s given.', TypeUtil::getType($output))
            );
        }
        
        static::doCopyByKeyPathMap($input, $output, $keyPathMap, $options);
    }
    
    protected static function doCopyByKeyPathMap(
        $input,
        &$output,
        array $keyPathMap,
        array $options = array()
    )
    {
        static::validateArrayOrArrayAccess($input);
        static::validateKeySeparatorOption($options);
        static::validateArrayPrototypeOption($options);
        
        if (count($keyPathMap) === 0) {
            throw new InvalidArgumentException('Path key map must have at least one element.');
        }
        
        $keySeparator = array_key_exists('keySeparator', $options)? $options['keySeparator'] : '.';
        $arrayPrototype = array_key_exists('arrayPrototype', $options)? $options['arrayPrototype'] : array();
        
        foreach ($keyPathMap as $inputKeyPath=> $outputKeyPath) {
            if (!is_string($outputKeyPath)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Key path map must be a string=> string array, invalid type %s at index \'%s\'.',
                        TypeUtil::getType($outputKeyPath),
                        $inputKeyPath
                    )
                );
            }
        }
        
        foreach ($keyPathMap as $inputKeyPath=> $outputKeyPath) {
            // string keys get casted to integers if they contain only numbers
            // array('1'=> 'something') becomes array(1=> 'something')
            $inputKeyPath = (string) $inputKeyPath;
            
            $element = static::getElementByKeyPath($input, $inputKeyPath, $options);
            static::setElementByKeyPath($output, $outputKeyPath, $element, $options);
        }
    }
    
    protected static function isArrayOrArrayAccess($input)
    {
        return (is_array($input) || ($input instanceof ArrayAccess));
    }

    protected static function validateArrayOrArrayAccess($input)
    {
        if (!static::isArrayOrArrayAccess($input)) {
            throw new InvalidArgumentException(
                sprintf('Input must be an array or an instance of ArrayAccess, %s given.', TypeUtil::getType($input))
            );
        }
    }
    
    protected static function validateKeyPath($keyPath)
    {
        if (is_array($keyPath)) {
            static::validateArrayKeyPath($keyPath);
        }
        else if (!is_string($keyPath)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Key path must be a string or an array, %s given.',
                    TypeUtil::getType($keyPath)
                )
            );
        }
    }
    
    protected static function validateArrayKeyPath(array $keyPath)
    {
        if (count($keyPath) === 0) {
            throw new InvalidArgumentException('At least one key must be given.');
        }
        
        foreach ($keyPath as $index=> $keyPart) {
            if (!is_string($keyPart) && !is_int($keyPart)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Array of keys must contain only strings and integers, %s given at index %s.',
                        TypeUtil::getType($keyPart),
                        $index
                    )
                );
            }
        }
    }
    
    protected static function validateKeySeparatorOption(array $options)
    {
        if (array_key_exists('keySeparator', $options) && !is_string($options['keySeparator'])) {
            throw new InvalidArgumentException(
                sprintf('Option \'keySeparator\' must be a string, %s given.', TypeUtil::getType($options['keySeparator']))
            );
        }
    }
    
    protected static function validateArrayPrototypeOption(array $options)
    {
        if (array_key_exists('arrayPrototype', $options) && !static::isArrayOrArrayAccess($options['arrayPrototype'])) {
            throw new InvalidArgumentException(
                sprintf('Option \'arrayPrototype\' must an be array or an instance of ArrayAccess, %s given.', TypeUtil::getType($options['arrayPrototype']))
            );
        }
    }
}

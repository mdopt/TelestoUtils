<?php

namespace Telesto\Utils;

use ArrayAccess;
use InvalidArgumentException;
use RuntimeException;

abstract class ArrayUtil
{
    /**
     * @param   array|ArrayAccess           $input
     * @param   string|array                $keyPath
     * @param   array                       $options
     *
     * @return  mixed
     *
     * @throws  InvalidArgumentException    on invalid arguments
     * @throws  RuntimeException            when element does not exist and $throwOnNonExisting is set to true
     */
    public static function getElementByKeyPath(
        $input,
        $keyPath,
        array $options = array()
    )
    {
        if (!static::isArrayOrArrayAccess($input)) {
            throw new InvalidArgumentException(
                sprintf('Input must be an array or an instance of ArrayAccess, %s given.', TypeUtil::getType($input))
            );
        }
        
        self::validateKeyPath($keyPath);
        
        if (array_key_exists('keySeparator', $options) && !is_string($options['keySeparator'])) {
            throw new InvalidArgumentException(
                sprintf('Option \'keySeparator\' must be a string, %s given.', TypeUtil::getType($options['keySeparator']))
            );
        }
        
        $throwOnNonExisting = !empty($options['throwOnNonExisting']);
        $defaultValue = array_key_exists('default', $options)? $options['default'] : null;
        $keySeparator = array_key_exists('keySeparator', $options)? $options['keySeparator'] : '.';
        
        $keyPath = is_array($keyPath)? $keyPath : explode($keySeparator, $keyPath);
        
        $visitedKeys = array();
        $value = &$input;
        
        foreach ($keyPath as $key) {
            $visitedKeys[] = $key;
            
            if (!static::isArrayOrArrayAccess($value) || !isset($value[$key])) {
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
     * Transforms one array or ArrayAccess instance to another using a transformation map and array prototype.
     *
     * Only values at requested keys are used, others are ignored.
     * Values will be overwritten if destination keys collide.
     *
     * @param   array|ArrayAccess           $input
     * @param   array                       $keyPathMap         a sourceKey => destinationKey array
     * @param   array                       $options
     *
     * @return  array|ArrayAccess
     *
     * @throws  InvalidArgumentException    on invalid arguments
     * @throws  RuntimeException            when element does not exist and $throwOnNonExisting is set to true
     */
    public static function transformByPathKeyMap(
        $input,
        array $keyPathMap,
        array $options = array()
    )
    {
        $arrayPrototype = array_key_exists('arrayPrototype', $options)? $options['arrayPrototype']: array();
        
        if (!static::isArrayOrArrayAccess($arrayPrototype)) {
            throw new InvalidArgumentException(
                sprintf('Option \'arrayPrototype\' must an be array or an instance of ArrayAccess, %s given.', TypeUtil::getType($arrayPrototype))
            );
        }
        
        if (count($keyPathMap) === 0) {
            throw new InvalidArgumentException('Path key map must have at least one element.');
        }
        
        $keySeparator = array_key_exists('keySeparator', $options)? $options['keySeparator'] : '.';
        
        foreach ($keyPathMap as $inputKeyPath=> $outputKeyPath) {
            if (!is_string($inputKeyPath)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Key path map must be a string=> string array, invalid key \'%s\'.',
                        $inputKeyPath
                    )
                );
            }
            
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
        
        // no need to validate $input and options other than 'arrayPrototype':
        // they will be validated on the first call of getElementByKeyPath
        
        $output = is_object($arrayPrototype)? clone $arrayPrototype : $arrayPrototype;
        
        foreach ($keyPathMap as $inputKeyPath=> $outputKeyPath) { 
            $element = static::getElementByKeyPath($input, $inputKeyPath, $options);
            
            $outputKeys = explode($keySeparator, $outputKeyPath);
            $lastOutputKey = $outputKeys[count($outputKeys) - 1];
            
            $outputValue = &$output;
            
            foreach ($outputKeys as $outputKey) {
                if ($outputKey !== $lastOutputKey && !isset($outputValue[$outputKey])) {
                    $outputValue[$outputKey] = is_object($arrayPrototype)? clone $arrayPrototype : $arrayPrototype;
                }
                
                $outputValue = &$outputValue[$outputKey];
            }
            
            $outputValue = $element;
        }
        
        return $output;
    }
    
    protected static function isArrayOrArrayAccess($input)
    {
        return (is_array($input) || ($input instanceof ArrayAccess));
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
}

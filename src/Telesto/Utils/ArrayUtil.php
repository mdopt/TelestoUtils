<?php

namespace Telesto\Utils;

abstract class ArrayUtil
{
    /**
     * @param   array|\ArrayAccess          $input
     * @param   string[]                    $keys
     * @param   mixed                       $defaultValue
     * @param   bool                        $throwOnNonExisting
     *
     * @return  mixed
     *
     * @throws  \InvalidArgumentException   on invalid arguments
     * @throws  \RuntimeException           when element does not exist and $throwOnNonExisting is set to true
     */
    public static function getElementByKeys(
        $input,
        array $keys,
        $defaultValue = null,
        $throwOnNonExisting = false
    )
    {
        if (!static::isArrayOrArrayAccess($input)) {
            throw new \InvalidArgumentException('Input must be an array or an instance of ArrayAccess.');
        }
        
        if (count($keys) === 0) {
            throw new \InvalidArgumentException('At least one key must be given.');
        }
        
        array_walk(
            $keys,
            function ($element) {
                if (!is_string($element) && !is_int($element)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Array of keys must contain only strings and integers, %s given.',
                            gettype($element)
                        )
                    );
                }
            }
        );
        
        $visitedKeys = array();
        $value = &$input;
        
        foreach ($keys as $key) {
            $visitedKeys[] = $key;
            
            if (!static::isArrayOrArrayAccess($value) || !isset($value[$key])) {
                if ($throwOnNonExisting) {
                    throw new \RuntimeException(sprintf('Element at %s does not exist.', json_encode($visitedKeys)));
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
     * @param   array|\ArrayAccess          $input
     * @param   string                      $keyPath
     * @param   mixed                       $defaultValue
     * @param   bool                        $throwOnNonExisting
     * @param   string                      $keySeparator
     *
     * @return  mixed
     *
     * @throws  \InvalidArgumentException   on invalid arguments
     * @throws  \RuntimeException           when element does not exist and $throwOnNonExisting is set to true
     */
    public static function getElementByKeyPath(
        $input,
        $keyPath,
        $defaultValue = null,
        $throwOnNonExisting = false,
        $keySeparator = '.'
    )
    {
        if (!is_string($keyPath)) {
            throw new \InvalidArgumentException(sprintf('Key path must be a string, %s given.', gettype($keyPath)));
        }
        
        if (!is_string($keySeparator)) {
            throw new \InvalidArgumentException(sprintf('Key separator must be a string, %s given.', gettype($keySeparator)));
        }
        
        try {
            return static::getElementByKeys($input, explode($keySeparator, $keyPath), $defaultValue, $throwOnNonExisting);
        }
        catch (\RuntimeException $e) {
            // just change the exception message
            throw new \RuntimeException(sprintf('Element at %s does not exist.', $keyPath));
        }
    }
    
    /**
     * Transforms one array or \ArrayAccess instance to another using a transformation map and array prototype.
     *
     * Only values at requested keys are used, others are ignored.
     * Values can be overwritten if destination keys collide.
     *
     * @param   array|\ArrayAccess          $input
     * @param   array                       $keyPathMap         a sourceKey => destinationKey array
     * @param   mixed                       $defaultValue
     * @param   bool                        $throwOnNonExisting
     * @param   string                      $keySeparator
     * @param   array|\ArrayAccess          $arrayPrototype     Prototype for an empty array
     *
     * @return  array|\ArrayAccess
     *
     * @throws  \InvalidArgumentException   on invalid arguments
     * @throws  \RuntimeException           when element does not exist and $throwOnNonExisting is set to true
     */
    public static function transformByPathKeyMap(
        $input,
        array $keyPathMap,
        $defaultValue = null,
        $throwOnNonExisting = false,
        $keySeparator = '.',
        $arrayPrototype = array()
    )
    {
        if (!is_array($arrayPrototype) && !($arrayPrototype instanceof \ArrayAccess)) {
            throw new \InvalidArgumentException('Array prototype must an be array or an instance of ArrayAccess.');
        }
        
        if (count($keyPathMap) === 0) {
            throw new \InvalidArgumentException('Path key map must have at least one element.');
        }
        
        foreach ($keyPathMap as $inputKeyPath=> $outputKeyPath) {
            if (!is_string($inputKeyPath)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Key path map must be a string=> string array, invalid key \'%s\'.',
                        $inputKeyPath
                    )
                );
            }
            
            if (!is_string($outputKeyPath)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Key path map must be a string=> string array, invalid type %s at key \'%s\'.',
                        gettype($outputKeyPath),
                        $inputKeyPath
                    )
                );
            }
        }
        
        // no need to validate $input and $keySeparator:
        // they will be validated on the first call of getElementByKeyPath
        
        $output = is_object($arrayPrototype)? clone $arrayPrototype : $arrayPrototype;
        
        foreach ($keyPathMap as $inputKeyPath=> $outputKeyPath) { 
            $element = static::getElementByKeyPath($input, $inputKeyPath, $defaultValue, $throwOnNonExisting, $keySeparator);
            
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
        return (is_array($input) || ($input instanceof \ArrayAccess));
    }
}

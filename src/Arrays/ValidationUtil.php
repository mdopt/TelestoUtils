<?php

namespace Telesto\Utils\Arrays;

use Telesto\Utils\TypeUtil;

use ArrayAccess;

use LogicException;
use InvalidArgumentException;
use LengthException;

abstract class ValidationUtil
{
    /**
     * @param   mixed   $input
     *
     * @return  bool
     */
    public static function isArrayOrArrayAccess($input)
    {
        return (is_array($input) || ($input instanceof ArrayAccess));
    }
    
    /**
     * @param   mixed   $input
     * @param   string  $argumentName
     *
     * @return  void
     *
     * @throws  InvalidArgumentException
     */
    public static function requireArrayOrArrayAccess($input, $argumentName)
    {
        if (!static::isArrayOrArrayAccess($input)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument %s must be an array or an instance of ArrayAccess, %s given.',
                    $argumentName,
                    TypeUtil::getType($input)
                )
            );
        }
    }

    /**
     * @param   mixed   $keyPath
     *
     * @return  void
     *
     * @throws  LogicException
     */
    public static function requireValidKeyPath($keyPath)
    {
        if (is_array($keyPath)) {
            return static::requireValidArrayKeyPath($keyPath);
        }
        
        if (!is_string($keyPath)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Key path must be a string or an array, %s given.',
                    TypeUtil::getType($keyPath)
                )
            );
        }
    }
    
    /**
     * @param   array   $keyPath
     *
     * @return  void
     *
     * @throws  LogicException
     */
    public static function requireValidArrayKeyPath(array $keyPath)
    {
        if (count($keyPath) === 0) {
            throw new LengthException('At least one key must be given.');
        }
        
        foreach ($keyPath as $index => $keyPart) {
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
    
    public static function requireValidKeyPathMap(array $keyPathMap)
    {
        foreach ($keyPathMap as $inputKeyPath => $outputKeyPath) {
            $originalOutputKeyPath = $outputKeyPath;
            $outputKeyPaths = is_array($outputKeyPath)? $outputKeyPath : array($outputKeyPath);
            
            foreach ($outputKeyPaths as $index => $outputKeyPath) {
                try {
                    static::requireValidKeyPath($outputKeyPath);
                }
                catch (LogicException $e) {
                    $exceptionClass = get_class($e);
                    
                    if (is_array($originalOutputKeyPath)) {
                        $newMessage = sprintf(
                            'Invalid output key path for input key path \'%s\'(subindex %s): %s',
                            $inputKeyPath,
                            $index,
                            $e->getMessage()
                        );
                    }
                    else {
                        $newMessage = sprintf(
                            'Invalid output key path for input key path \'%s\': %s',
                            $inputKeyPath,
                            $e->getMessage()
                        );
                    }
                    
                    throw new $exceptionClass($newMessage, $e->getCode(), $e);
                }
            }
        }
    }
    
    /**
     * @param   mixed[]     $options
     * @param   string[]    $keys       Keys of $options array which have to be validated
     *                                  This argument itself is not validated
     *
     * @return  void
     *
     * @throws  LogicException
     */
    public static function requireValidOptions(array $options, array $keys)
    {
        $options = array_intersect_key($options, array_flip($keys));
        
        if (array_key_exists('keySeparator', $options)) {
            static::requireValidKeySeparator($options['keySeparator']);
        }
        
        if (array_key_exists('arrayPrototype', $options)) {
            static::requireValidArrayPrototype($options['arrayPrototype']);
        }
        
        if (array_key_exists('returnMode', $options)) {
            static::requireValidReturnMode($options['returnMode']);
        }
    }
    
    public static function requireValidKeySeparator($keySeparator)
    {
        if (!is_string($keySeparator)) {
            throw new InvalidArgumentException(
                sprintf('Option \'keySeparator\' must be a string, %s given.', TypeUtil::getType($keySeparator))
            );
        }
    }
    
    public static function requireValidArrayPrototype($arrayPrototype)
    {
        if (!static::isArrayOrArrayAccess($arrayPrototype)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Option \'arrayPrototype\' must an be array or an instance of ArrayAccess, %s given.',
                    TypeUtil::getType($arrayPrototype)
                )
            );
        }
    }
    
    public static function requireValidReturnMode($returnMode)
    {
        if (
            !in_array(
                $returnMode,
                array(
                    ReturnMode::ELEMENT_ONLY,
                    ReturnMode::EXISTS_ONLY,
                    ReturnMode::BOTH
                ),
                true
            )
        ) {
            throw new InvalidArgumentException('Option \'returnMode\' must an be same as one of ReturnMode constants.');
        }
    }
}

<?php

namespace Telesto\Utils\Arrays;

use Telesto\Utils\ArrayUtil;
use Telesto\Utils\CommonUtil;
use Telesto\Utils\StringUtil;
use Telesto\Utils\TypeUtil;

use Telesto\Utils\Arrays\ReturnMode;

use Traversable;
use ArrayAccess;
use DomainException;
use InvalidArgumentException;

/**
 * Utility for wildcard keys and key paths.
 *
 * Used for internal purposes. It is not the part of the api.
 */
abstract class WildcardKeyUtil
{
    const COMPATIBLE_TYPE_INFO = 'an array or an object implementing both Traversable and ArrayAccess interfaces';
    
    /**
     * Returns key paths to query given an array or array-like object and
     * input path wildcard representation(see getInputPathRepr).
     *
     * Example result of this method:
     *
     * <code>
     * [
     *     ['key', 'deeperKey', ...],
     *     ['key', 'deeperKey2', ...],
     *     ...
     * ]
     * </code>
     *
     * @param   array|object        $array          Either array or object implementing
     *                                              both ArrayAccess and Traversable
     *                                                          
     * @param   array               $inputPathRepr  see getInputPathRepr
     * @param   array               $options
     *
     * @return  array
     */
    public static function getInputKeyPaths($array, array $inputPathRepr, array $options = array())
    {
        if (!static::isArrayCompatible($array)) {
            throw new InvalidArgumentException(
                sprintf('Argument $array is not compatible type (expected %s, got %s).', static::COMPATIBLE_TYPE_INFO, TypeUtil::getType($array))
            );
        }
        
        $omitNonExisting    = !empty($options['omitNonExisting']);
        
        $searchOptions         = array(
            'returnMode'            => ReturnMode::BOTH,
            'omitValidation'        => true
        );
        
        $keyPaths = array();
        
        foreach ($inputPathRepr['keyPath'] as $keyIndex => $key) {
            $isParameter = isset($inputPathRepr['parameters'][$keyIndex]);
            
            if ($keyIndex === 0) {
                $deeperKeys = $isParameter? ArrayUtil::getKeys($array) : array($key);
                
                foreach ($deeperKeys as $deeperKey) {
                    $keyPaths[] = array($deeperKey);
                }
                
                continue;
            }
            
            $newKeyPaths = array();
            
            foreach ($keyPaths as $keyPath) {
                list ($innerArray, $exists) = ArrayUtil::getElementByKeyPath($array, $keyPath, $searchOptions);
                $isArrayCompatible = static::isArrayCompatible($innerArray);
                
                if ($omitNonExisting && (!$exists || !$isArrayCompatible)) {
                    continue;
                }
                
                if (!$isParameter) {
                    $newKeyPaths[] = array_merge($keyPath, array($key));
                    continue;
                }
                
                if (!$exists) {
                    throw new DomainException(sprintf('Element at key path %s does not exist.', json_encode($keyPath)));
                }
                else if (!$isArrayCompatible) {
                    throw new InvalidArgumentException(
                        sprintf('Element at key path %s is not compatible type (expected %s, got %s).', json_encode($keyPath), static::COMPATIBLE_TYPE_INFO, TypeUtil::getType($innerArray))
                    );
                }
                
                $deeperKeys = ArrayUtil::getKeys($innerArray);
                
                foreach ($deeperKeys as $deeperKey) {
                    $newKeyPaths[] = array_merge($keyPath, array($deeperKey));
                }
            }
            
            $keyPaths = $newKeyPaths;
        }
        
        return $keyPaths;
    }
    
    /**
     * @param   array       $inputKeyPath
     * @param   array       $inputPathRepr
     * @param   array       $outputPathRepr
     *
     * @return  array
     */
    public static function getOutputKeyPath(array $inputKeyPath, array $inputPathRepr, array $outputPathRepr)
    {
        $parameterIndexes = array_flip($inputPathRepr['parameters']);
        $outputKeyPath = array();
        
        foreach ($outputPathRepr as $outputKeyRepr) {
            $outputKey = '';
            
            foreach ($outputKeyRepr as $outputKeyReprEntry) {
                list ($isParameter, $content) = $outputKeyReprEntry;
                
                if ($isParameter) {
                    $outputKey .= $inputKeyPath[$parameterIndexes[$content]];
                }
                else {
                    $outputKey .= $content;
                }
            }
            
            $outputKeyPath[] = $outputKey;
        }
        
        return $outputKeyPath;
    }
    
    /**
     * Returns representation of wildcard key path map relation as an array:
     *
     * <code>
     * [
     *     [inputPathRepr, [outputPathRepr1, outputPathRepr2, ...]],
     *     ...
     * ]
     * </code>
     *
     * @param   array           $keyPathMap
     * @param   array           $options
     *
     * @return  array
     */
    public static function getPathMapRepr(array $keyPathMap, array $options = array())
    {
        $repr = array();
        
        foreach ($keyPathMap as $inputKeyPath => $outputKeyPaths) {
            $repr[] = static::getPathRepr($inputKeyPath, $outputKeyPaths, $options);
        }
        
        return $repr;
    }
    
    /**
     * Returns representation of wildcard inputKeyPath -> outputKeyPath relation
     * as an array:
     *
     * <code>
     * [inputPathRepr, [outputPathRepr1, outputPathRepr2, ...]]
     * </code>
     *
     * @param   string              $inputKeyPath
     * @param   string|string[]     $outputKeyPaths
     * @param   array               $options
     *
     * @return  array
     *
     * @throws  LogicException
     */
    public static function getPathRepr($inputKeyPath, $outputKeyPaths, array $options = array())
    {
        $outputKeyPaths = is_array($outputKeyPaths)? $outputKeyPaths : array($outputKeyPaths);
        
        $inputRepr      = static::getInputPathRepr($inputKeyPath, $options);
        $inputParams    = array_values($inputRepr['parameters']);
        
        $outputReprs = array();
        
        foreach ($outputKeyPaths as $index => $outputKeyPath) {
            $outputKeys = ArrayUtil::getKeyPathAsArray($outputKeyPath, $options);
            
            foreach ($outputKeys as $outputKey) {
                $outputReprs[$index][] = static::getOutputKeyRepr($outputKey);
            }
            
            $outputParams   = static::getMultipleOutputKeyReprParams($outputReprs[$index]);
            $paramDiff      = array_diff($inputParams, $outputParams);
            $paramDiffRev   = array_diff($outputParams, $inputParams);
            
            if (count($paramDiff) > 0) {
                throw new DomainException(
                    sprintf(
                        'Parameters %s in the input(\'%s\') are not used in the output(\'%s\').',
                        json_encode(array_values($paramDiff)),
                        $inputKeyPath,
                        $outputKeyPath
                    )
                );
            }
            else if (count($paramDiffRev) > 0) {
                throw new DomainException(
                    sprintf(
                        'Parameters %s in the output(\'%s\') are not defined in the input(\'%s\').',
                        json_encode(array_values($paramDiffRev)),
                        $inputKeyPath,
                        $outputKeyPath
                    )
                );
            }
        }
        
        return array($inputRepr, $outputReprs);
    }
    
    /**
     * Returns representation of wildcard input key path for further processing.
     *
     * The result of $keyPath 'static.%x%.%y%' should be:
     *
     * <code>
     * [
     *     'keyPath'        => ['static', '%x%', '%y%'],
     *     'parameters'     => [
     *           1          => 'x', 
     *           2          => 'y'
     *     ]
     * ]
     * </code>
     *
     * @param   string|array    $keyPath
     * @param   array           $options    see ArrayUtil::getKeyPathAsArray
     *
     * @return  array
     *
     * @throws  LogicException
     *
     * @see ArrayUtil::getKeyPathAsArray
     */
    public static function getInputPathRepr($keyPath, array $options = array())
    {
        $keyPathAsArray = ArrayUtil::getKeyPathAsArray($keyPath, $options);
        $parameters = array();
        $pattern = '/^%([\w]+)%$/';
        
        foreach ($keyPathAsArray as $position => $key) {
            if (strpos($key, '%') === false) {
                continue;
            }
            
            if (preg_match($pattern, $key, $matches)) {
                $parameterName = $matches[1];
                
                if (in_array($parameterName, $parameters, true)) {
                    throw new DomainException(
                        sprintf(
                            'Parameter \'%s\' occurs more than once in key path %s.',
                            $parameterName,
                            is_array($keyPath)? json_encode($keyPath) : "'" . $keyPath . "'"
                        )
                    );
                }
                
                $parameters[$position] = $matches[1];
                continue;
            }
            
            $wildcardCharCounts = StringUtil::substrConsecutiveCounts($key, '%');
            foreach ($wildcardCharCounts as $charCount) {
                if ($charCount % 2 === 1) {
                    throw new DomainException(
                        sprintf(
                            'Invalid key \'%s\' in key path %s: every wildcard(\'%%\') character that does not mark a parameter must be escaped using double wildcard(\'%%%%\').',
                            $key,
                            is_array($keyPath)? json_encode($keyPath) : "'" . $keyPath . "'"
                        )
                    );
                }
            }
            
            $keyPathAsArray[$position] = str_replace('%%', '%', $key);
        }
        
        return array(
            'keyPath'       => $keyPathAsArray,
            'parameters'    => $parameters
        );
    }
    
    /**
     * Returns representation of wildcard output key path for further processing.
     *
     * The format of the output is:
     *
     * <code>
     * [
     *     [isParameter: bool, content: string]
     * ]
     * </code>
     *
     * The result of $key 'static1%var1%%var2%static2' should be:
     *
     * <code>
     * [
     *     [false, 'static1'],
     *     [true, 'var1'],
     *     [true, 'var2'],
     *     [false, 'static2']
     * ]
     * </code>
     *
     * @param   string          $key
     *
     * @return  array
     *
     * @throws  LogicException
     */
    public static function getOutputKeyRepr($key)
    {
        $chars = str_split($key);
        $wildcardCharCount = 0;
        $isWildcardStarted = false;
        $lastIndex = count($chars) - 1;
        
        $repr = array();
        $currentPart = '';
        
        foreach ($chars as $char) {
            $isWildcardChar = ($char === '%');
            
            $finishWildcard = ($isWildcardChar && $isWildcardStarted);
            $startWildcard  = (!$isWildcardChar && $wildcardCharCount === 1);
            
            $changeState    = ($finishWildcard || $startWildcard);
            
            if ($finishWildcard) {
                static::requireValidParameter($currentPart);
                
                $repr[] = array(true, $currentPart);
                $currentPart = '';
            }
            else if ($startWildcard) {
                if ($currentPart !== '') {
                    $repr[] = array(false, $currentPart);
                }
                
                $currentPart = $char;
            }
            
            if ($changeState) {
                $isWildcardStarted = !$isWildcardStarted;
                $wildcardCharCount = 0;
                
                continue;
            }
            
            if ($isWildcardChar) {
                if ($wildcardCharCount === 1) {
                    $currentPart .= '%';
                }
                
                $wildcardCharCount = $wildcardCharCount === 1? 0 : 1;
            }
            else {
                $currentPart .= $char;
            }
        }
        
        if ($currentPart !== '') {
            if ($isWildcardStarted) {
                throw new DomainException(sprintf('Started but not finished parameter: \'%s\' in \'%s\'.', $currentPart, $key));
            }
            
            if ($wildcardCharCount !== 0) {
                throw new DomainException(sprintf('Unescaped wildcard character(%%) at the end: \'%s\'.', $key));
            }
            
            $repr[] = array(false, $currentPart);
        }
        
        return $repr;
    }
    
    public static function isArrayCompatible($array)
    {
        return (is_array($array) || (($array instanceof \Traversable) && ($array instanceof \ArrayAccess)));
    }
    
    public static function getMultipleOutputKeyReprParams(array $reprs)
    {
        $parameters = array();
        
        foreach ($reprs as $repr) {
            $parameters = array_merge($parameters, static::getOutputKeyReprParams($repr));
        }
        
        return array_values(array_unique($parameters));
    }
    
    public static function getOutputKeyReprParams(array $repr)
    {
        $parameters = array();
        
        foreach ($repr as $reprElement) {
            list ($isParameter, $content) = $reprElement;
            
            if ($isParameter) {
                $parameters[] = $content;
            }
        }
        
        return array_values(array_unique($parameters));
    }
    
    protected static function requireValidParameter($parameter)
    {
        if (!preg_match('/^\w+$/', $parameter)) {
            throw new DomainException(sprintf('Invalid parameter \'%s\'.', $parameter));
        }
    }
}

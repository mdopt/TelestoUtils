<?php

namespace Telesto\Utils;

use InvalidArgumentException;

abstract class StringUtil
{
    /**
     * Works similarly to standard explode function, but has additional features.
     *
     * $options:
     * - escapeChar     string|null         Allows to escape the delimiter. Can be any single character.
     *                                      Default: null(no escaping).
     *
     * Option 'escapeChar' works like \ in php single quote strings('), meaning that:
     * 1) single escapeChar before delimiter avoids exploding at current offset and is removed,
     * 2) double escapeChar results in a single escapeChar.
     *
     * Escape character cannot not be the same as the delimiter or occur in the delimiter
     * to avoid ambiguity.
     *
     * @param   string      $delimiter
     * @param   string      $string
     * @param   int|null    $limit
     * @param   array       $options
     */
    public static function explode($delimiter, $string, $limit = null, array $options = array())
    {
        $delimiter = (string) $delimiter;
        $string = (string) $string;
        $limit = is_null($limit)? null : (int) $limit;
        
        $escapeChar = array_key_exists('escapeChar', $options)? $options['escapeChar'] : null;
        $escapeChar = is_null($escapeChar)? null : (string) $escapeChar;
        
        if (is_string($escapeChar)) {
            if (strlen($escapeChar) !== 1) {
                throw new InvalidArgumentException(
                    sprintf('If specified, option \'escapeChar\' must have exactly 1 character, %s given.', strlen($escapeChar))
                );
            }
            else if (strpos($delimiter, $escapeChar) !== false) {
                throw new InvalidArgumentException(
                    'Escape character cannot not be the same as the delimiter or occur in the delimiter.'
                );
            }
        }
        
        if (is_null($escapeChar)) {
            return is_null($limit)? explode($delimiter, $string) : explode($delimiter, $string, $limit);
        }
        
        $parts = explode($delimiter, $string);
        $partsCount = count($parts);
        
        $escapeMap = array(
            str_repeat($escapeChar, 2)      => $escapeChar,
            $escapeChar . $delimiter        => $delimiter,
        );
        
        // if n is in array, nth part will be joined with (n + 1)th part
        $partKeysToJoin = array();
        
        foreach ($parts as $partKey=> $part) {
            $isLastKey = ($partKey >= $partsCount - 1);
            if ($isLastKey) {
                $parts[$partKey] = strtr($part, $escapeMap);
                break;
            }
            
            $partLen = strlen($part);
            $escapeCharsCount = 0;
            
            while ($partLen > $escapeCharsCount) {
                $rightChar = substr($part, - $escapeCharsCount - 1, 1);
                
                if ($rightChar !== $escapeChar) {
                    break;
                }
                
                ++$escapeCharsCount;
            }
            
            if ($escapeCharsCount % 2 === 1) {
                $partKeysToJoin[] = $partKey;
                $part = substr($part, 0, -1);
            }
            
            $parts[$partKey] = strtr($part, $escapeMap);
        }
        
        krsort($partKeysToJoin);
        
        foreach ($partKeysToJoin as $partKey) {
            $parts[$partKey] = $parts[$partKey] . $delimiter . $parts[$partKey + 1];
            unset($parts[$partKey + 1]);
        }
        
        $parts = array_values($parts);
        
        if (is_null($limit)) {
            return $parts;
        }
        
        return static::limitExplodedParts($parts, $limit, $delimiter);
    }
    
    protected static function limitExplodedParts(array $parts, $limit, $delimiter)
    {
        if ($limit === 0) {
            $limit = 1; // works as expected for standard explode
        }
        
        $partsCount = count($parts);
        
        if ($limit < 0) {
            if ($limit <= - $partsCount) {
                return array();
            }
            else {
                return array_slice($parts, 0, $partsCount + $limit);
            }
        }
        else {
            $limitedParts = array_slice($parts, 0, $limit - 1);
            $limitedParts[$limit - 1] = implode($delimiter, array_slice($parts, $limit - 1));
            return $limitedParts;
        }
    }
}

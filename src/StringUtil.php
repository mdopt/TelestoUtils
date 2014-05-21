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
        $escapeChar = isset($options['escapeChar'])? $options['escapeChar'] : null;
        $escapeChar = is_null($escapeChar)? null : (string) $escapeChar;
        
        static::validateImplodeExplodeEscapeChar($escapeChar, $delimiter);
        
        // the following strpos is for optimization:
        // if there is no escapeChar, there's no need for further parsing
        if (is_null($escapeChar) || strpos($string, $escapeChar) === false) {
            return is_null($limit)? explode($delimiter, $string) : explode($delimiter, $string, $limit);
        }
        
        $parts = explode($delimiter, $string);
        $partsCount = count($parts);
        
        $search = str_repeat($escapeChar, 2);
        $replace = $escapeChar;
        
        // if n is in array, nth part will be joined with (n + 1)th part
        $partKeysToJoin = array();
        $lastKey = $partsCount - 1;
        
        foreach ($parts as $partKey=> $part) {
            $isLastKey = ($partKey >= $lastKey);
            if ($isLastKey) {
                $parts[$partKey] = str_replace($search, $replace, $part);
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
            
            $parts[$partKey] = str_replace($search, $replace, $part);
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
    
    /**
     * @param   string      $glue
     * @param   array       $pieces
     * @param   array       $options    See explode options
     */
    public static function implode($glue, array $pieces, array $options = array())
    {
        $escapeChar = array_key_exists('escapeChar', $options)? $options['escapeChar'] : null;
        $escapeChar = is_null($escapeChar)? null : (string) $escapeChar;
        
        if (is_null($escapeChar)) {
            return implode($glue, $pieces);
        }
        
        static::validateImplodeExplodeEscapeChar($escapeChar, $glue, 'glue');
        
        $search = array($escapeChar, $glue);
        $replace = array(str_repeat($escapeChar, 2), $escapeChar . $glue);
        
        foreach ($pieces as $key=> $piece) {
            $pieces[$key] = str_replace($search, $replace, $piece);
        }
        
        return implode($glue, $pieces);
    }
    
    /**
     * Finds the positions of all occurrences of a substring in a string.
     *
     * <code>
     *  StringUtil::strposAll(' x xx x', 'x'); // returns [1, 3, 4, 6]
     * </code>
     *
     * @param   string      $haystack
     * @param   string      $needle
     * @param   int         $offset
     * @param   int|null    $length
     *
     * @return  array
     */
    public static function strposAll($haystack, $needle, $offset = 0, $length = null)
    {
        $boundary = is_null($length)? null : $offset + $length;
        $needleLen = strlen($needle);
        $haystackLen = strlen($haystack);
        
        if ($needleLen === 0) {
            throw new InvalidArgumentException('Needle cannot be an empty string.');
        }
        
        if (!is_null($boundary) && $boundary > $haystackLen) {
            throw new InvalidArgumentException(
                sprintf('Offset + length(%d) is greater than haystack length(%d).', $boundary, $haystackLen)
            );
        }
        
        $currentOffset = $offset;
        $positions = array();
        
        while (true) {
            $position = strpos($haystack, $needle, $currentOffset);
            
            if (
                $position === false
                || (!is_null($boundary) && $position + $needleLen > $boundary)
            ) {                
                break;
            }
            
            $positions[] = $position;
            $currentOffset = $position + $needleLen;
        }
        
        return $positions;
    }
    
    /**
     * Returns an array containing the numbers of consecutive occurences
     * of a substring in a string
     *
     * <code>
     *  StringUtil::substrConsecutiveCount(' x xxx x xx', 'x'); // returns [1, 3, 1, 2]
     * </code>
     * 
     * @param   string      $haystack
     * @param   string      $needle
     * @param   int|null    $offset
     * @param   int         $length
     *
     * @return  array
     */
    public static function substrConsecutiveCount($haystack, $needle, $offset = 0, $length = null)
    {
        $positions = static::strposAll($haystack, $needle, $offset, $length);
        
        $needleLen = strlen($needle);
        $consecutiveCount = 0;
        $countArray = array();
        $previousPosition = null;
        $lastIndex = count($positions) - 1;
        
        foreach ($positions as $index => $position) {
            if (is_null($previousPosition) || $position === $previousPosition + $needleLen) {
                ++$consecutiveCount;
            }
            else {
                if ($consecutiveCount > 0) {
                    $countArray[] = $consecutiveCount;
                }
                
                $consecutiveCount = 1;
            }
            
            if ($index === $lastIndex && $consecutiveCount > 0) {
                $countArray[] = $consecutiveCount;
            }
            
            $previousPosition = $position;
        }
        
        return $countArray;
    }
    
    /**
     * Returns the max number of consecutive occurences of a substring in a string
     *
     * <code>
     *  StringUtil::substrMaxConsecutiveCount(' x xxx x xx', 'x'); // returns 3
     * </code>
     * 
     * @param   string      $haystack
     * @param   string      $needle
     * @param   int|null    $offset
     * @param   int         $length
     *
     * @return  int
     */
    public static function substrMaxConsecutiveCount($haystack, $needle, $offset = 0, $length = null)
    {
        return max(static::substrConsecutiveCount($haystack, $needle, $offset, $length));
    }
    
    protected static function validateImplodeExplodeEscapeChar($escapeChar, $delimiter, $delimiterName = 'delimiter')
    {
        if (is_string($escapeChar)) {
            if (strlen($escapeChar) !== 1) {
                throw new InvalidArgumentException(
                    sprintf('If specified, option \'escapeChar\' must have exactly 1 character, %s given.', strlen($escapeChar))
                );
            }
            else if (strpos($delimiter, $escapeChar) !== false) {
                throw new InvalidArgumentException(
                    sprintf('Escape character cannot not be the same as the %1$s or occur in the %1$s.', $delimiterName)
                );
            }
        }
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

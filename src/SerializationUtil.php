<?php

namespace Telesto\Utils;

use InvalidArgumentException;
use UnexpectedValueException;

abstract class SerializationUtil
{
    /**
     * @param   string      $serialized
     * @param   bool        $suppressNotice
     *
     * @return  mixed
     *
     * @throws  InvalidArgumentException
     * @throws  UnexpectedValueException
     */
    public static function unserialize($serialized, $suppressNotice = false)
    {
        if (!is_string($serialized)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Serialized value must be a string, %s given.',
                    is_object($serialized)? get_class($serialized) : gettype($serialized)
                )
            );
        }
        
        if ($suppressNotice) {
            set_error_handler(function () {}, E_NOTICE);
        }
        
        $unserialized = unserialize($serialized);
        
        if ($suppressNotice) {
            restore_error_handler();
        }
        
        if ($unserialized === false && $serialized !== serialize(false)) {
            throw new UnexpectedValueException('Invalid value after unserialization.');
        }
        
        return $unserialized;
    }
}

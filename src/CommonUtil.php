<?php

namespace Telesto\Utils;

abstract class CommonUtil
{
    /**
     * This method is taken from Symfony HttpKernel component
     * (Symfony\Component\HttpKernel\HttpKernel class).
     *
     * In has been copied here, because in the original code it's a private
     * method, therefore not accessible from outside the class.
     *
     * @param   mixed       $var
     * @return  string
     *
     * @author  Fabien Potencier <fabien@symfony.com>
     * @see     https://github.com/symfony/HttpKernel
     */
    public static function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }
        
        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, static::varToString($v));
            }
            
            return sprintf("Array(%s)", implode(', ', $a));
        }
        
        if (is_resource($var)) {
            return sprintf('Resource(%s)', get_resource_type($var));
        }
        
        if (null === $var) {
            return 'null';
        }
        
        if (false === $var) {
            return 'false';
        }
        
        if (true === $var) {
            return 'true';
        }
        
        return (string) $var;
    }
}

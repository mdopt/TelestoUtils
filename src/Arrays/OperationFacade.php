<?php

namespace Telesto\Utils\Arrays;

use Telesto\Utils\TypeUtil;
use LogicException;

abstract class OperationFacade
{
    protected static $overwriterCreators = array(
        'allKeys'       => array(__CLASS__, 'createAllKeysOverwriter'),
        'keyPathPairs'  => array(__CLASS__, 'createKeyPathPairsOverwriter'),
        'composite'     => array(__CLASS__, 'createCompositeOverwriter')
    );
    
    protected static $transformerCreators = array();
    
    /**
     * @param   string      $name
     * @param   callable    $creator
     *
     * @return  void
     *
     * @throws  LogicException
     */
    public static function registerOverwriterCreator($name, $creator)
    {
        if (isset(static::$overwriterCreators[$name])) {
            throw new LogicException(sprintf('Overwriter creator \'%s\' is already registered.', $name));
        }
        
        TypeUtil::requireType($creator, 'callable', '$creator');
        
        static::$overwriterCreators[$name] = $creator;
    }
    
    /**
     * @param   string      $name
     * @param   callable    $creator
     *
     * @return  void
     *
     * @throws  LogicException
     */
    public static function registerTransformerCreator($name, $creator)
    {
        if (isset(static::$transformerCreators[$name])) {
            throw new LogicException(sprintf('Transformer creator \'%s\' is already registered.', $name));
        }
        
        TypeUtil::requireType($creator, 'callable', '$creator');
        
        static::$transformerCreators[$name] = $creator;
    }
    
    /**
     * @param   string      $name
     *
     * @return  Overwriting\Overwriter
     */
    public static function createOverwriter($name)
    {
        if (!isset(static::$overwriterCreators[$name])) {
            throw new LogicException(sprintf('Creator for overwriter \'%s\' does not exist.', $name));
        }
        
        $arguments = array_slice(func_get_args(), 1);
        return call_user_func_array(static::$overwriterCreators[$name], $arguments);
    }
    
    /**
     * @param   string      $name
     *
     * @return  Transformer
     */
    public static function createTransformer($name)
    {
        $arguments = array_slice(func_get_args(), 1);
        
        if (isset(static::$transformerCreators[$name])) {
            return call_user_func_array(static::$transformerCreators[$name], $arguments);
        }
        
        $overwriter = call_user_func_array(array(__CLASS__, 'createOverwriter'), func_get_args());
        
        return new Transformation\CreateAndOverwriteTransformer(
            new Factories\PrototypeFactory(array()),
            $overwriter
        );
    }
    
    /**
     * @param   array|\ArrayAccess  $array
     * @param   array|\ArrayAccess  $outputArray
     * @param   string              $operationName
     *
     * @return  void
     */
    public static function overwrite($array, &$outputArray, $operationName)
    {
        $arguments = array_slice(func_get_args(), 3);
        $overwriter = call_user_func_array(
            array(__CLASS__, 'createOverwriter'),
            array_merge(array($operationName), $arguments)
        );
        
        $overwriter->overwrite($array, $outputArray);
    }
    
    /**
     * @param   array|\ArrayAccess  $array
     * @param   string              $operationName
     *
     * @return  array|\ArrayAccess
     */
    public static function transform($array, $operationName)
    {
        $arguments = array_slice(func_get_args(), 2);
        $transformer = call_user_func_array(
            array(__CLASS__, 'createTransformer'),
            array_merge(array($operationName), $arguments)
        );
        
        return $transformer->transform($array);
    }
    
    protected static function createAllKeysOverwriter()
    {
        return new Overwriting\AllKeysOverwriter();
    }
    
    protected static function createKeyPathPairsOverwriter()
    {
        return TypeUtil::createObject('Telesto\Utils\Arrays\Overwriting\KeyPathPairsOverwriter', func_get_args());
    }
    
    protected static function createCompositeOverwriter()
    {
        $arguments = func_get_args();
        $overwriters = array();
        
        foreach ($arguments as $overwriterArguments) {
            $overwriters[] = call_user_func_array(array(__CLASS__, 'createOverwriter'), $overwriterArguments);
        }
        
        return new Overwriting\CompositeOverwriter($overwriters);
    }
}
<?php

namespace Telesto\Utils\Arrays\Overwriting\Copy\KeyPathMap;

use Telesto\Utils\Arrays\Overwriting\Overwriter;
use Telesto\Utils\ArrayUtil;
use Telesto\Utils\Arrays\ReturnMode;
use Telesto\Utils\Arrays\ValidationUtil;
use Telesto\Utils\Arrays\WildcardKeyUtil;
use Telesto\Utils\TypeUtil;

use LogicException;
use InvalidArgumentException;
use LengthException;

/**
 * Overwriter which works similarly to trivial key path overwriter, but allows
 * wildcards in key paths.
 *
 */
class WildcardOverwriter implements Overwriter
{
    /**
     * @param array
     */
    protected $mapRepr;
    
    /**
     * @param array
     */
    protected $defaultOptions;
    
    protected static $perOperationOptions = array(
        'default', 'throwOnNonExisting', 'throwOnCollision', 'arrayPrototype', 'omitNonExisting'
    );
    
    public function __construct(
        array $keyPathMap,
        array $defaultOptions = array()
    )
    {
        $this->setDefaultOptions($defaultOptions);
        $this->setMapRepr($keyPathMap);
    }
    
    /**
     * {@inheritdoc}
     */
    public function overwrite($input, &$output, array $options = array())
    {
        // require input to be traversable
        ValidationUtil::requireArrayOrArrayAccess($input, '$input');
        ValidationUtil::requireArrayOrArrayAccess($output, '$output');
        ValidationUtil::requireValidOptions($options, array('arrayPrototype'));
        
        $realOptions = array_merge(
            $this->defaultOptions,
            array_intersect_key(
                $options,
                array_flip(static::$perOperationOptions)
            )
        );
        
        foreach ($this->mapRepr as $pathRepr) {
            list ($inputPathRepr, $outputPathReprs) = $pathRepr;
            
            $inputPaths = WildcardKeyUtil::getInputKeyPaths($input, $inputPathRepr, $realOptions);
            
            foreach ($inputPaths as $inputPath) {
                $element = ArrayUtil::getElementByKeyPath($input, $inputPath, $realOptions);
                $outputPaths = array();
                
                foreach ($outputPathReprs as $outputPathRepr) {
                    $outputPaths[] = WildcardKeyUtil::getOutputKeyPath($inputPath, $inputPathRepr, $outputPathRepr);
                }
                
                foreach ($outputPaths as $outputPath) {
                    ArrayUtil::setElementByKeyPath($output, $outputPath, $element);
                }
            }
        }
    }
    
    protected function setMapRepr(array $keyPathMap)
    {
        ValidationUtil::requireValidKeyPathMap($keyPathMap);
        $this->mapRepr = WildcardKeyUtil::getPathMapRepr($keyPathMap, $this->defaultOptions);
    }
    
    protected function setDefaultOptions(array $defaultOptions)
    {
        ValidationUtil::requireValidOptions($defaultOptions, array('keySeparator', 'arrayPrototype'));
        
        $defaultOptions['omitValidation']   = true;
        $this->defaultOptions = $defaultOptions;
    }
}

<?php

namespace Telesto\Utils\Arrays\Overwriting\Copy\KeyPathMap;

use Telesto\Utils\Arrays\Overwriting\Overwriter;
use Telesto\Utils\ArrayUtil;
use Telesto\Utils\Arrays\ReturnMode;
use Telesto\Utils\Arrays\ValidationUtil;
use Telesto\Utils\TypeUtil;

use LogicException;
use InvalidArgumentException;
use LengthException;

/**
 * Overwriter which uses key path map in the form of an array:
 *    <code>
 *    [
 *        inputKeyPath  => outputKeypath,
 *        ...
 *    ]
 *    </code>
 *
 * or
 *    <code>
 *    [
 *        inputKeyPath  => [outputKeypath1, outputKeyPath2],
 *        ...
 *    ]
 *    </code>
 * 
 * Paths must be strings containing keys combined using separator (default is dot)
 *
 *    <code>
 *    [
 *        'users.0.id'  => 'user.id',
 *        ...
 *    ]
 *    </code>
 *
 * This will tell the overwriter to put value of
 * $input['users'][0]['id'] into $output['user']['id']
 *
 * Only values at requested key paths are used, others are ignored.
 *
 * Values in output can get overwritten more than once if output key paths
 * are duplicated, example:
 *    <code>
 *    [
 *        'input.first'     => 'output.first',
 *        'input.second'    => 'output.first'
 *    ]
 *    </code>
 * 
 * Also collision might occur (see ArrayUtil::setElementByKeyPath)
 *
 * @see ArrayUtil::getElementByKeyPath
 * @see ArrayUtil::setElementByKeyPath
 */
class BasicOverwriter implements Overwriter
{
    /**
     * Internal representation of key paths in the form of an array: [
     *     ['input', 'key', 'path'], [['output', 'key', 'path'], ['output', 'key', 'path2'], ...],
     *     ...
     * ]
     *
     * @var array
     */
    protected $keyPaths;
    
    /**
     * @var array
     */
    protected $defaultOptions;
    
    /**
     * These options can be changed for a particular operation
     *
     * @var array
     */
    protected static $perOperationOptions = array(
        'default', 'throwOnNonExisting', 'throwOnCollision', 'arrayPrototype', 'omitNonExisting'
    );
    
    /**
     * $defaultOptions:
     * - default                [mixed]                 see ArrayUtil::getElementByKeyPath
     * - keySeparator           [string]                see ArrayUtil::getElementByKeyPath
     * - throwOnNonExisting     [bool]                  see ArrayUtil::getElementByKeyPath
     * - throwOnCollision       [bool]                  see ArrayUtil::setElementByKeyPath
     * - arrayPrototype         [array|ArrayAccess]     see ArrayUtil::setElementByKeyPath
     * - omitNonExisting        [bool]                  When true, values at non existing source keys will omitted
     *                                                   (no value at destination keys will be set, instead of default)
     *                                                  Overwrites 'throwOnNonExisting'. Default: false.
     * @param   array           $keyPathMap
     * @param   array           $defaultOptions
     *
     * @throws  LogicException  on invalid arguments
     */
    public function __construct(
        array $keyPathMap,
        array $defaultOptions = array()
    )
    {
        // Default options must be set before key path pairs, because string paths
        // get transformed to arrays using keySeparator setting
        $this->setDefaultOptions($defaultOptions);
        $this->setKeyPaths($keyPathMap);
    }
    
    /**
     * {@inheritdoc}
     */
    public function overwrite($input, &$output, array $options = array())
    {
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
        
        $omitNonExisting = !empty($realOptions['omitNonExisting']);
        
        if ($omitNonExisting) {
            $realOptions['throwOnNonExisting'] = false;
            $realOptions['returnMode']  = ReturnMode::BOTH;
        }
        else {
            $realOptions['returnMode']  = ReturnMode::ELEMENT_ONLY;
        }
        
        foreach ($this->keyPaths as $keyPathPair) {
            list ($inputKeyPath, $outputKeyPaths) = $keyPathPair;
            
            if ($omitNonExisting) {
                list ($element, $exists) = ArrayUtil::getElementByKeyPath($input, $inputKeyPath, $realOptions);
                
                if (!$exists) {
                    continue;
                }
            }
            else {
                $element = ArrayUtil::getElementByKeyPath($input, $inputKeyPath, $realOptions);
            }
            
            foreach ($outputKeyPaths as $outputKeyPath) {
                ArrayUtil::setElementByKeyPath($output, $outputKeyPath, $element, $realOptions);
            }
        }
    }
    
    protected function setKeyPaths(array $keyPathMap)
    {
        ValidationUtil::requireValidKeyPathMap($keyPathMap);
        $keyPaths = array();
        
        foreach ($keyPathMap as $inputKeyPath => $outputKeyPaths) {
            $outputKeyPaths = (array) $outputKeyPaths;
            $normalizedOutputKeyPaths = array();
            
            foreach ((array) $outputKeyPaths as $outputKeyPath) {
                $normalizedOutputKeyPaths[] = ArrayUtil::getKeyPathAsArray($outputKeyPath, $this->defaultOptions);
            }
            
            $keyPaths[] = array(
                ArrayUtil::getKeyPathAsArray($inputKeyPath, $this->defaultOptions),
                $normalizedOutputKeyPaths
            );
        }
        
        $this->keyPaths = $keyPaths;
    }
    
    protected function setDefaultOptions(array $defaultOptions)
    {
        ValidationUtil::requireValidOptions($defaultOptions, array('keySeparator', 'arrayPrototype'));
        
        // This option should be hardcoded.
        // We don't need ArrayUtil validation (this class does it's own)
        $defaultOptions['omitValidation']   = true;
        
        $this->defaultOptions = $defaultOptions;
    }
}

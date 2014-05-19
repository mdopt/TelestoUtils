<?php

namespace Telesto\Utils\Arrays\Overwriting;

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
class KeyPathMapOverwriter implements Overwriter
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
    protected $settings;
    
    /**
     * $settings:
     * - default                [mixed]                 see ArrayUtil::getElementByKeyPath
     * - keySeparator           [string]                see ArrayUtil::getElementByKeyPath
     * - throwOnNonExisting     [bool]                  see ArrayUtil::getElementByKeyPath
     * - throwOnCollision       [bool]                  see ArrayUtil::setElementByKeyPath
     * - arrayPrototype         [array|ArrayAccess]     see ArrayUtil::setElementByKeyPath
     * - omitNonExisting        [bool]                  When true, values at non existing source keys will omitted
     *                                                   (no value at destination keys will be set, instead of default)
     *                                                  Overwrites 'throwOnNonExisting'. Default: false.
     * @param   array           $keyPathMap
     * @param   array           $settings
     *
     * @throws  LogicException  on invalid arguments
     */
    public function __construct(
        array $keyPathMap,
        array $settings = array()
    )
    {
        // settings must be set before key path pairs, because string paths
        // get transformed to arrays using keySeparator setting
        $this->setSettings($settings);
        $this->setKeyPaths($keyPathMap);
    }
    
    /**
     * {@inheritdoc}
     */
    public function overwrite($input, &$output)
    {
        ValidationUtil::requireArrayOrArrayAccess($input, '$input');
        ValidationUtil::requireArrayOrArrayAccess($output, '$output');
        
        $options = $this->settings;
        $omitNonExisting = $options['omitNonExisting'];
        
        foreach ($this->keyPaths as $keyPathPair) {
            list ($inputKeyPath, $outputKeyPaths) = $keyPathPair;
            
            if ($omitNonExisting) {
                list ($element, $exists) = ArrayUtil::getElementByKeyPath($input, $inputKeyPath, $options);
                
                if (!$exists) {
                    continue;
                }
            }
            else {
                $element = ArrayUtil::getElementByKeyPath($input, $inputKeyPath, $options);
            }
            
            foreach ($outputKeyPaths as $outputKeyPath) {
                ArrayUtil::setElementByKeyPath($output, $outputKeyPath, $element, $options);
            }
        }
    }
    
    protected function setKeyPaths(array $keyPathMap)
    {
        $this->validateKeyPathMap($keyPathMap);
        $keyPaths = array();
        
        foreach ($keyPathMap as $inputKeyPath => $outputKeyPaths) {
            $outputKeyPaths = (array) $outputKeyPaths;
            $normalizedOutputKeyPaths = array();
            
            foreach ((array) $outputKeyPaths as $outputKeyPath) {
                $normalizedOutputKeyPaths[] = ArrayUtil::getKeyPathAsArray($outputKeyPath, $this->settings);
            }
            
            $keyPaths[] = array(
                ArrayUtil::getKeyPathAsArray($inputKeyPath, $this->settings),
                $normalizedOutputKeyPaths
            );
        }
        
        $this->keyPaths = $keyPaths;
    }
    
    protected function setSettings(array $settings)
    {
        $this->validateSettings($settings);
        
        // These settings should be hardcoded
        $settings['omitValidation']     = true;
        $settings['omitNonExisting']    = !empty($settings['omitNonExisting']);
        
        if ($settings['omitNonExisting']) {
            $settings['returnMode']     = ReturnMode::BOTH;
        }
        else {
            $settings['returnMode']     = ReturnMode::ELEMENT_ONLY;
        }
        
        $this->settings = $settings;
    }
    
    protected function validateKeyPathMap(array $keyPathMap)
    {
        foreach ($keyPathMap as $inputKeyPath => $outputKeyPath) {
            $originalOutputKeyPath = $outputKeyPath;
            $outputKeyPaths = is_array($outputKeyPath)? $outputKeyPath : array($outputKeyPath);
            
            foreach ($outputKeyPaths as $index => $outputKeyPath) {
                try {
                    ValidationUtil::requireValidKeyPath($outputKeyPath);
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
    
    protected function validateSettings(array $settings)
    {
        ValidationUtil::requireValidOptions($settings, array('keySeparator', 'arrayPrototype'));
    }
}

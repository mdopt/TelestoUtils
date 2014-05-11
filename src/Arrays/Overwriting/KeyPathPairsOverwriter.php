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
 * Overwriter which uses pairs of key paths in the array form:
 *    <code>
 *    [
 *        [inputKeyPath, outputKeypath],
 *        ...
 *    ]
 *    </code>
 * 
 * Paths can be either arrays of scalars:
 *    <code>
 *    [
 *        [['users', 0, 'id'], ['user', 'id']],
 *        ...
 *    ]
 *    </code>
 *
 * or strings containing combined path with separator (default is dot)
 *    <code>
 *    [
 *        ['users.0.id', 'user.id'],
 *        ...
 *    ]
 *    </code>
 *
 * Both forms are equivalent and will tell the overwriter to put value of
 * $input['users'][0]['id'] into $output['user']['id']
 *
 * Only values at requested key paths are used, others are ignored.
 *
 * Values in output can get overwritten more than once if output key paths
 * are duplicated, example:
 *    <code>
 *    [
 *        ['input.first', 'output.first'],
 *        ['input.second', 'output.first']
 *    ]
 *    </code>
 * 
 * Also collision might occur (see ArrayUtil::setElementByKeyPath)
 *
 * @see ArrayUtil::getElementByKeyPath
 * @see ArrayUtil::setElementByKeyPath
 */
class KeyPathPairsOverwriter implements Overwriter
{
    /**
     * @var array
     */
    protected $keyPathPairs;
    
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
     * @param   array           $keyPathPairs
     * @param   array           $settings
     *
     * @throws  LogicException  on invalid arguments
     */
    public function __construct(
        array $keyPathPairs,
        array $settings = array()
    )
    {
        // settings must be set before key path pairs, because string paths
        // get transformed to arrays using keySeparator setting
        $this->setSettings($settings);
        $this->setKeyPathPairs($keyPathPairs);
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
        
        foreach ($this->keyPathPairs as $keyPathPair) {
            list ($inputKeyPath, $outputKeyPath) = $keyPathPair;
            
            if ($omitNonExisting) {
                list ($element, $exists) = ArrayUtil::getElementByKeyPath($input, $inputKeyPath, $options);
                
                if ($exists) {
                    ArrayUtil::setElementByKeyPath($output, $outputKeyPath, $element, $options);
                }
            }
            else {
                $element = ArrayUtil::getElementByKeyPath($input, $inputKeyPath, $options);
                ArrayUtil::setElementByKeyPath($output, $outputKeyPath, $element, $options);
            }
        }
    }
    
    protected function setKeyPathPairs(array $keyPathPairs)
    {
        $this->validateKeyPathPairs($keyPathPairs);
        
        foreach ($keyPathPairs as $index => $keyPathPair) {
            list ($inputKeyPath, $outputKeyPath) = $keyPathPair;
            
            $keyPathPairs[$index] = array(
                ArrayUtil::getKeyPathAsArray($inputKeyPath, $this->settings),
                ArrayUtil::getKeyPathAsArray($outputKeyPath, $this->settings)
            );
        }
        
        $this->keyPathPairs = $keyPathPairs;
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
    
    protected function validateKeyPathPairs(array $keyPathPairs)
    {
        foreach ($keyPathPairs as $index => $keyPathPair) {
            if (!is_array($keyPathPair)) {
                throw new InvalidArgumentException(sprintf(
                    'Argument $keyPathPairs must be an array of arrays, %s given at index %s.',
                    TypeUtil::getType($keyPathPair),
                    $index
                ));
            }
            
            if (count($keyPathPair) != 2) {
                throw new LengthException(sprintf(
                    'Arrays in $keyPathPairs must have exactly 2 elements, %d given at index %s.',
                    count($keyPathPair),
                    $index
                ));
            }
            
            $keyPathPair = array_values($keyPathPair);
            
            foreach ($keyPathPair as $keyPathIndex => $keyPath) {
                $e = null;
                
                try {
                    ValidationUtil::requireValidKeyPath($keyPath);
                }
                catch (InvalidArgumentException $e) {
                }
                catch (LogicException $e) {
                }
                
                if ($e) {
                    $exceptionClass = get_class($e);
                    $newMessage = sprintf(
                        'Invalid value for %s key path at index %s: %s',
                        $keyPathIndex == 0? 'input' : 'output',
                        $index,
                        $e->getMessage()
                    );
                    
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

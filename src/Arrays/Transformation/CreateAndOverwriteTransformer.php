<?php

namespace Telesto\Utils\Arrays\Transformation;

use Telesto\Utils\Arrays\Overwriting\Overwriter;
use Telesto\Utils\ArrayUtil;
use Telesto\Utils\Arrays\ValidationUtil;

/**
 * Transformer which creates a new array|ArrayAccess and then performs overwriting.
 *
 * Since you can perform any transformation this way, most of the 'real' work
 * is done by overwriters. This way we don't have to duplicate functionality.
 *
 * It could be done the other way: by putting functionality in the transformer
 * and letting the overwriter use the output of the transformation.
 * But then, the overwriter would have to know the keys of the output.
 * It is possible (and trivial) for arrays, but not for all ArrayAccess instances.
 */
class CreateAndOverwriteTransformer implements Transformer
{
    /**
     * @var Overwriter
     */
    protected $overwriter;
    
    /**
     * @var array
     */
    protected $defaultOptions;
    
    /**
     * $options:
     * - arrayPrototype         [array|ArrayAccess]     default: empty array
     *
     * @param   Overwriter      $overwriter
     * @param   array           $defaultOptions
     */
    public function __construct(Overwriter $overwriter, array $defaultOptions = array())
    {
        ValidationUtil::requireValidOptions($defaultOptions, array('arrayPrototype'));
        
        $this->overwriter = $overwriter;
        $this->defaultOptions = $defaultOptions;
    }
    
    /**
     * {@inheritdoc}
     */
    public function transform($input, array $options = array())
    {
        ValidationUtil::requireArrayOrArrayAccess($input, '$input');
        ValidationUtil::requireValidOptions($options, array('arrayPrototype'));
        
        $localOptions = array_merge($this->defaultOptions, $options);
        $arrayPrototype = isset($localOptions['arrayPrototype'])? $localOptions['arrayPrototype'] : array();
        
        $output = is_object($arrayPrototype)? clone $arrayPrototype : $arrayPrototype;
        $this->overwriter->overwrite($input, $output, $options);
        
        return $output;
    }
}

<?php

namespace Telesto\Utils\Arrays\Transformation;

use Telesto\Utils\Arrays\Factories\Factory;
use Telesto\Utils\Arrays\Overwriting\Overwriter;
use Telesto\Utils\ArrayUtil;
use Telesto\Utils\Arrays\ValidationUtil;

/**
 * Transformer which uses factory to create a new array|ArrayAccess and then
 * performs overwriting.
 *
 * Since you can perform any transformation this way, most of the 'real' work
 * is done by overwriters. This way we don't have to duplicate functionality.
 *
 * It could be done the other way: putting functionality in transformer and
 * letting the overwriter use the output of the transformation.
 * But then, the overwriter would have to know the keys of the output.
 * It is possible (and trivial) for arrays, but not for all ArrayAccess instances.
 */
class CreateAndOverwriteTransformer implements Transformer
{
    /**
     * @var Factory
     */
    protected $factory;
    
    /**
     * @var Overwriter
     */
    protected $overwriter;
    
    public function __construct(Factory $factory, Overwriter $overwriter)
    {
        $this->factory = $factory;
        $this->overwriter = $overwriter;
    }
    
    /**
     * {@inheritdoc}
     */
    public function transform($input)
    {
        ValidationUtil::requireArrayOrArrayAccess($input, '$input');
        
        $output = $this->factory->createArray();
        $this->overwriter->overwrite($input, $output);
        
        return $output;
    }
}

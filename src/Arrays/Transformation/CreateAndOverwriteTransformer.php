<?php

namespace Telesto\Utils\Arrays\Transformation;

use Telesto\Utils\Arrays\Factories\Factory;
use Telesto\Utils\Arrays\Overwriting\Overwriter;
use Telesto\Utils\ArrayUtil;

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
        ArrayUtil::ensureArrayOrArrayAccess($input, '$input');
        
        $output = $this->factory->createArray();
        $this->overwriter->overwrite($input, $output);
        
        return $output;
    }
}

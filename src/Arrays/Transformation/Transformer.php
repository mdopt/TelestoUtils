<?php

namespace Telesto\Utils\Arrays\Transformation;

interface Transformer
{
    /**
     * @param   array|\ArrayAccess  $input
     *
     * @return  array|\ArrayAccess
     */
    public function transform($input);
}

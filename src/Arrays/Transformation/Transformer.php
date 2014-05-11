<?php

namespace Telesto\Utils\Arrays\Transformation;

/**
 * Interface for transforming one array|ArrayAccess into another
 */
interface Transformer
{
    /**
     * @param   array|\ArrayAccess  $input
     *
     * @return  array|\ArrayAccess
     */
    public function transform($input);
}

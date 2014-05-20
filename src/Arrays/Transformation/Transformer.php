<?php

namespace Telesto\Utils\Arrays\Transformation;

/**
 * Interface for transforming one array|ArrayAccess into another
 */
interface Transformer
{
    /**
     * @param   array|\ArrayAccess  $input
     * @param   array               $options    Additional options of the transformation.
     *                                          This is non-standard argument, meaning that each transformer
     *                                          supports only it's own options (if any). Every supported option
     *                                          should be also settable using transformer's constructor.
     *
     * @return  array|\ArrayAccess
     */
    public function transform($input, array $options = array());
}

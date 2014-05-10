<?php

namespace Telesto\Utils\Arrays\Factories;

interface Factory
{
    /**
     * @return array|\ArrayAccess
     */
    public function createArray();
}

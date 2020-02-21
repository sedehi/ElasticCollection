<?php

namespace Sedehi\ElasticCollection\Facades;

use Illuminate\Support\Facades\Facade;

class ElasticCollection extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'elasticcollection';
    }
}

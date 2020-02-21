<?php

namespace Sedehi\ElasticCollection;

use Sedehi\ElasticCollection\Query\QueryBuilder;

trait ElasticCollection
{
    public static function elasticSearch()
    {
        return new QueryBuilder(new static());
    }
}

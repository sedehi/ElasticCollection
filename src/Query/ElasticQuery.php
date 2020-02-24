<?php

namespace Sedehi\ElasticCollection\Query;

use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait ElasticQuery
{
    protected function sendQuery($params, $method)
    {
        try {
            return $this->elasticClient->{$method}($params);
        } catch (Missing404Exception $exception) {
            throw new ModelNotFoundException($this->model);
        }
    }
}

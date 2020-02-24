<?php


namespace Sedehi\ElasticCollection\Query;


trait HasRelationships
{
    public function with($relations)
    {
        $relations = is_string($relations) ? func_get_args() : $relations;
        foreach ($relations as $relation) {
            if (method_exists($this->model, $relation)) {
                $this->relations[$relation] = $this->model->$relation()->getRelated();
            }
        }
        return $this;
    }
}

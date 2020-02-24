<?php


namespace Sedehi\ElasticCollection\Traits;


use Illuminate\Support\Arr;

trait ToModel
{
    /**
     * @param $model
     * @param $data
     * @return mixed
     */
    protected function convertToModel($model, $data)
    {
        if (!empty($this->model->elasticFields)) {
            $this->model->elasticFields = array_merge($this->model->elasticFields, array_keys($this->relations));
            $data                       = Arr::only($data, $this->model->elasticFields);
        }
        $model->exists = true;
        $data          = $this->formatDates($model, $data);
        foreach ($this->relations as $with => $relation) {
            if (isset($data[$with])) {
                $model->setRelation($with, $this->convertToModel($relation, $data[$with]));
            }
        }
        return  $model->forceFill($data);
    }


    public function elasticFields(array $fields)
    {
        $this->model->elasticFields = $fields;
        return $this;
    }
}

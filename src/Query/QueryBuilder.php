<?php

namespace Sedehi\ElasticCollection\Query;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Elasticsearch\ClientBuilder;
use Illuminate\Database\Eloquent\Model;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QueryBuilder
{
    protected $model;
    protected $query;
    protected $elasticClient;
    protected $relations = [];

    public function __construct(Model $model)
    {
        $this->model         = $model;
        $this->elasticClient = ClientBuilder::create()->setHosts(['elasticsearch:9200'])->build();
    }

    public function get()
    {
        $params = [
            'index' => $this->model->elasticIndex,
            'body'  => $this->query,
        ];
        $response = $this->sendQuery($params, 'search');
        $items    = Arr::get($response, 'hits.hits');
        $items    = array_map(function ($item) {
            $data = $item['_source'];
            $data['id'] = $item['_id'];
            return $this->convertToModel(clone $this->model,$data);
        }, $items);
        return $items;
    }

    public function with($relations)
    {
        $relations = is_string($relations) ? func_get_args() : $relations;
        foreach ($relations as $relation) {
            if(method_exists($this->model,$relation)){
                $this->relations[$relation] = $this->model->$relation()->getRelated();
            }
        }
        return $this;
    }

    public function all()
    {
        return $this->get();
    }

    public function paginate($perPage)
    {
        dd('perpage', $perPage);
    }

    public function find($id)
    {
        $params = [
            'index' => $this->model->elasticIndex,
            'id'    => $id
        ];

        $response = $this->sendQuery($params, 'get');

        $data       = $response['_source'];
        $data['id'] = $response['_id'];
        return $this->convertToModel($data);
    }

    public function first()
    {
    }

    public function search($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param $model
     * @param $data
     * @return mixed
     */
    protected function formatDates($model,$data)
    {
        foreach ($model->getDates() as $dateField) {
            if (isset($data[$dateField])) {
                $data[$dateField] = Carbon::createFromDate($data[$dateField]);
            }
        }
        return $data;
    }

    protected function sendQuery($params, $method)
    {
        try {
            return $this->elasticClient->{$method}($params);
        } catch (Missing404Exception $exception) {
            throw new ModelNotFoundException($this->model);
        }
    }

    /**
     * @param $model
     * @param $data
     * @return mixed
     */
    protected function convertToModel($model,$data)
    {
        $this->model->elasticFields = array_merge($this->model->elasticFields,array_keys($this->relations));
        if (!empty($this->model->elasticFields)) {
            $data = Arr::only($data, $this->model->elasticFields);
        }
        $model->exists = true;
        $data                = $this->formatDates($model,$data);
        foreach ($this->relations as $with=>$relation) {
            if(isset($data[$with])){
               $model->setRelation($with,$this->convertToModel($relation,$data[$with]));
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

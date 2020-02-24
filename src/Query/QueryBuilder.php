<?php

namespace Sedehi\ElasticCollection\Query;

use Elasticsearch\ClientBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Sedehi\ElasticCollection\Traits\HasDates;
use Sedehi\ElasticCollection\Traits\ToModel;

class QueryBuilder
{
    use HasDates, ElasticQuery,HasRelationships,ToModel;

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
            return $this->convertToModel(clone $this->model, $data);
        }, $items);
        return $items;
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
        return $this->convertToModel($this->model, $data);
    }

    public function findOrFail($id)
    {
        $data = $this->find($id);

        if ($data === null) {
            throw new ModelNotFoundException($this->model);
        }

        return $data;
    }

    public function first()
    {
        $params = [
            'index' => $this->model->elasticIndex,
            'size'  => 1
        ];

        $response = $this->sendQuery($params, 'search');
        $items    = Arr::get($response, 'hits.hits');
        $first    = head($items);
        if ($first === false) {
            return null;
        }
        $data       = $first['_source'];
        $data['id'] = $first['_id'];
        return $this->convertToModel($this->model, $data);
    }

    public function firstOrFail()
    {
        $data = $this->first();

        if ($data === null) {
            throw new ModelNotFoundException($this->model);
        }

        return $data;
    }

    public function search($query)
    {
        $this->query = $query;
        return $this;
    }
}

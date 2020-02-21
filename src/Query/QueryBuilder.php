<?php

namespace Sedehi\ElasticCollection\Query;

use Elasticsearch\ClientBuilder;
use Illuminate\Database\Eloquent\Model;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QueryBuilder
{
    protected $model;
    protected $query;
    protected $elasticClient;

    public function __construct(Model $model)
    {
        $this->model         = $model;
        $this->elasticClient = ClientBuilder::create()->setHosts(['elasticsearch:9200'])->build();
    }

    public function get()
    {
        if ($this->query !== null) {
            try {
                $params = [
                    'index' => $this->model->elasticIndex,
                    'body'  => $this->query,
                ];
                $response = $this->elasticClient->search($params);
                dd($response);
            } catch (Missing404Exception $exception) {
                throw new ModelNotFoundException($this->model);
            }
        }
    }

    public function all()
    {
        dd('all');
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

        try {
            $response = $this->elasticClient->get($params);
        } catch (Missing404Exception $exception) {
            throw new ModelNotFoundException($this->model);
        }
        $data = $response['_source'];

        $data['id']          = $response['_id'];
        $this->model->exists = true;
        return $this->model->forceFill($data);
    }

    public function first()
    {
    }

    public function search($query)
    {
        $this->query = $query;
        return $this;
    }
}

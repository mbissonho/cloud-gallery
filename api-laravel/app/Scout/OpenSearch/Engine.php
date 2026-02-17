<?php

namespace App\Scout\OpenSearch;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Laravel\Scout\Engines\Engine as ScoutEngine;
use Laravel\Scout\Builder;
use Exception;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

class Engine extends ScoutEngine
{
    protected $softDelete;
    protected $url;

    public function __construct($softDelete = false)
    {
        $this->softDelete = $softDelete;
        $this->url = config('scout.opensearch.host');
    }

    public function update($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $index = $models->first()->searchableAs();

        if(in_array(SoftDeletes::class, class_uses_recursive($models->first())) && $this->softDelete){
            $models->each->pushSoftDeleteMetadata();
        }

        $objects = $models->map(function ($model) {
            if (empty($searchableData = $model->toSearchableArray())) {
                return;
            }
            return array_merge(
                [
                    'id' => $model->getScoutKey()
                ],
                $searchableData
            );
        })->filter()->values()->all();

        if (! empty($objects)) {
            foreach($objects as $object){
                Http::withBasicAuth(config('scout.opensearch.user'), config('scout.opensearch.pass'))
                    ->post($this->url . '/' . $index . '/_doc/' . $object['id'], $object);
            }
        }
    }

    public function delete($models){

        $index = $models->first()->searchableAs();

        $ids = $models->map(function ($model) {
            return $model->getScoutKey();
        })->values()->all();

        foreach($ids as $id){
            $response = Http::withBasicAuth(config('scout.opensearch.user'), config('scout.opensearch.pass'))
                ->delete($this->url . '/' . $index . '/_doc/' . $id);
            self::errors($response);
        }
    }

    /**
     * @throws ConnectionException
     */
    public function search(Builder $builder) {
        return $this->performSearch($builder, array_filter([
            'filters' => $this->filters($builder),
            'limit' => $builder->limit,
        ]));
    }

    /**
     * @throws ConnectionException
     */
    public function paginate(Builder $builder, $limit, $page){
        return $this->performSearch($builder, [
            'filters' => $this->filters($builder),
            'limit' => $limit,
            'page' => $page,
        ]);
    }

    public function mapIds($results): Collection
    {
        return collect($results['hits'])->pluck('_id');
    }

    public function map(Builder $builder, $results, $model)
    {
        /* @var Model|Searchable $model  */
        if (!is_array($results) || count($results['hits']) === 0) {
            return $model->newCollection();
        }

        $avoidMapWithPersistedModels = true;
        if($avoidMapWithPersistedModels) {
            $models = collect($results['hits'])->map(function ($hit) use ($model) {
                $instance = $model->newInstance();
                $instance->forceFill($hit['_source']);
                $instance->exists = true;
                return $instance;
            });

            return \Illuminate\Database\Eloquent\Collection::make($models);
        }

        $idsCollection = $this->mapIds($results);
        $positions = array_flip($idsCollection->toArray());

        return $model->getScoutModelsByIds($builder, $idsCollection->toArray())->sortBy(function ($model) use ($positions) {
            return $positions[$model->getScoutKey()];
        })->values();
    }

    public function lazyMap(Builder $builder, $results, $model): LazyCollection
    {
        /* @var Model|Searchable $model  */
        if (count($results['hits']) === 0) {
            return LazyCollection::make($model->newCollection());
        }

        $idsCollection = $this->mapIds($results);
        $positions = array_flip($idsCollection->toArray());

        return $model->queryScoutModelsByIds(
            $builder, $idsCollection->toArray()
        )->cursor()->sortBy(function ($model) use ($positions) {
            return $positions[$model->getScoutKey()];
        })->values();
    }

    public function getTotalCount($results): int
    {
        return (int) Arr::get($results, 'total.value');
    }

    /**
     * @throws Exception
     */
    public function flush($model)
    {
        /* @var Model|Searchable $model  */
        $index = $model->searchableAs();
        $this->deleteIndex($index);
    }

    public function createIndex($name, array $options = [])
    {
        throw new Exception('OpenSearch indexes are created automatically upon adding objects.');
    }

    public function deleteIndex($name)
    {
        $response = Http::withBasicAuth(config('scout.opensearch.user'), config('scout.opensearch.pass'))
            ->delete($this->url . '/' . $name);
        if($response->status() != 200){
            return throw new Exception($response->reason());
        }
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                $this,
                $builder->query,
                $options
            );
        }

        $url = $this->url . '/' . $builder->model->searchableAs() . '/_search';
        $body = $this->body($builder, $options);
        $response = Http::withBasicAuth(config('scout.opensearch.user'), config('scout.opensearch.pass'))
            ->post($url, $body);

        self::errors($response);
        return $response->json('hits');
    }


    protected function body(Builder $builder, $options): array
    {
        $size = Arr::get($options, 'limit', 10);
        $page = Arr::get($options, 'page', 1);
        $body = [
            'size' => $size,
            'from' => ($page - 1) * $size,
            '_source' => true,
            'query' => [
                'bool' => [
                    'filter' => $this->buildFilterClause($options)
                ]
            ]
        ];


        if(!empty($builder->query) || !empty($builder->orders)) {
            return $builder->model->processBody($builder, $body, $options);
        }

        return $body;
    }

    protected function filters(Builder $builder): array
    {
        return array_merge($builder->wheres, $builder->whereIns);
    }

    protected function buildFilterClause(array $options): array
    {
        $result = [];
        if(!isset($options['filters'])) return $result;

        foreach ($options['filters'] as $filterIndex => $filterValue) {
            $result[] = [
                'term' => [$filterIndex => is_array($filterValue) ? $filterValue[0] : $filterValue]
            ];
        }

        return $result;
    }

    public static function errors(Response $response){
        if($response->status() != 200){
            $data = $response->json();
            $reason = Arr::has($data, 'error.reason') ? Arr::get($data, 'error.reason') : $response->getReasonPhrase();
            return throw new Exception($reason);
        }
    }
}

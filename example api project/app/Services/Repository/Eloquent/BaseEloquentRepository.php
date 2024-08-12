<?php

namespace App\Services\Repository\Eloquent;

use App\Services\Repository\CriteriaInterface;
use App\Services\Repository\Exceptions\RepositoryException;
use App\Services\Repository\RepositoryInterface;
use Closure;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

abstract class BaseEloquentRepository implements RepositoryInterface
{
	protected Model $model;
	protected Builder $query;
	protected Collection $criteria;
	protected Collection $conditions;

	protected const DEFAULT_PAGINATE_LIMIT = 100;

	/**
	 * @throws RepositoryException
	 */
	public function __construct(protected Application $app)
	{
		$this->criteria = new Collection();
		$this->makeModel();
		$this->resetQuery();
	}

	public function get($columns = ['*']) : Collection
	{
		$this->prepareQuery();

		$results = $this->query->get($columns);

		$this->resetQuery();

		return $this->parseCollectionResult($results);
	}

	public function count($columns = ['*']) : int
	{
		$this->prepareQuery();

		$result = $this->query->count($columns);

		$this->resetQuery();

		return $result;
	}

	/**
	 * @throws RepositoryException
	 */
	public function paginate(int $limit = null, array $columns = ['*'], string $method = 'paginate') : LengthAwarePaginator
	{
		$this->prepareQuery();

		$limit = $limit ?? static::DEFAULT_PAGINATE_LIMIT;
		$results = $this->query->{$method}($limit, $columns);
		$results->appends(app('request')->query());

		$this->resetQuery();

		return $results;
	}

	public function first($columns = ['*']) : ?Model
	{
		$this->prepareQuery();

		$resultModel = $this->query->first($columns);

		$this->resetQuery();

		return $resultModel;
	}

	/**
	 * @throws ModelNotFoundException
	 */
	public function findOrFail($id, $columns = ['*']) : Model
	{
		$this->prepareQuery();
		$resultModel = $this->query->findOrFail($id, $columns);

		$this->resetQuery();

		return $resultModel;
	}

    /**
     * @throws \Throwable
     */
    public function create(array $attributes) : Model
	{
		DB::beginTransaction();

		try {
			$resultModel = $this->model->newInstance($attributes);
			$resultModel->save();
			$this->resetQuery();

			$this->invalidateCache();

			DB::commit();

			return $resultModel;
		} catch (\Throwable $e) {
			DB::rollBack();
			throw $e;
		}
	}

    /**
     * @throws \Throwable
     */
    public function update(array $attributes, $id) : Model
	{
		DB::beginTransaction();

		try {
			$resultModel = $this->query->findOrFail($id);

			$resultModel->fill($attributes);
			$resultModel->save();

			$this->resetQuery();

			$this->invalidateCache();

			DB::commit();

			return $resultModel;
		} catch (\Throwable $e) {
			DB::rollBack();
			throw $e;
		}
	}

    /**
     * @throws \Throwable
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
	{
		DB::beginTransaction();

		try {
			$resultModel = $this->query->updateOrCreate($attributes, $values);

			$this->resetQuery();

            DB::commit();

			return $resultModel;

		} catch (\Throwable $e) {
			DB::rollBack();
			throw $e;
		}
	}

    /**
     * @throws \Throwable
     */
    public function delete($id) : int|bool
	{
		DB::beginTransaction();

		try {
			$resultModel = $this->findOrFail($id);
			$this->resetQuery();
			$result = $resultModel->delete();

            DB::commit();

            return $result;
		} catch (\Throwable $e) {
			DB::rollBack();
			throw $e;
		}
	}

	public function orderBy($column, $direction = 'asc'): static
	{
		$this->query = $this->query->orderBy($column, $direction);

		return $this;
	}

	public function with($relations): static
	{
		$this->query = $this->query->with($relations);

		return $this;
	}

	public function pushCriteria(CriteriaInterface $criteria): static
	{
		$this->criteria->push($criteria);

		return $this;
	}

	public function pushCondition(array $where): static
	{
		$this->conditions->push($where);

		return $this;
	}

	public function setLimit(int $limit): static
	{
		$this->query = $this->query->take($limit);

		return $this;
	}

	public function setOffset(int $offset): static
	{
		$this->query = $this->query->skip($offset);

		return $this;
	}

	protected function applyCriteria(): static
	{
		foreach ($this->criteria as $c) {
			$this->model = $c->apply($this->query, $this);
		}

		return $this;
	}

	public function resetCriteria(): static
	{
		$this->criteria = new Collection();

		return $this;
	}

	public function resetConditions(): static
	{
		$this->conditions = new Collection();

		return $this;
	}

	public function withTrashed() : static
	{
		$this->model = $this->model->withTrashed();

		return $this;
	}

	public function withoutTrashed() : static
	{
		$this->model = $this->model->withoutTrashed();

		return $this;
	}

	protected function makeModel(): void
	{
		$newModel = $this->app->make($this->model());

		if (!$newModel instanceof Model) {
			throw new RepositoryException("Class {$this->model()} must be an instance of " . Model::class);
		}

		$this->model = $newModel;
	}

	protected function resetQuery(): void
	{
		$this->query = $this->model->newQuery();
		$this->criteria = new Collection();
		$this->conditions = new Collection();
	}

	protected function prepareQuery() : void
	{
		$this->applyCriteria();
		$this->applyConditions();
	}

	protected function parseCollectionResult(mixed $result) : Collection
	{
		if ($result instanceof EloquentCollection) {
			return $result->toBase();
		}

		return $result;
	}

	protected function invalidateCache() : void
	{
		// It's base empty, using to flush cache for extended repository if needed
	}

	protected function getCacheKey(string $key) : string
	{
		return sprintf(
			'%s%s%s',
			$key,
			$this->criteria->map(function ($item, $key) {
				return ['criteria' => get_class($item)];
			})->implode('criteria', ''),
			$this->query->toSql(),
		);
	}

	protected function applyConditions(): void
	{
		foreach ($this->conditions as $field => $value) {
			if (!is_array($value)) {
				$this->query = $this->query->where($field, '=', $value);
			} else {
				[$field, $condition] = $value;
				$val = $value[2] ?? $condition;
				$condition = '=';

				//smooth input
				$condition = preg_replace('/\s\s+/', ' ', trim($condition));

				//split to get operator, syntax: "DATE >", "DATE =", "DAY <"
				$operator = explode(' ', $condition);
				if (count($operator) > 1) {
					$condition = $operator[0];
					$operator = $operator[1];
				} else {
					$operator = '=';
				}

				switch (strtoupper($condition)) {
					case 'IN':
						if (!is_array($val)) throw new RepositoryException("Input {$val} mus be an array");
						$this->query = $this->query->whereIn($field, $val);
						break;
					case 'NOTIN':
						if (!is_array($val)) throw new RepositoryException("Input {$val} mus be an array");
						$this->query = $this->query->whereNotIn($field, $val);
						break;
					case 'DATE':
						$this->query = $this->query->whereDate($field, $operator, $val);
						break;
					case 'DAY':
						$this->query = $this->query->whereDay($field, $operator, $val);
						break;
					case 'MONTH':
						$this->query = $this->query->whereMonth($field, $operator, $val);
						break;
					case 'YEAR':
						$this->query = $this->query->whereYear($field, $operator, $val);
						break;
					case 'EXISTS':
						if (!($val instanceof Closure)) throw new RepositoryException("Input {$val} must be closure function");
						$this->query = $this->query->whereExists($val);
						break;
					case 'HAS':
						if (!($val instanceof Closure)) throw new RepositoryException("Input {$val} must be closure function");
						$this->query = $this->query->whereHas($field, $val);
						break;
					case 'HASMORPH':
						if (!($val instanceof Closure)) throw new RepositoryException("Input {$val} must be closure function");
						$this->query = $this->query->whereHasMorph($field, $val);
						break;
					case 'DOESNTHAVE':
						if (!($val instanceof Closure)) throw new RepositoryException("Input {$val} must be closure function");
						$this->query = $this->query->whereDoesntHave($field, $val);
						break;
					case 'DOESNTHAVEMORPH':
						if (!($val instanceof Closure)) throw new RepositoryException("Input {$val} must be closure function");
						$this->query = $this->query->whereDoesntHaveMorph($field, $val);
						break;
					case 'BETWEEN':
						if (!is_array($val)) throw new RepositoryException("Input {$val} must be an array");
						$this->query = $this->query->whereBetween($field, $val);
						break;
					case 'BETWEENCOLUMNS':
						if (!is_array($val)) throw new RepositoryException("Input {$val} must be an array");
						$this->query = $this->query->whereBetweenColumns($field, $val);
						break;
					case 'NOTBETWEEN':
						if (!is_array($val)) throw new RepositoryException("Input {$val} must be an array");
						$this->query = $this->query->whereNotBetween($field, $val);
						break;
					case 'NOTBETWEENCOLUMNS':
						if (!is_array($val)) throw new RepositoryException("Input {$val} must be an array");
						$this->query = $this->query->whereNotBetweenColumns($field, $val);
						break;
					case 'RAW':
						$this->query = $this->query->whereRaw($val);
						break;
					default:
						$this->query = $this->query->where($field, $condition, $val);
				}
			}
		}
	}

	abstract protected function model() : string;
}
<?php

namespace App\Services\Repository;

interface RepositoryInterface
{
	public function get(array $columns = ['*']);
	public function count(array $columns = ['*']);
	public function paginate(int $limit = null, array $columns = ['*']);
	public function findOrFail(int $id, array $columns = ['*']);
	public function create(array $attributes);
	public function update(array $attributes, $id);
	public function updateOrCreate(array $attributes, array $values = []);
	public function delete(int $id) : int|bool;
	public function orderBy(string $column, string $direction = 'asc') : static;
	public function with(array $relations) : static;
	public function pushCriteria(CriteriaInterface $criteria) : static;
	public function pushCondition(array $where) : static;
	public function resetCriteria() : static;
	public function resetConditions() : static;
	public function first($columns = ['*']);
	public function setLimit(int $limit): static;
	public function setOffset(int $offset): static;
}
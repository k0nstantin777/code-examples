<?php

namespace App\Services\Repository;

interface CriteriaInterface
{
	public function apply($query, RepositoryInterface $repository);
}
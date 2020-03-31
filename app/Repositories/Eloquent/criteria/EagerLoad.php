<?php

namespace App\Repositories\Eloquent\Criteria;

use App\Repositories\Criteria\ICriterion;

class EagerLoad implements ICriterion
{

	protected $relationsships;

	public function __construct($relationsships)
	{
		$this->relationsships = $relationsships;
	}
	public function apply($model)
	{
		return $model->with($this->relationsships);
	}

}
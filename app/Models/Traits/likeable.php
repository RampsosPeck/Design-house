<?php


namespace App\Models\Traits;

use App\Models\Like;
use App\Models\Traits\isLikedByUser;


trait Likeable
{
	public static function bootLikeable()
	{
		static::deleting(function($model){
			$model->removeLikes();
		});
	}

	// Eliminar me gusta cuando se está eliminando el modelo
	public function removeLikes()
	{
		if($this->likes()->count()){
			$this->likes()->delete();
		}
	}

	public function likes()
	{
		return $this->morphMany(Like::class, 'likeable');
	}

	public function like()
	{
		if(! auth()->check()) return;

		//Comprobar si al usuario actual ya le ha gustado el modelo
		if($this->isLikedByUser(auth()->id())){
			return;
		};

		$this->likes()->create(['user_id' => auth()->id()]);
	}
	public function unlike()
	{
		if(! auth()->check()) return;

		if(! $this->isLikedByUser(auth()->id()))
		{
			return;
		}
		$this->likes()->where('user_id', auth()->id())->delete();
	}

	public function isLikedByUser($user_id)
	{
		return (bool)$this->likes()->where('user_id', $user_id)->count();
	}



}




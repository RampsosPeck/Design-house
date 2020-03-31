<?php

namespace App\Repositories\Eloquent;
use App\Models\Design;
use App\Models\Traits\isLikedByUser;
use App\Repositories\Contracts\IDesign;
use App\Repositories\Eloquent\BaseRepository;


class DesignRepository extends BaseRepository implements IDesign
{

	public function model()
	{
		return Design::class;  // 'App\Models\Design'
	}

	/*public function allLive()
	{
		return $this->model->where('is_live', true)->get();
	}*/

	public function applyTags($id, array $data)
	{
		$design = $this->find($id);
		$design->retag($data);
	}

	public function addComment($designId, array $data)
	{
		//Obtenga el diseño para el que queremos crear un comentario
		$design = $this->find($designId);

		//Crea el comentario para el diseño
		$comment = $design->comments()->create($data);

		return $comment;
	}

	public function like($id)
	{
		$design = $this->model->findOrFail($id);
		if($design->isLikedByUser(auth()->id()))
		{
			$design->unlike();
		} else {
			$design->like();
		}

	}

	public function isLikedByUser($id)
	{
		$design = $this->model->findOrFail($id);

		return $design->isLikedByUser(auth()->id());

	}

}








<?php

namespace App\Repositories\Eloquent;
use App\Models\Design;
use App\Models\Traits\isLikedByUser;
use App\Repositories\Contracts\IDesign;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Http\Request;


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
		return $design->likes()->count();
	}

	public function isLikedByUser($id)
	{
		$design = $this->model->findOrFail($id);

		return $design->isLikedByUser(auth()->id());

	}

	public function search(Request $request)
	{
		$query = (new $this->model)->newQuery();

		$query->where('is_live', true);

		// Devolver solo diseños con comentarios
		if($request->has_comments){
			$query->has('comments');
		}

		// Devolver solo diseños a equipos asignados
		if($request->has_has_team){
			$query->has('team');
		}

		//http://mysite.com?q=hello-world&
		// Buscar título y descripción de la cadena proporcionada
		if($request->q){
			$query->where(function($q) use ($request){
				$q->where('title', 'like', '%'.$request->q.'%')
					->orWhere('description','like', '%'.$request->q.'%');
			});
		}

		// Ordenar la consulta porque me gusta o último primero
		if($request->orderBy=='likes'){
			$query->withCount('likes') //likes_count
					->orderByDesc('likes_count');
		}else{
			$query->latest();
		}

		return $query->get();
	}

}








<?php

namespace App\Http\Controllers\Designs;

use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use App\Models\Design;
use App\Policies\DesignPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DesignController extends Controller
{
    public function update(Request $request, $id)
    {
	    $design = Design::findOrFail($id);
	    $this->authorize('update', $design);

    	$this->validate($request, [
    		'title' => ['required', 'unique:designs,title,'.$id],
    		'description' => ['required', 'string', 'min:20', 'max:140'],
    		'tags' => ['required']
    	]);

	    $design->update([
	    	'title' => $request->title,
	    	'description' => $request->description,
	    	'slug' => Str::slug($request->title),
	    	'is_live' => ! $design->upload_successful ? false : $request->is_live
	    ]);

	    //apply the tags
	    $design->retag($request->tags);

	    return new DesignResource($design);

	    //return response()->json($design, 200);
	}

	public function destroy($id)
	{
		$design = Design::findOrFail($id);
	    $this->authorize('delete', $design);

	    //eliminar los archivos asociados al registro
	    foreach(['thumbnail','large','original'] as $size){
	    	if(Storage::disk($design->disk)->exists("uploads/designs/{$size}/".$design->image)){
	    		Storage::disk($design->disk)->delete("uploads/designs/{$size}/".$design->image);
	    	}
	    }

	    $design->delete();

	    return response()->json(['message'=> 'Registro eliminado'], 200);

	}
}











<?php

namespace App\Http\Controllers\Designs;

use App\Http\Controllers\Controller;
use App\Jobs\UploadImage;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    //
    public function upload(Request $request)
    {
    	$this->validate($request, [
    		'image' => ['required', 'mimes:jpeg,gif,bmp,png', 'max:2048']
    	]);

    	//Obtener la imagen
    	$image = $request->file('image');
    	$image_path = $image->getPathName();

    	//Obtener el nombre del archivo original y reemplazar los espacios con un subrayado
    	// Ejm. "Business Card.png = timestamp()_business_cards.png"
    	$filename = time()."_".preg_replace('/\s+/', '_', strtolower($image->getClientOriginalName()));

    	//Mover la imagen a la ubicación temporal (tmp)
    	$tmp = $image->storeAs('uploads/original', $filename, 'tmp');

    	//Crear el registro de la base de datos para el diseño
		$design = Auth()->user()->designs()->create([
			'image' => $filename,
			'disk' => config('site.upload_disk')
		]);

		//Despachar un trabajo "job" para manejar la manipulación de la imagen
		$this->dispatch(new UploadImage($design));

		return response()->json($design, 200);

    }

}






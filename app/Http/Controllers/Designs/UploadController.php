<?php

namespace App\Http\Controllers\Designs;

use App\Jobs\UploadImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\IDesign;
use Illuminate\Http\storeAs;

class UploadController extends Controller
{
    protected $designs;

    public function __construct(IDesign $designs)
    {
        $this->designs = $designs;
    }
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
        /*$design = $this->designs()->create([
            'user_id' => auth()->id(),
            'image' => $filename,
            'disk' => config('site.upload_disk')
        ]);*/

		//Despachar un trabajo "job" para manejar la manipulación de la imagen
		$this->dispatch(new UploadImage($design));

		//UploadImage::dispatch($design);

		return response()->json($design, 200);

    }

}






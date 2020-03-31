<?php
/*
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Public routes
 Route::get('/', function(){
 	return response()->json(['message'=>'Hola mundo!'],200);
 });*/
 Route::get('me', 'User\MeController@getMe');


// Obtener Designs
Route::get('designs', 'Designs\DesignController@index');
Route::get('designs/{id}', 'Designs\DesignController@findDesign');

// Obtener Usuarios
Route::get('users', 'User\UserController@index');

 //Route group for authenticated users only
 Route::group(['middleware' => ['auth:api']], function(){
 	Route::post('logout', 'Auth\LoginController@logout');
 	Route::put('settings/profile', 'User\SettingsController@updateProfile');
 	Route::put('settings/password', 'User\SettingsController@updatePassword');

 	// Upload Designs
 	Route::post('designs', 'Designs\UploadController@upload');
 	Route::put('designs/{id}', 'Designs\DesignController@update');
 	Route::delete('designs/{id}', 'Designs\DesignController@destroy');

 	//Likes and Unlikes
 	Route::post('designs/{id}/like', 'Designs\DesignController@like');
 	Route::get('designs/{id}/liked', 'Designs\DesignController@checkIfUserHasLiked');

 	// Comments
 	Route::post('designs/{id}/comments', 'Designs\CommentController@store');
 	Route::put('comments/{id}', 'Designs\CommentController@update');
 	Route::delete('comments/{id}', 'Designs\CommentController@destroy');
 });

 //Route group for guests only
 Route::group(['middleware' => ['guest:api']], function(){
 	Route::post('register', 'Auth\RegisterController@register');
 	Route::post('verification/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
 	Route::post('verification/resend', 'Auth\VerificationController@resend');
 	Route::post('login', 'Auth\LoginController@login');
 	Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
 	Route::post('password/reset', 'Auth\ResetPasswordController@reset');



 });

//Auth::routes();



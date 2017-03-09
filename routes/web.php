<?php
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', 'UserController@test');
Route::post('/test', 'UserController@test');
Route::resource('users', 'UserController');
Route::resource('profils', 'ProfilController');
Route::resource('photos', 'PhotoController');
Route::resource('likes', 'LikesController');
Route::resource('shows', 'ShowsController');
Route::resource('tags', 'TagsController');

Route::get('images/{filename}', function ($filename)
{
     $path = storage_path() . '/app/images/'.$filename;
     if(!File::exists($path)) return;
    return Image::make($path)->response();

    return $response;
});


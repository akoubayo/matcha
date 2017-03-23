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
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:Content-Type, X-Auth-Token, Origin, Authorization, x-xsrf-token');


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
Route::resource('notifs', 'NotifsController');

Route::get('images/{filename}', function ($filename)
{

    $path = storage_path() . '/app/images/'.$filename;
    if(!File::exists($path)) return;
    $file = File::get($path);
    $type = File::mimeType($path);
    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Route::get('small/{filename}', function ($filename)
{

    $path = storage_path() . '/app/small/'.$filename;
    if(!File::exists($path)) return;
    $file = File::get($path);
    $type = File::mimeType($path);
    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});



<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;

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
    return view('auth.login');
});


Route::middleware('auth')->get('/articles',[ArticleController::class, 'index']);

Route::middleware('auth')->get('/exportarticles', [ArticleController::class, 'export'])->name('export.articles');

//Route::get('test',[ArticleController::class,'getWriteTasks']);

Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::view('/{any}', 'home')
    ->middleware('auth')
    ->where('any', '.*');

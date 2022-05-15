<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('get-articles', [ArticleController::class, 'datatableIndex']);
Route::post('update-article', [ArticleController::class, 'updateArticle']);
Route::get('delete/{id}',[ArticleController::class, 'delete']);
Route::get('status/{id}', [ArticleController::class, 'status']);
Route::get('exportarticles/{id}/{date}', [ArticleController::class, 'export']);
Route::get('companies', [ArticleController::class, 'companies']);
Route::post('deletebulk',[ArticleController::class, 'deleteBulk']);

Route::get('clear',[ArticleController::class, 'clear']);

Route::get('article-details/{id}', [ArticleController::class, 'show']);

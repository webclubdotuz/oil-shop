<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Category;
use App\Models\Brand;

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


//get categories
Route::get('categories', function(Request $request) {
    $perPage = $request->perPage ?: 7;
    $categories = Category::where('deleted_at', '=', null)
      ->paginate($perPage, ['id', 'name']);
    return response()->json([
      'data' => $categories->items(),
      'current_page' => $categories->currentPage(),
      'last_page' => $categories->lastPage()
    ]);
  });

  //get brands
Route::get('brands', function(Request $request) {
    $perPage = $request->perPage ?: 7;
    $brands = Brand::where('deleted_at', '=', null)
      ->paginate($perPage, ['id', 'name']);
    return response()->json([
      'data' => $brands->items(),
      'current_page' => $brands->currentPage(),
      'last_page' => $brands->lastPage()
    ]);
  });
  

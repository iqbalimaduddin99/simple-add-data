<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Helpers\ApiResponse;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return ApiResponse::success($categories, 'Master category list');
    }

}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->active()
            ->orderBy('id')
            ->get();

        return ApiResponse::success(
            CategoryResource::collection($categories),
            __('api.category.retrieved')
        );
    }

    public function show(Category $category): JsonResponse
    {
        if (! $category->is_active) {
            abort(404);
        }

        return ApiResponse::success(
            new CategoryResource($category),
            __('api.category.show')
        );
    }
}

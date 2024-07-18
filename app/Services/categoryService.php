<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Exhibition_category;
use Illuminate\Support\Facades\DB;

class categoryService
{
    public function addCategory($request, $exhibition_id): array
    {
        $data = [];
        try {
            $category = Category::create([
                'name' => $request['name'],
            ]);

            // Create the association record
            $category_exh= Exhibition_category::query()->create([
                'category_id' => $category['id'],
                'exhibition_id' => $exhibition_id,
            ]);

            $data = $category;
            $message = 'category added successfully.';
            $code = 200;

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

    public function deleteCategory($category_id){
        DB::beginTransaction();
        try {
            $category=Category::query()->find($category_id);
            $category->delete();
            DB::commit();
            $message=' category deleted successfully. ';
            $code = 200;
            return ['data' => [], 'message' => $message, 'code' => $code];

        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during deleting category. Please try again ';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];
        }
    }

    public function showExhibitionCategory($exhibition_id){
        DB::beginTransaction();

        try {
            $categories = Exhibition_category::where('exhibition_id', $exhibition_id)->get();
            DB::commit();
            $data = $categories;
            $message = 'categories  have been successfully show.';
            $code = 200;
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];

    }

}

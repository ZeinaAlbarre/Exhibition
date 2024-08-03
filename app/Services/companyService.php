<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class companyService
{
    public function addProduct($request): array
    {
        $data = [];
        try {
            $user=Auth::user();

            $img=Str::random(32).".".time().'.'.request()->img->getClientOriginalExtension();

            $product = Product::create([
                'info'=>$request['info'],
                'img' => $img,
                'user_id' => $user->id,
            ]);
            Storage::disk('public')->put($img, file_get_contents($request['img']));

            $data = $product;
            $message = 'Product added successfully.';
            $code = 200;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

    public function deleteProduct($id): array
    {
        $data = [];
        try {
            $product = Product::query()->where('id',$id)->first();
            $product->delete();
            $message = 'Product deleted successfully.';
            $code = 200;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

    public function updateProduct($request, $id): array
    {
        $data = [];
        try {
            $product = Product::findOrFail($id);

            if(!is_null(request()->img))
            {
                $img=Str::random(32).".".time().'.'.request()->img->getClientOriginalExtension();
                Storage::disk('public')->put($img, file_get_contents($request['img']));
                $product->update(
                    [
                        'img' => $img,
                    ]
                );
            }
            $product->update(
                [
                    'info'=>request()->info
                ]
            );
            $product->save();
            $data = $product;
            $message = 'Product updated successfully.';
            $code = 200;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];
    }


    public function showProducts($id): array
    {
        $data = [];
        try {
            $products = Product::query()->where('user_id',$id)->get();
            $data = $products;
            $message = 'Products show successfully.';
            $code = 200;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

}

<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function showCompanies()
    {
        DB::beginTransaction();
        try {
            $company=Company::all();
            DB::commit();
            $data = $company;
            $message = '';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing company . Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function searchCompany($request)
    {
        DB::beginTransaction();
        try {
            $name = $request['name'];
            $company=Company::query()->where('company_name', 'LIKE', '%'.$name.'%')->get();
            DB::commit();
            $data = $company;
            $message = 'successfully searched. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during search on company. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function removeCompany($company_id)
    {
        DB::beginTransaction();
        try {
            $company=Company::query()->findOrFail($company_id);
            $user=User::query()->where('userable_id',$company_id)->where('userable_type','App\Models\Company')->first();
            $company->delete();
            $user->delete();
            DB::commit();
            $data = [];
            $message = 'Company removed successfully. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during removing company. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $e->getMessage(), 'code' => $code];

        }
    }

    public function showRegisterCompanyExhibition()
    {
        DB::beginTransaction();
        try {
            $user=Auth::user();
            $exhibition_company=Exhibition_company::query()->where('user_id',$user->id)->get();
            $exhibitions=[];
            if($exhibition_company){
                foreach ($exhibition_company as $item) {
                    $exhibition = Exhibition::query()->where('id', $item['exhibition_id'])->first();
                    $exhibitions[]=$exhibition;
                }
                DB::commit();
                $data = $exhibitions;
                $message = 'Register exhibition has been shown successfully. ';
                $code = 200;
            }
            else{
                DB::commit();
                $data = [];
                $message = 'There are no Register exhibition. ';
                $code = 200;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibition who company register in it . Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function showUnRegisterCompanyExhibition()
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $exhibition_company = Exhibition_company::query()
                ->where('user_id', $user->id)
                ->pluck('exhibition_id')
                ->toArray();
            $exhibitions = Exhibition::query()
                ->whereNotIn('id', $exhibition_company)
                ->get();
            DB::commit();
            $data = $exhibitions;
            $message = 'Unregistered exhibitions have been shown successfully.';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibition who company register in it . Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $e->getMessage(), 'code' => $code];

        }
    }


}

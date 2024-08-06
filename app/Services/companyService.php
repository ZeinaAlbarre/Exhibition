<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Product;
use App\Models\Stand;
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
            $company=Company::with('user')->get();
            DB::commit();
            $data = $company;
            $message = 'Companies has been shown successfully';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing companies . Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $e->getMessage(), 'code' => $code];

        }
    }

    public function searchCompany($request)
    {
        DB::beginTransaction();
        try {
            $name = $request['name'];
            $company=Company::query()->where('company_name', 'LIKE', '%'.$name.'%')->with('user')->get();
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
            $exhibition_company=Exhibition_company::query()->where('user_id',$user->id)
                ->whereIn('status',[1,2])->get();
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
                ->where('status',[1,2])
                ->pluck('exhibition_id')
                ->toArray();
            $exhibitions = Exhibition::query()
                ->whereNotIn('id', $exhibition_company)
                ->whereIn('status',[2,3])
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

    public function showCompanyStand($stand_id)
    {
        DB::beginTransaction();
        try {
            $companyStand=Company_stand::query()->where('stand_id',$stand_id)->where('status',1)->first();
            $company=Company::query()->where('id',$companyStand['company_id'])->with('user')->first();
            DB::commit();
            $data = $company;
            $message = 'Company stand shown successfully. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing company stand. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function showMyStand($exhibition_id)
    {
        DB::beginTransaction();
        try {
            $user=Auth::user();
            $company=Company::query()->where('id',$user['userable_id'])->first();
            $stand=Stand::query()->where('exhibition_id',$exhibition_id)->pluck('id');
            $companyStand=Company_stand::query()->where('company_id',$company['id'])->whereIn('stand_id',$stand)->where('status',1)->first();
            if($companyStand){
                $stand=Stand::query()->findOrFail($companyStand['stand_id']);
                DB::commit();
                $data = $stand;
                $message = 'Stand has been shown successfully. ';
                $code = 200;
            }
            else{
                DB::commit();
                $data = [];
                $message = 'You do not book any stand yet';
                $code = 200;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibition Request. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }
}

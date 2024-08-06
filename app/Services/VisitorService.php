<?php

namespace App\Services;

use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;


class VisitorService
{
    public function showVisitors()
    {
        DB::beginTransaction();
        try {
            $user=User::query()->where('userable_type','App\Models\Visitor')->with('userable')->get();
            DB::commit();
            $data = $user;
            $message = '';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibition Request. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function searchVisitor($request)
    {
        DB::beginTransaction();
        try {
            $name = $request['name'];
            $visitor=User::query()->where('name', 'LIKE', '%'.$name.'%')->where('userable_type','App\Models\Visitor')->with('userable')->get();
            DB::commit();
            $data = $visitor;
            $message = 'successfully searched. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during search on visitor. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function removeVisitor($user_id)
    {
        DB::beginTransaction();
        try {
            $user=User::query()->findOrFail($user_id);
            $visitor=Visitor::query()->where('id',$user['userable_id'])->first();
            $user->delete();
            $visitor->delete();
            DB::commit();
            $data = [];
            $message = 'visitor removed successfully. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during removing visitor. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

}

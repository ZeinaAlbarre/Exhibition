<?php

namespace App\Services;

use App\Models\Exhibition_visitor;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketServices
{
    public function createTicket($exhibition_id, $user_id): array
    {
        DB::beginTransaction();
        try {
           $is_exist= Exhibition_visitor::query()->where('exhibition_id',$exhibition_id)
                ->where('user_id',$user_id)->first();
            if(!is_null($is_exist))
            {
                $data=[];
                $message='You have previously registered for this exhibition';
                $code=200;
                return ['data' => $data , 'message' => $message, 'code' =>$code];

            }
            $exhibitionVisitor = Exhibition_visitor::create([
                'exhibition_id' => $exhibition_id,
                'user_id' => $user_id,
            ]);
            $data =$exhibitionVisitor;
            DB::commit();
            $qrCodeData = $exhibitionVisitor->id . '-' . now()->timestamp;
            $qrCode = QrCode::generate($qrCodeData);
            $exhibitionVisitor['qr_code']=  $qrCodeData ;
            $exhibitionVisitor->save();
            $message='you are registered successfully to exhibition';
            $code=200;
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['data' => [], 'message' => $e->getMessage() , 'code' => $e->getCode()];
        }
        return ['data' => $data , 'message' => $message, 'code' =>$code];

    }

    public function showQR($request)
    {
        DB::beginTransaction();
        try {
            $qrImage = QrCode::format('png')->size(200)->generate($request['qr']);
            $message = 'success';
            $code = 200;
        }
      catch (\Exception $e) {
        DB::rollback();
        return ['data' => [], 'message' => $e->getMessage() , 'code' => $e->getCode()];
    }
        return ['data' => $qrImage, 'message' => $message, 'code' => $code];

    }

    public function validateTicket($request)
    {
        $qrCode = $request->input('qr_code');
        $ticket = Ticket::where('qr_code', $qrCode)->first();

        if ($ticket && !$ticket->is_used) {
            $ticket->update(['is_used' => true]);
            return response()->json(['status' => 'success', 'message' => 'Ticket is valid.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Ticket is invalid or already used.']);
        }
    }
}


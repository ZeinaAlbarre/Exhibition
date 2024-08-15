<?php

namespace App\Exports;

use App\Models\Company;
use App\Models\Qr;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class ExhibitionVisitorExport implements FromCollection, WithHeadings, WithMapping
{
    protected $exhibitionId;

    public function __construct($exhibitionId)
    {
        $this->exhibitionId = $exhibitionId;
    }

    public function collection()
    {
        $users = Qr::query()
            ->where('exhibition_id', $this->exhibitionId)
            ->where('Attended', 1)
            ->join('users', 'qrs.user_id', '=', 'users.id')
            ->select(
                'users.name as visitor_name',
                'users.id as visitor_id',
                'users.email as visitor_email',
                'users.userable_type',
                'users.userable_id'
            )
            ->get();

        $visitors = [];
        foreach ($users as $item) {
            if ($item->userable_type === 'App\Models\Visitor') {
                $visitor = Visitor::find($item->userable_id);
                $visitors[$item->userable_id] = $visitor;
            }
        }

        $mergedUsers = $users->map(function ($user) use ($visitors) {
            $user['visitor_details'] = $visitors[$user->userable_id] ?? [];

            if ($user->visitor_details) {
                $user['visitor_details']['gender'] = $user->visitor_details->gender;
                $user['visitor_details']['birth_date'] = $user->visitor_details->birth_date;
            }

            return $user;
        });

        return $mergedUsers;
    }

    public function headings(): array
    {
        return [
            'User Name',
            'User Id',
            'User Email',
            'Gender',
            'Birth_Date',
        ];
    }

    public function map($user): array
    {
        return [
            $user->visitor_name,
            $user->visitor_id,
            $user->visitor_email,
            $user->visitor_details['gender'] ?? null,
            $user->visitor_details['birth_date'] ?? null,
        ];
    }
}







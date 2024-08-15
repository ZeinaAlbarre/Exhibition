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


class ExhibitionCompanyVisitorrExport implements FromCollection , WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
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
            ->select('users.name as visitor_name', 'users.id as visitor_id', 'users.email as Visitor_email', 'users.userable_type', 'users.userable_id')
            ->get();

        $companies = [];
        foreach ($users as $item) {
            if ($item->userable_type === 'App\Models\Company') {
                $company = Company::find($item->userable_id);
                $companies[$item->userable_id] = $company;
            }
        }

        $mergedUsers = $users->map(function ($user) use ($companies) {
            $user['company_details'] = $companies[$user->userable_id] ?? null;
            if ($user->company_details) {
                $user['company_details']['company_name'] = $user->company_details->company_name;
                $user['company_details']['business_email'] = $user->company_details->business_email;
                $user['company_details']['website'] = $user->company_details->website;
                $user['company_details']['office_address'] = $user->company_details->office_address;
            }
            return $user;
        });

        return $mergedUsers;
    }

    public function headings(): array
    {
        return [
            'Use Name',
            'User Id',
            'User Email',
            'Company Name',
            'Company Email',
            'Company Website',
            'Company Address',
        ];
    }

    public function map($user): array
    {
        return [
            $user->visitor_name,
            $user->visitor_id,
            $user->Visitor_email,
            $user->company_details ? $user->company_details['company_name'] : null,
            $user->company_details ? $user->company_details['business_email'] : null,
            $user->company_details ? $user->company_details['website'] : null,
            $user->company_details ? $user->company_details['office_address'] : null,
        ];
    }

}

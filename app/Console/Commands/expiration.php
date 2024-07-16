<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Console\Command;

class expiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'expire code user every minute automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users=User::query()->where('code','!=',null)
            ->where('expire_at','<',now()->subDay())->get();
        foreach ($users as $user){
            if($user->hasRole('company')) {
                Company::query()->where('id',$user['userable_id'])->delete();
            }
            else {
                Visitor::query()->where('id',$user['userable_id'])->delete();
            }
            $user->delete();
        }
    }
}

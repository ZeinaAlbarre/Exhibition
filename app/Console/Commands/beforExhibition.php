<?php

namespace App\Console\Commands;

use App\Models\Exhibition;
use App\Models\Exhibition_visitor;
use App\Models\User;
use App\Notifications\beforStartExhibition;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class beforExhibition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:befor-exhibition';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threeDaysLater = Carbon::now()->addDays(3)->toDateString();


        $upcomingExhibitions = Exhibition::where('start_date', $threeDaysLater)->get();

        foreach ($upcomingExhibitions as $exhibition) {

            $exs = Exhibition_visitor::where('exhibition_id', $exhibition->id)->get();

            foreach ($exs as $ex) {
                $visitor = User::where('id', $ex->user_id)->where('userable_id', 'App\Models\Visitor')->first();


                if ($visitor) {
                    Notification::send($visitor, new beforStartExhibition($exhibition->title, $exhibition->start_date));
                }
            }
        }

      //  return response()->json(['message' => 'Reminders sent successfully!']);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Exhibition;
use App\Models\Exhibition_visitor;
use App\Models\Scheduale;
use App\Models\User;
use App\Notifications\schedualeNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class reminding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reminding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'notify Upcoming Scheduales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $upcomingScheduales = Scheduale::where('date', $now->toDateString())
            ->where('time', '>=', $now->addHours(3)->toTimeString())
            ->get();

        foreach ($upcomingScheduales as $scheduale) {
            $exhibition = Exhibition::find($scheduale->exhibition_id);

            if ($exhibition) {

                $exs = Exhibition_visitor::query()->where('exhibition_id',$exhibition['id'])->get();
                foreach ($exs as $ex) {
                    $visitor = User::query()->where('id', $ex['user_id'])->where('userable_id', 'App\Models\Visitor')->first();
                    Notification::send($visitor, new SchedualeNotification($scheduale['topic_name'],$exhibition->title));
                }
            }
        }

        //return response()->json(['message' => 'Notifications sent successfully!']);

    }
}

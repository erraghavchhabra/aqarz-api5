<?php

namespace App\Jobs;


use App\Models\v3\Estate;
use App\Models\v3\NotificationUser;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;



class EstateExpired extends Job
{


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        $estates = Estate::whereDate(
            'updated_at', '<=', Carbon::now()->subDays(30)->format('Y-m-d'))
           // ->where('status', 'new')
            ->get();


        foreach ($estates as $estatesItem) {

            $user = User::where('id', $estatesItem->user_id)
                ->first();


            try {
                if ($user) {
                    $push_data = [
                        'body' => 'مر ٣٠ يوم على اعلانك العقاري رقم : '. $estatesItem->id .' نرجوا منك تحديث العقار حتى يستمر عرضه في التطبيق ولايتم حذفه',
                        'title' => 'مر ٣٠ يوم على اعلانك العقاري',
                        'id' => $estatesItem->id,
                        'user_id' => $estatesItem->user_id,
                        'type' => 'estate',
                    ];

                    $note = NotificationUser::create([
                        'user_id' => $estatesItem->user_id,
                        'title' => 'مر ٣٠ يوم على اعلانك العقاري',
                        'type' => 'estate',
                        'type_id' => $estatesItem->id,
                    ]);


                    //     dd($client);
                    if ($user) {
                        send_push($user->device_token, $push_data, $user->device_type);
                    }
                }
                $estatesItem->status = 'expired';
                $estatesItem->timestamps = false;
                $estatesItem->save();
                Log::channel('slack')->info(['data'=>json_encode($estatesItem),'msg'=>'from job estate expired','check'=>'تم ارسال تنبيه للعقار المرفق ']);
               // Log::channel('slack')->info(json_encode($estatesItem));
                Log::channel('single')->info(json_encode($estatesItem));
            }
            catch (\Exception $ex)
            {
              //  Log::channel('slack')->info(['data'=>json_encode($estatesItem),'msg'=>'from job estate expired','check'=>'تم ارسال تنبيه للعقار المرفق ']);

                Log::channel('slack')->info(['msg'=>$ex->getMessage(),'check'=>'هناك مشكلة في العقار لارسال تنبيه بالانتهاء ']);
            }

        }


    }
}

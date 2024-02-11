<?php

namespace App\Jobs;

use App\User;

class RestoreJob extends Job
{
    private $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      //  $user_mobile = checkIfMobileStartCode( $this->user->mobile,  $this->user->country_code);
        $this->user->restoreOtp($this->user->mobile,$this->user->confirmation_code);

    }
}

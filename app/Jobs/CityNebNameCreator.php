<?php

namespace App\Jobs;


use App\Models\v2\Neighborhood;



class CityNebNameCreator extends Job
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

        $Neighborhood = Neighborhood::with('city')->get();


        foreach ($Neighborhood as $NeighborhoodItem)
        {
            if($NeighborhoodItem->search_name==null)
            {
                $NeighborhoodItem->search_name=@$NeighborhoodItem->city->name_ar.' '.$NeighborhoodItem->name_ar;
                $NeighborhoodItem->save();
            }

        }


    }
}

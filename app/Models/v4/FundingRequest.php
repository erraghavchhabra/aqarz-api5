<?php

namespace App\Models\v4;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundingRequest extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'funding_request';

    protected $fillable = [
        'user_id' ,
        'personalIDNumber',
        'personalName',
        'personalMobileNumber',
        'personalMonthlyNetSalary',
        'employerName',
        'employerType',
        'realEstateProductType',
        'realEstatePropertyInformation',
        'realEstatePropertyPrice',
        'realEstatePropertyAge',
        'leadID',
        'requestID',
        'statusDescription',
        'statusCode',
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

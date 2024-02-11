<?php

namespace App\Models\v3;


use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

//use Malhal\Geographical\Geographical;


class IamProvider extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';
    // use SpatialTrait;
    // use Geographical;

    protected $guarded = [

    ];
    /* protected $spatialFields = [
         'center',
         //    'boundaries',
     ];*/

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        'user_id',
        'sub',
        'englishName',
        'arabicFatherName',
        'englishFatherName',
        'gender',
        'iss',
        'cardIssueDateGregorian',
        'englishGrandFatherName',
        'userid', 'idVersionNo',
        'arabicNationality',
        'arabicName',
        'arabicFirstName',
        'nationalityCode',
        'iqamaExpiryDateHijri',
        'exp',
        'lang',
        'iat',
        'jti',
        'iqamaExpiryDateGregorian',
        'idExpiryDateGregorian',
        'issueLocationAr',
        'dobHijri',
        'englishFirstName',
        'cardIssueDateHijri',
        'issueLocationEn',
        'arabicGrandFatherName',
        'aud',
        'nbf',
        'nationality',
        'dob',
        'englishFamilyName',
        'idExpiryDateHijri',
        'assurance_level',
        'arabicFamilyName',

    ];


    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];

    protected $appends = ['name', 'first_last_name'];

    public function getNameAttribute()
    {
        return App::getLocale() == 'ar' ? $this->arabicName : $this->englishName;
    }

    public function getFirstLastNameAttribute()
    {
        $first = App::getLocale() == 'ar' ? $this->arabicFirstName : $this->englishFirstName;
        $last = App::getLocale() == 'ar' ? $this->arabicFamilyName : $this->englishFamilyName;

        return $first . ' ' . $last;
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}

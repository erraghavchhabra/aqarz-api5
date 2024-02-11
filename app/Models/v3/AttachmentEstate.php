<?php

namespace App\Models\v3;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttachmentEstate extends Model
{

    use SoftDeletes;
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $table = 'attachment_estate';
    protected $guarded = [

    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    use SoftDeletes;
    protected $fillable = [
        'estate_id',
        'file',
        'type',
    ];

 /*   public function getFileAttribute()
    {
        return url( (@$this->attributes['file']));
    }*/

  /*  public function getFilePathAttribute()
    {
        return $this->attributes['file'];
    }*/

    public function estate()
    {
        return $this->belongsTo(Estate::class,'estate_id','id');
    }

    public function setDateAttribute( $value ) {
        $this->attributes['date'] = (new Carbon($value))->format('Y-m-d h:i:s');
    }
}

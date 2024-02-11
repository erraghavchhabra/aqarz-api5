<?php

namespace App\Models\v3;

use App\Models\dashboard\Admin;
use Illuminate\Database\Eloquent\Model;

class PreviewStage extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

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

    protected $table = 'clickup_request_preview_stages';
    protected $with=['field_preview_stages','emp','assigned'];
    protected $fillable = [
        'request_id',
        'request_uuid',
        'emp_id',
        'assigned_id',
        'preview_date',
        'preview_time',
        'ascertainment_status',
        'notes',
        'assigned_id',
    ];
    public function field_preview_stages()
    {
        return $this->hasMany(FieldPreviewStage::class, 'preview_stage_id', 'id');
    }
    public function emp()
    {
        return $this->belongsTo(Admin::class, 'emp_id');
    }
    public function assigned()
    {
        return $this->belongsTo(Admin::class, 'assigned_id');
    }
}

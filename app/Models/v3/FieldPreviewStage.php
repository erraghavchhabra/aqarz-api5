<?php

namespace App\Models\v3;

use App\Models\dashboard\Admin;
use Illuminate\Database\Eloquent\Model;

class FieldPreviewStage extends Model
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
    protected $with = ['emp','assigned'];
    protected $table = 'clickup_request_field_preview_stages';
    protected $fillable = [
        'preview_stage_id',
        'request_id',
        'request_uuid',
        'emp_id',
        'assigned_id',
        'field_emp_name',
        'attendance_status',
        'preview_status',
        'notes',
        'estate_visited_count',
    ];

    public function emp()
    {
        return $this->belongsTo(Admin::class, 'emp_id');
    }
    public function assigned()
    {
        return $this->belongsTo(Admin::class, 'assigned_id');
    }

}

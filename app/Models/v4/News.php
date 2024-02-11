<?php

namespace App\Models\v4;

use App\Models\dashboard\Admin;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{

    protected $table = 'news';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];

    protected $fillable = [
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'view',
        'image',
        'user_id',
    ];


    protected $appends = ['title' , 'description'];

    public function getTitleAttribute()
    {

        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name = 'title_' . $local;
        return $this->$colum_name;
    }

    public function getDescriptionAttribute()
    {

        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name = 'description_' . $local;
        return $this->$colum_name;
    }

    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(NewsComment::class, 'news_id', 'id')->where('comment_id', null);
    }
}

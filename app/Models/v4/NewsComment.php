<?php

namespace App\Models\v4;

use App\Models\dashboard\Admin;
use App\User;
use Illuminate\Database\Eloquent\Model;

class NewsComment extends Model
{

    protected $table = 'news_comments';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];
    protected $fillable = [
        'name',
        'email',
        'comment',
        'news_id',
        'user_id',
        'comment_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function news()
    {
        return $this->belongsTo(News::class, 'news_id', 'id');
    }

    public function replies()
    {
        return $this->hasMany(NewsComment::class, 'comment_id', 'id');
    }
}

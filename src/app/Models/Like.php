<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'user_name',
    ];

    protected $casts = [
        'post_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function scopeByPostAndUser($query, $postId, $userId)
    {
        return $query->where('post_id', $postId)->where('user_id', $userId);
    }

    public static function getPostLikesCount($postId)
    {
        return self::where('post_id', $postId)->count();
    }

    public static function isLikedByUser($postId, $userId)
    {
        return self::where('post_id', $postId)
                    ->where('user_id', $userId)
                    ->exists();
    }


}

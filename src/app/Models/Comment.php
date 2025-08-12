<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'user_name',
        'content',
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

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByPost($query, $postId)
    {
        return $query->where('post_id', $postId);
    }
}
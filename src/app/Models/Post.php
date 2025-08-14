<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Like;
use App\Models\Comment;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'content',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function isLikedByUser($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
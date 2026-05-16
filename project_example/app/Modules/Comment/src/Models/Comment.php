<?php

namespace Comment\Models;

use App\Modules\Comment\src\Models\CommentReaction;
use Comment\Enums\CommentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use User\Models\User;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'item_type',
        'item_id',
        'text',
        'status',
    ];

    protected $casts = [
        'status' => CommentStatusEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->morphTo();
    }

    public function reactions()
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function likes()
    {
        return $this->reactions()->where('type', 'like');
    }

    public function dislikes()
    {
        return $this->reactions()->where('type', 'dislike');
    }

    public function likedByUser($userId): bool
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function dislikedByUser($userId): bool
    {
        return $this->dislikes()->where('user_id', $userId)->exists();
    }

    public function likesCount(): int
    {
        return $this->likes()->count();
    }

    public function dislikesCount(): int
    {
        return $this->dislikes()->count();
    }

    public function scopeApproved($query)
    {
        return $query->where('status', CommentStatusEnum::Approved);
    }

    public function scopePending($query)
    {
        return $query->where('status', CommentStatusEnum::Pending);
    }
}

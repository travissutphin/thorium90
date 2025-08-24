<?php

namespace App\Features\Blog\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_post_id',
        'user_id',
        'parent_id',
        'content',
        'author_name',
        'author_email',
        'author_website',
        'status',
        'approved_at',
        'approved_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Update post comment count when comment is created/updated/deleted
        static::saved(function ($comment) {
            if ($comment->blogPost) {
                $comment->blogPost->updateCommentCount();
            }
        });

        static::deleted(function ($comment) {
            if ($comment->blogPost) {
                $comment->blogPost->updateCommentCount();
            }
        });
    }

    /**
     * Get the post that the comment belongs to.
     */
    public function blogPost()
    {
        return $this->belongsTo(BlogPost::class);
    }

    /**
     * Get the user who wrote the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('status', 'approved')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get the user who approved this comment.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include approved comments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending comments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include top-level comments (no replies).
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Check if the comment is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the comment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Approve the comment.
     */
    public function approve(User $approver = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approver ? $approver->id : auth()->id(),
        ]);
    }

    /**
     * Mark the comment as spam.
     */
    public function markAsSpam()
    {
        $this->update([
            'status' => 'spam',
        ]);
    }

    /**
     * Reject the comment.
     */
    public function reject()
    {
        $this->update([
            'status' => 'rejected',
        ]);
    }

    /**
     * Get the author name (user name or guest name).
     */
    public function getAuthorNameAttribute($value)
    {
        if ($this->user) {
            return $this->user->name;
        }

        return $value;
    }

    /**
     * Get the author email (user email or guest email).
     */
    public function getAuthorEmailAttribute($value)
    {
        if ($this->user) {
            return $this->user->email;
        }

        return $value;
    }

    /**
     * Check if this is a reply to another comment.
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get the depth level of this comment (for nested display).
     */
    public function getDepthLevel(): int
    {
        $depth = 0;
        $comment = $this;

        while ($comment->parent) {
            $depth++;
            $comment = $comment->parent;
        }

        return $depth;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'properties' => 'array',
    ];
    
    /**
     * Get the user that owns the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the subject model of the activity.
     */
    public function subject()
    {
        return $this->morphTo();
    }
    
    /**
     * Scope a query to only include activities for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Scope a query to only include activities with a specific action.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAction($query, $action)
    {
        return $query->where('action', $action);
    }
    
    /**
     * Scope a query to only include activities for a specific subject.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $subject
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSubject($query, $subject)
    {
        return $query->where([
            'subject_type' => get_class($subject),
            'subject_id' => $subject->getKey(),
        ]);
    }
    
    /**
     * Get the username or 'System' if user_id is null.
     *
     * @return string
     */
    public function getUsernameAttribute()
    {
        return $this->user ? $this->user->name : 'System';
    }
}
